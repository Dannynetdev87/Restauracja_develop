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

class BarDashboardTest extends TestCase
{
    use DatabaseTransactions;

    public function test_bar_user_can_see_only_bar_items(): void
    {
        $barUser = User::factory()->create(['role' => User::ROLE_BAR]);
        $order = $this->createOrder();
        $barItem = $this->createOrderItem($order, 'Lemoniada testowa', MenuItem::AREA_BAR);
        $kitchenItem = $this->createOrderItem($order, 'Schabowy testowy', MenuItem::AREA_KITCHEN);

        $this
            ->actingAs($barUser)
            ->get(route('bar.dashboard'))
            ->assertOk()
            ->assertSee('Lemoniada testowa')
            ->assertDontSee('Schabowy testowy')
            ->assertSee('Rozpocznij przygotowanie');

        $this->assertSame(OrderItem::STATUS_NEW, $barItem->fresh()->status);
        $this->assertSame(OrderItem::STATUS_NEW, $kitchenItem->fresh()->status);
    }

    public function test_waiter_cannot_access_bar_dashboard(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);

        $this
            ->actingAs($waiter)
            ->get(route('bar.dashboard'))
            ->assertForbidden();
    }

    public function test_bar_user_can_start_preparing_item_and_history_is_saved(): void
    {
        $barUser = User::factory()->create(['role' => User::ROLE_BAR]);
        $order = $this->createOrder(status: Order::STATUS_OPEN);
        $orderItem = $this->createOrderItem($order, 'Kawa testowa', MenuItem::AREA_BAR);

        $this
            ->actingAs($barUser)
            ->patch(route('bar.order-items.status', $orderItem), [
                'status' => OrderItem::STATUS_PREPARING,
            ])
            ->assertRedirect(route('bar.dashboard'));

        $this->assertDatabaseHas('order_items', [
            'id' => $orderItem->id,
            'status' => OrderItem::STATUS_PREPARING,
        ]);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => Order::STATUS_IN_PROGRESS,
        ]);
        $this->assertDatabaseHas('order_item_status_histories', [
            'order_item_id' => $orderItem->id,
            'changed_by' => $barUser->id,
            'old_status' => OrderItem::STATUS_NEW,
            'new_status' => OrderItem::STATUS_PREPARING,
        ]);
    }

    public function test_bar_user_can_mark_item_as_ready_and_order_status_is_synced(): void
    {
        $barUser = User::factory()->create(['role' => User::ROLE_BAR]);
        $order = $this->createOrder(status: Order::STATUS_IN_PROGRESS);
        $orderItem = $this->createOrderItem(
            order: $order,
            name: 'Herbata testowa',
            productionArea: MenuItem::AREA_BAR,
            status: OrderItem::STATUS_PREPARING,
        );

        $this
            ->actingAs($barUser)
            ->patch(route('bar.order-items.status', $orderItem), [
                'status' => OrderItem::STATUS_READY,
            ])
            ->assertRedirect(route('bar.dashboard'));

        $this->assertDatabaseHas('order_items', [
            'id' => $orderItem->id,
            'status' => OrderItem::STATUS_READY,
        ]);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => Order::STATUS_READY,
        ]);
        $this->assertDatabaseHas('order_item_status_histories', [
            'order_item_id' => $orderItem->id,
            'changed_by' => $barUser->id,
            'old_status' => OrderItem::STATUS_PREPARING,
            'new_status' => OrderItem::STATUS_READY,
        ]);
    }

    public function test_bar_user_cannot_skip_status_transition(): void
    {
        $barUser = User::factory()->create(['role' => User::ROLE_BAR]);
        $order = $this->createOrder();
        $orderItem = $this->createOrderItem($order, 'Napoj testowy', MenuItem::AREA_BAR);

        $this
            ->actingAs($barUser)
            ->patch(route('bar.order-items.status', $orderItem), [
                'status' => OrderItem::STATUS_READY,
            ])
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('order_items', [
            'id' => $orderItem->id,
            'status' => OrderItem::STATUS_NEW,
        ]);
    }

    public function test_bar_user_cannot_update_kitchen_item(): void
    {
        $barUser = User::factory()->create(['role' => User::ROLE_BAR]);
        $order = $this->createOrder();
        $orderItem = $this->createOrderItem($order, 'Danie testowe', MenuItem::AREA_KITCHEN);

        $this
            ->actingAs($barUser)
            ->patch(route('bar.order-items.status', $orderItem), [
                'status' => OrderItem::STATUS_PREPARING,
            ])
            ->assertNotFound();

        $this->assertDatabaseHas('order_items', [
            'id' => $orderItem->id,
            'status' => OrderItem::STATUS_NEW,
        ]);
    }

    private function createOrder(string $status = Order::STATUS_OPEN): Order
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $table = RestaurantTable::create([
            'number' => random_int(100000, 999999),
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

    private function createOrderItem(
        Order $order,
        string $name,
        string $productionArea,
        string $status = OrderItem::STATUS_NEW,
    ): OrderItem {
        $category = MenuCategory::firstOrCreate(
            ['name' => 'Testowa kategoria baru'],
            ['sort_order' => 10, 'is_active' => true],
        );
        $menuItem = MenuItem::create([
            'menu_category_id' => $category->id,
            'name' => $name,
            'description' => null,
            'price' => 18.00,
            'production_area' => $productionArea,
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
