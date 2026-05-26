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

class KitchenDashboardTest extends TestCase
{
    use DatabaseTransactions;

    public function test_kitchen_user_can_see_only_kitchen_items(): void
    {
        $kitchenUser = User::factory()->create(['role' => User::ROLE_KITCHEN]);
        $order = $this->createOrder();
        $kitchenItem = $this->createOrderItem($order, 'Schabowy testowy', MenuItem::AREA_KITCHEN);
        $barItem = $this->createOrderItem($order, 'Lemoniada testowa', MenuItem::AREA_BAR);

        $this
            ->actingAs($kitchenUser)
            ->get(route('kitchen.dashboard'))
            ->assertOk()
            ->assertSee('Schabowy testowy')
            ->assertDontSee('Lemoniada testowa')
            ->assertSee('Rozpocznij przygotowanie')
            ->assertSee('data-auto-refresh', false)
            ->assertSee('data-refresh-interval="8000"', false);

        $this->assertSame(OrderItem::STATUS_NEW, $kitchenItem->fresh()->status);
        $this->assertSame(OrderItem::STATUS_NEW, $barItem->fresh()->status);
    }

    public function test_kitchen_user_can_see_current_oldest_kitchen_order(): void
    {
        $kitchenUser = User::factory()->create(['role' => User::ROLE_KITCHEN]);
        OrderItem::query()->update(['status' => OrderItem::STATUS_DELIVERED]);

        $oldestOrder = $this->createOrder(openedAt: now()->subYears(2));
        $newerOrder = $this->createOrder(openedAt: now()->subMinutes(5));
        $this->createOrderItem($oldestOrder, 'Najstarsze danie testowe', MenuItem::AREA_KITCHEN, notes: 'Bez cebuli');
        $this->createOrderItem($newerOrder, 'Nowsze danie testowe', MenuItem::AREA_KITCHEN);

        $this
            ->actingAs($kitchenUser)
            ->get(route('kitchen.current'))
            ->assertOk()
            ->assertSee('Zamówienie #'.$oldestOrder->id)
            ->assertSee('Najstarsze danie testowe')
            ->assertSee('Bez cebuli')
            ->assertDontSee('Nowsze danie testowe')
            ->assertSee('>Aktualne</a>', false)
            ->assertSee('>Dashboard</a>', false)
            ->assertDontSee('>Start</a>', false)
            ->assertDontSee('>Menu</a>', false)
            ->assertSee('data-auto-refresh', false)
            ->assertSee('data-refresh-interval="8000"', false)
            ->assertSee('Pełny dashboard');
    }

    public function test_kitchen_current_view_shows_empty_state_without_active_items(): void
    {
        $kitchenUser = User::factory()->create(['role' => User::ROLE_KITCHEN]);
        OrderItem::query()->update(['status' => OrderItem::STATUS_DELIVERED]);

        $this
            ->actingAs($kitchenUser)
            ->get(route('kitchen.current'))
            ->assertOk()
            ->assertSee('Brak aktywnych pozycji kuchni');
    }

    public function test_kitchen_user_is_redirected_to_current_view_after_login(): void
    {
        $kitchenUser = User::factory()->create([
            'email' => 'kitchen-current@example.com',
            'password' => 'password',
            'role' => User::ROLE_KITCHEN,
        ]);

        $this
            ->post(route('login'), [
                'login' => $kitchenUser->email,
                'password' => 'password',
            ])
            ->assertRedirect(route('kitchen.current'));
    }

    public function test_waiter_cannot_access_kitchen_dashboard(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);

