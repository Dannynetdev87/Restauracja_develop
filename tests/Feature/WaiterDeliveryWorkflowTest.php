<?php

namespace Tests\Feature;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RestaurantTable;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class WaiterDeliveryWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_waiter_can_mark_ready_item_as_delivered(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $order = $this->createOrder($waiter, Order::STATUS_READY);
        $orderItem = $this->createOrderItem($order, OrderItem::STATUS_READY);

        $this
            ->actingAs($waiter)
            ->patch(route('waiter.order-items.deliver', $orderItem))
            ->assertRedirect(route('waiter.orders.show', $order));

        $this->assertDatabaseHas('order_items', [
            'id' => $orderItem->id,
            'status' => OrderItem::STATUS_DELIVERED,
        ]);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => Order::STATUS_SERVED,
        ]);
        $this->assertDatabaseHas('order_item_status_histories', [
            'order_item_id' => $orderItem->id,
            'changed_by' => $waiter->id,
            'old_status' => OrderItem::STATUS_READY,
            'new_status' => OrderItem::STATUS_DELIVERED,
        ]);
    }

    public function test_waiter_cannot_deliver_other_waiters_item(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $otherWaiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $order = $this->createOrder($otherWaiter, Order::STATUS_READY);
        $orderItem = $this->createOrderItem($order, OrderItem::STATUS_READY);

        $this
            ->actingAs($waiter)
            ->patch(route('waiter.order-items.deliver', $orderItem))
            ->assertForbidden();

        $this->assertDatabaseHas('order_items', [
            'id' => $orderItem->id,
            'status' => OrderItem::STATUS_READY,
        ]);
    }

    public function test_waiter_cannot_deliver_item_that_is_not_ready(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $order = $this->createOrder($waiter, Order::STATUS_IN_PROGRESS);
        $orderItem = $this->createOrderItem($order, OrderItem::STATUS_PREPARING);

        $this
            ->actingAs($waiter)
            ->patch(route('waiter.order-items.deliver', $orderItem))
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('order_items', [
            'id' => $orderItem->id,
            'status' => OrderItem::STATUS_PREPARING,
        ]);
    }

    public function test_order_stays_ready_until_all_items_are_delivered(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $order = $this->createOrder($waiter, Order::STATUS_READY);
        $firstItem = $this->createOrderItem($order, OrderItem::STATUS_READY, 'Pierwsza pozycja');
        $this->createOrderItem($order, OrderItem::STATUS_READY, 'Druga pozycja');

        $this
            ->actingAs($waiter)
            ->patch(route('waiter.order-items.deliver', $firstItem))
            ->assertRedirect(route('waiter.orders.show', $order));

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => Order::STATUS_READY,
        ]);
    }

    public function test_cancelled_items_do_not_block_marking_order_as_served(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $order = $this->createOrder($waiter, Order::STATUS_READY);
        $readyItem = $this->createOrderItem($order, OrderItem::STATUS_READY, 'Pozycja gotowa');
        $this->createOrderItem($order, OrderItem::STATUS_CANCELLED, 'Pozycja anulowana');

        $this
            ->actingAs($waiter)
            ->patch(route('waiter.order-items.deliver', $readyItem))
            ->assertRedirect(route('waiter.orders.show', $order));

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => Order::STATUS_SERVED,
        ]);
    }

    public function test_ready_item_has_delivery_action_on_order_page(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $order = $this->createOrder($waiter, Order::STATUS_READY);
        $this->createOrderItem($order, OrderItem::STATUS_READY, 'Gotowa pozycja');

        $this
            ->actingAs($waiter)
            ->get(route('waiter.orders.show', $order))
            ->assertOk()
            ->assertSee('wire:name="waiter.order-show"', false)
            ->assertSee('wire:poll.visible.5s', false)
            ->assertDontSee('data-refresh-interval="8000"', false)
            ->assertDontSee('wire:click', false)
            ->assertSee('Gotowa pozycja')
            ->assertSee('Gotowe do wydania')
            ->assertSee('Oznacz jako dostarczone');
    }

    private function createOrder(User $waiter, string $status): Order
    {
        $table = RestaurantTable::create([
            'number' => random_int(10000, 99999),
            'seats' => 4,
            'status' => RestaurantTable::STATUS_OCCUPIED,
        ]);

        return Order::create([
            'restaurant_table_id' => $table->id,
            'waiter_id' => $waiter->id,
            'status' => $status,
            'opened_at' => now(),
        ]);
    }

    private function createOrderItem(Order $order, string $status, string $name = 'Testowa pozycja'): OrderItem
    {
        $category = MenuCategory::firstOrCreate(
            ['name' => 'Testowa kategoria wydawania'],
            ['sort_order' => 10, 'is_active' => true],
        );
        $menuItem = MenuItem::create([
            'menu_category_id' => $category->id,
            'name' => $name,
            'description' => null,
            'price' => 25.00,
            'production_area' => MenuItem::AREA_KITCHEN,
            'available' => true,
        ]);

        return OrderItem::create([
            'order_id' => $order->id,
            'menu_item_id' => $menuItem->id,
            'quantity' => 1,
            'unit_price' => $menuItem->price,
            'notes' => null,
            'status' => $status,
        ]);
    }
}
