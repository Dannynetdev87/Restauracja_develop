<?php

namespace Tests\Feature;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\RestaurantTable;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ManagerDashboardTest extends TestCase
{
    use DatabaseTransactions;

    public function test_manager_dashboard_uses_database_data(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $table = RestaurantTable::create([
            'number' => 940,
            'seats' => 4,
            'status' => RestaurantTable::STATUS_OCCUPIED,
        ]);
        RestaurantTable::create([
            'number' => 941,
            'seats' => 2,
            'status' => RestaurantTable::STATUS_FREE,
        ]);

        $paidOrder = Order::create([
            'restaurant_table_id' => $table->id,
            'waiter_id' => $waiter->id,
            'status' => Order::STATUS_PAID,
            'opened_at' => now(),
            'paid_at' => now(),
        ]);
        $this->createOrderItem($paidOrder, 'Dashboard pierogi', 100, 0.32);
        Payment::create([
            'order_id' => $paidOrder->id,
            'amount' => 32.00,
            'payment_method' => Payment::METHOD_CARD,
            'status' => Payment::STATUS_PAID,
            'paid_at' => now(),
        ]);

        Order::create([
            'restaurant_table_id' => $table->id,
            'waiter_id' => $waiter->id,
            'status' => Order::STATUS_OPEN,
            'opened_at' => now()->subMinutes(45),
        ]);

        $this
            ->actingAs($manager)
            ->get(route('manager.dashboard'))
            ->assertOk()
            ->assertSee('Centrum dowodzenia')
            ->assertSee('Panel Managera')
            ->assertSee('32,00 zł')
            ->assertSee('Dashboard pierogi')
            ->assertSee('Stolik')
            ->assertSee('Zarządzanie menu')
            ->assertSee('Zarządzanie stolikami')
            ->assertSee('Historia zamówień')
            ->assertSee('Wymagające uwagi')
            ->assertSee('Długo otwarte')
            ->assertSee(route('manager.podglad'), false)
            ->assertSee(route('manager.tables.index'), false)
            ->assertSee(route('manager.orders.history'), false);
    }

    public function test_admin_can_access_manager_dashboard(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this
            ->actingAs($admin)
            ->get(route('manager.dashboard'))
            ->assertOk()
            ->assertSee('Panel Managera');
    }

    public function test_non_manager_roles_cannot_access_manager_dashboard(): void
    {
        foreach ([User::ROLE_WAITER, User::ROLE_KITCHEN, User::ROLE_BAR] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this
                ->actingAs($user)
                ->get(route('manager.dashboard'))
                ->assertForbidden();
        }
    }

    public function test_manager_order_history_filters_orders(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $otherWaiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $table = RestaurantTable::create([
            'number' => 942,
            'seats' => 4,
            'status' => RestaurantTable::STATUS_FREE,
        ]);
        $otherTable = RestaurantTable::create([
            'number' => 943,
            'seats' => 2,
            'status' => RestaurantTable::STATUS_FREE,
        ]);

        $visibleOrder = Order::create([
            'restaurant_table_id' => $table->id,
            'waiter_id' => $waiter->id,
            'status' => Order::STATUS_PAID,
            'opened_at' => now(),
            'paid_at' => now(),
        ]);
        $this->createOrderItem($visibleOrder, 'Historia pierogi', 1, 18.00);

        $hiddenOrder = Order::create([
            'restaurant_table_id' => $otherTable->id,
            'waiter_id' => $otherWaiter->id,
            'status' => Order::STATUS_PAID,
            'opened_at' => now()->subDay(),
            'paid_at' => now()->subDay(),
        ]);
        $this->createOrderItem($hiddenOrder, 'Historia kompot', 1, 8.00);

        $this
            ->actingAs($manager)
            ->get(route('manager.orders.history', [
                'date_from' => now()->toDateString(),
                'date_to' => now()->toDateString(),
                'waiter_id' => $waiter->id,
                'restaurant_table_id' => $table->id,
            ]))
            ->assertOk()
            ->assertSee('Historia zamówień')
            ->assertSee('Historia pierogi')
            ->assertDontSee('Historia kompot');
    }

    private function createOrderItem(Order $order, string $name, int $quantity, float $price): OrderItem
    {
        $category = MenuCategory::firstOrCreate(
            ['name' => 'Testowa kategoria dashboardu'],
            ['sort_order' => 10, 'is_active' => true],
        );

        $menuItem = MenuItem::create([
            'menu_category_id' => $category->id,
            'name' => $name,
            'description' => null,
            'price' => $price,
            'production_area' => MenuItem::AREA_KITCHEN,
            'available' => true,
        ]);

        return OrderItem::create([
            'order_id' => $order->id,
            'menu_item_id' => $menuItem->id,
            'quantity' => $quantity,
            'unit_price' => $price,
            'notes' => null,
            'status' => OrderItem::STATUS_DELIVERED,
        ]);
    }
}