        $this
            ->actingAs($waiter)
            ->get(route('kitchen.dashboard'))
            ->assertForbidden();
    }

    public function test_kitchen_user_can_start_preparing_item_and_history_is_saved(): void
    {
        $kitchenUser = User::factory()->create(['role' => User::ROLE_KITCHEN]);
        $order = $this->createOrder(status: Order::STATUS_OPEN);
        $orderItem = $this->createOrderItem($order, 'Zupa testowa', MenuItem::AREA_KITCHEN);

        $this
            ->actingAs($kitchenUser)
            ->patch(route('kitchen.order-items.status', $orderItem), [
                'status' => OrderItem::STATUS_PREPARING,
            ])
            ->assertRedirect(route('kitchen.dashboard'));

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
            'changed_by' => $kitchenUser->id,
            'old_status' => OrderItem::STATUS_NEW,
            'new_status' => OrderItem::STATUS_PREPARING,
        ]);
    }

    public function test_kitchen_dashboard_shows_item_timing_and_preparation_start(): void
    {
        $this->travelTo(now()->setTime(14, 0));

        $kitchenUser = User::factory()->create(['role' => User::ROLE_KITCHEN]);
        $order = $this->createOrder(status: Order::STATUS_OPEN);
        $orderItem = $this->createOrderItem(
            order: $order,
            name: 'Czasowe danie testowe',
            productionArea: MenuItem::AREA_KITCHEN,
            createdAt: now()->subMinutes(17),
        );

        $this
            ->actingAs($kitchenUser)
            ->patch(route('kitchen.order-items.status', $orderItem), [
                'status' => OrderItem::STATUS_PREPARING,
            ])
            ->assertRedirect(route('kitchen.dashboard'));

        $this
            ->actingAs($kitchenUser)
            ->get(route('kitchen.dashboard'))
            ->assertOk()
            ->assertSee('Zamówienie #'.$order->id)
            ->assertSee('Stolik '.$order->table->number)
            ->assertSee('Czasowe danie testowe')
            ->assertSee('Godzina złożenia')
            ->assertSee('13:43')
            ->assertSee('Czeka')
            ->assertSee('17 min')
            ->assertSee('Start przygotowania')
            ->assertSee('14:00');

        $this->travelBack();
    }

    public function test_kitchen_current_view_can_keep_user_on_current_view_after_status_change(): void
    {
        $kitchenUser = User::factory()->create(['role' => User::ROLE_KITCHEN]);
        $order = $this->createOrder(status: Order::STATUS_OPEN);
        $orderItem = $this->createOrderItem($order, 'Zupa do aktualnego widoku', MenuItem::AREA_KITCHEN);

        $this
            ->actingAs($kitchenUser)
            ->patch(route('kitchen.order-items.status', $orderItem), [
                'status' => OrderItem::STATUS_PREPARING,
                'redirect_to' => 'kitchen.current',
            ])
            ->assertRedirect(route('kitchen.current'));
    }

    public function test_kitchen_user_can_mark_item_as_ready_and_order_status_is_synced(): void
    {
        $kitchenUser = User::factory()->create(['role' => User::ROLE_KITCHEN]);
        $order = $this->createOrder(status: Order::STATUS_IN_PROGRESS);
        $orderItem = $this->createOrderItem(
            order: $order,
            name: 'Pierogi testowe',
            productionArea: MenuItem::AREA_KITCHEN,
            status: OrderItem::STATUS_PREPARING,
        );

        $this
            ->actingAs($kitchenUser)
            ->patch(route('kitchen.order-items.status', $orderItem), [
                'status' => OrderItem::STATUS_READY,
            ])
            ->assertRedirect(route('kitchen.dashboard'));

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
            'changed_by' => $kitchenUser->id,
            'old_status' => OrderItem::STATUS_PREPARING,
            'new_status' => OrderItem::STATUS_READY,
        ]);
    }

    public function test_kitchen_user_can_cancel_item_and_history_is_saved(): void
    {
        $kitchenUser = User::factory()->create(['role' => User::ROLE_KITCHEN]);
        $order = $this->createOrder(status: Order::STATUS_OPEN);
        $orderItem = $this->createOrderItem($order, 'Brakujacy kotlet testowy', MenuItem::AREA_KITCHEN);

        $this
            ->actingAs($kitchenUser)
            ->patch(route('kitchen.order-items.cancel', $orderItem))
            ->assertRedirect(route('kitchen.dashboard'));

        $this->assertDatabaseHas('order_items', [
            'id' => $orderItem->id,
            'status' => OrderItem::STATUS_CANCELLED,
        ]);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => Order::STATUS_IN_PROGRESS,
        ]);
        $this->assertDatabaseHas('order_item_status_histories', [
            'order_item_id' => $orderItem->id,
            'changed_by' => $kitchenUser->id,
            'old_status' => OrderItem::STATUS_NEW,
            'new_status' => OrderItem::STATUS_CANCELLED,
        ]);
    }

    public function test_waiter_sees_cancelled_kitchen_item_and_bill_excludes_it(): void
    {
        $kitchenUser = User::factory()->create(['role' => User::ROLE_KITCHEN]);
        $order = $this->createOrder(status: Order::STATUS_READY);
        $billableItem = $this->createOrderItem($order, 'Dostarczone danie testowe', MenuItem::AREA_KITCHEN, OrderItem::STATUS_DELIVERED);
        $cancelledItem = $this->createOrderItem($order, 'Brakujace danie testowe', MenuItem::AREA_KITCHEN);
        $waiter = $order->waiter;

        $this
            ->actingAs($kitchenUser)
            ->patch(route('kitchen.order-items.cancel', $cancelledItem))
            ->assertRedirect(route('kitchen.dashboard'));

        $this
            ->actingAs($waiter)
            ->get(route('waiter.orders.show', $order))
            ->assertOk()
            ->assertSee('Brakujace danie testowe')
            ->assertSee('Anulowane')
            ->assertSee('0,00');

        $this
            ->actingAs($waiter)
            ->get(route('waiter.orders.bill', $order))
            ->assertOk()
            ->assertSee('Brakujace danie testowe')
            ->assertSee('Anulowane')
            ->assertSee('30,00')
            ->assertDontSee('60,00');

        $this->assertEquals($billableItem->subtotal(), $order->fresh()->load('items')->total());
    }

    public function test_kitchen_user_cannot_skip_status_transition(): void
    {
        $kitchenUser = User::factory()->create(['role' => User::ROLE_KITCHEN]);
        $order = $this->createOrder();
        $orderItem = $this->createOrderItem($order, 'Danie testowe', MenuItem::AREA_KITCHEN);

        $this
            ->actingAs($kitchenUser)
            ->patch(route('kitchen.order-items.status', $orderItem), [
                'status' => OrderItem::STATUS_READY,
            ])
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('order_items', [
            'id' => $orderItem->id,
            'status' => OrderItem::STATUS_NEW,
        ]);
    }

    public function test_kitchen_user_cannot_update_bar_item(): void
    {
        $kitchenUser = User::factory()->create(['role' => User::ROLE_KITCHEN]);
        $order = $this->createOrder();
        $orderItem = $this->createOrderItem($order, 'Napój testowy', MenuItem::AREA_BAR);

        $this
            ->actingAs($kitchenUser)
            ->patch(route('kitchen.order-items.status', $orderItem), [
                'status' => OrderItem::STATUS_PREPARING,
            ])
            ->assertNotFound();

        $this->assertDatabaseHas('order_items', [
            'id' => $orderItem->id,
            'status' => OrderItem::STATUS_NEW,
        ]);
    }

    private function createOrder(string $status = Order::STATUS_OPEN, $openedAt = null): Order
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $table = RestaurantTable::create([
            'number' => random_int(1000, 9999),
            'seats' => 4,
            'status' => RestaurantTable::STATUS_OCCUPIED,
        ]);

        return Order::create([
            'restaurant_table_id' => $table->id,
            'waiter_id' => $waiter->id,
            'status' => $status,
            'opened_at' => $openedAt ?? now(),
        ]);
    }

    private function createOrderItem(
        Order $order,
        string $name,
        string $productionArea,
        string $status = OrderItem::STATUS_NEW,
        ?string $notes = null,
        $createdAt = null,
    ): OrderItem {
        $category = MenuCategory::firstOrCreate(
            ['name' => 'Testowa kategoria kuchni'],
            ['sort_order' => 10, 'is_active' => true],
        );
        $menuItem = MenuItem::create([
            'menu_category_id' => $category->id,
            'name' => $name,
            'description' => null,
            'price' => 30.00,
            'production_area' => $productionArea,
            'available' => true,
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'menu_item_id' => $menuItem->id,
            'quantity' => 1,
            'unit_price' => $menuItem->price,
            'notes' => $notes,
            'status' => $status,
        ]);

        if ($createdAt !== null) {
            $orderItem->forceFill([
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ])->save();
        }

        return $orderItem;
    }
}
