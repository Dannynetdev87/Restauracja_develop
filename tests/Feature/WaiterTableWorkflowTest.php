<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\RestaurantTable;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class WaiterTableWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_waiter_can_see_restaurant_tables(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        RestaurantTable::create([
            'number' => 920,
            'seats' => 4,
            'status' => RestaurantTable::STATUS_FREE,
        ]);

        $this
            ->actingAs($waiter)
            ->get(route('waiter.tables.index'))
            ->assertOk()
            ->assertSee('Stolik 920')
            ->assertSee('Wolny')
            ->assertSee('Rozpocznij zamówienie');
    }

    public function test_waiter_can_open_order_for_free_table(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $table = RestaurantTable::create([
            'number' => 921,
            'seats' => 2,
            'status' => RestaurantTable::STATUS_FREE,
        ]);

        $response = $this
            ->actingAs($waiter)
            ->post(route('waiter.orders.store', $table));

        $order = Order::where('restaurant_table_id', $table->id)->first();

        $response->assertRedirect(route('waiter.orders.show', $order));

        $this->assertDatabaseHas('orders', [
            'restaurant_table_id' => $table->id,
            'waiter_id' => $waiter->id,
            'status' => Order::STATUS_OPEN,
        ]);
        $this->assertDatabaseHas('restaurant_tables', [
            'id' => $table->id,
            'status' => RestaurantTable::STATUS_OCCUPIED,
        ]);
    }

    public function test_waiter_cannot_open_order_for_unavailable_table(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $table = RestaurantTable::create([
            'number' => 922,
            'seats' => 4,
            'status' => RestaurantTable::STATUS_RESERVED,
        ]);

        $this
            ->actingAs($waiter)
            ->post(route('waiter.orders.store', $table))
            ->assertRedirect(route('waiter.tables.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('orders', [
            'restaurant_table_id' => $table->id,
            'waiter_id' => $waiter->id,
        ]);
    }

    public function test_waiter_cannot_open_second_active_order_for_table(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $table = RestaurantTable::create([
            'number' => 923,
            'seats' => 4,
            'status' => RestaurantTable::STATUS_FREE,
        ]);

        Order::create([
            'restaurant_table_id' => $table->id,
            'waiter_id' => $waiter->id,
            'status' => Order::STATUS_OPEN,
            'opened_at' => now(),
        ]);

        $this
            ->actingAs($waiter)
            ->post(route('waiter.orders.store', $table))
            ->assertRedirect(route('waiter.tables.index'))
            ->assertSessionHas('error');

        $this->assertSame(1, Order::where('restaurant_table_id', $table->id)->count());
    }

    public function test_waiter_cannot_view_other_waiters_order(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $otherWaiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $table = RestaurantTable::create([
            'number' => 924,
            'seats' => 4,
            'status' => RestaurantTable::STATUS_OCCUPIED,
        ]);
        $order = Order::create([
            'restaurant_table_id' => $table->id,
            'waiter_id' => $otherWaiter->id,
            'status' => Order::STATUS_OPEN,
            'opened_at' => now(),
        ]);

        $this
            ->actingAs($waiter)
            ->get(route('waiter.orders.show', $order))
            ->assertForbidden();
    }
}
