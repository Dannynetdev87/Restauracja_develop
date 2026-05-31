<?php

namespace Tests\Feature;

use App\Models\RestaurantTable;
use App\Models\User;
use App\Models\Zone;
use Database\Seeders\MenuSeeder;
use Database\Seeders\OrderSeeder;
use Database\Seeders\RestaurantTableSeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\ZoneSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SeededWaiterDataConsistencyTest extends TestCase
{
    use DatabaseTransactions;

    public function test_seeded_waiter_tables_zones_and_active_orders_are_consistent(): void
    {
        $this->seedWaiterData();

        $tables = RestaurantTable::query()
            ->with(['assignedWaiter', 'zone.assignedWaiter', 'activeOrders.waiter'])
            ->orderBy('number')
            ->get();

        foreach ($tables as $table) {
            $activeOrders = $table->activeOrders;

            if ($activeOrders->isNotEmpty()) {
                $effectiveWaiter = $table->effectiveAssignedWaiter();

                $this->assertNotNull($effectiveWaiter, "Table {$table->number} has active orders without an effective waiter.");

                foreach ($activeOrders as $order) {
                    $this->assertSame(
                        $effectiveWaiter->id,
                        $order->waiter_id,
                        "Active order {$order->id} on table {$table->number} belongs to a different waiter.",
                    );
                }
            }

            if ($table->status === RestaurantTable::STATUS_FREE) {
                $this->assertCount(0, $activeOrders, "Free table {$table->number} has an active order.");
            }

            if ($table->status === RestaurantTable::STATUS_OCCUPIED) {
                $this->assertGreaterThan(0, $activeOrders->count(), "Occupied table {$table->number} has no active order.");
            }

            if ($table->assigned_waiter_id === null && $table->zone?->assigned_waiter_id !== null) {
                $this->assertTrue(
                    RestaurantTable::query()
                        ->visibleForWaiter($table->zone->assigned_waiter_id)
                        ->whereKey($table->id)
                        ->exists(),
                    "Zone-served table {$table->number} is not visible for its zone waiter.",
                );
            }
        }

        $waiters = User::query()
            ->where('role', User::ROLE_WAITER)
            ->get();

        foreach ($waiters as $waiter) {
            $visibleTables = RestaurantTable::query()
                ->visibleForWaiter($waiter->id)
                ->with('activeOrders')
                ->get();

            foreach ($visibleTables as $table) {
                foreach ($table->activeOrders as $order) {
                    $this->assertSame(
                        $waiter->id,
                        $order->waiter_id,
                        "Waiter {$waiter->email} can see table {$table->number} with another waiter's active order.",
                    );
                }
            }
        }
    }

    public function test_direct_table_assignment_takes_precedence_over_zone_assignment(): void
    {
        $directWaiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $zoneWaiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $zone = Zone::create([
            'name' => 'Testowa strefa z kelnerem',
            'assigned_waiter_id' => $zoneWaiter->id,
            'is_active' => true,
        ]);
        $table = RestaurantTable::create([
            'number' => 9901,
            'seats' => 4,
            'status' => RestaurantTable::STATUS_FREE,
            'assigned_waiter_id' => $directWaiter->id,
            'zone_id' => $zone->id,
        ]);

        $this->assertTrue($table->fresh(['assignedWaiter', 'zone.assignedWaiter'])->effectiveAssignedWaiter()->is($directWaiter));
        $this->assertTrue(RestaurantTable::query()->visibleForWaiter($directWaiter->id)->whereKey($table->id)->exists());
        $this->assertFalse(RestaurantTable::query()->visibleForWaiter($zoneWaiter->id)->whereKey($table->id)->exists());
    }

    private function seedWaiterData(): void
    {
        $this->seed(UserSeeder::class);
        $this->seed(RestaurantTableSeeder::class);
        $this->seed(ZoneSeeder::class);
        $this->seed(MenuSeeder::class);
        $this->seed(OrderSeeder::class);
    }
}
