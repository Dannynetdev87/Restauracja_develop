<?php

namespace Tests\Feature;

use App\Livewire\Production\BarDashboard;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RestaurantTable;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
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
            ->assertSee('Rozpocznij przygotowanie')
            ->assertSee('wire:name="production.bar-dashboard"', false)
            ->assertSee('wire:poll.visible.5s', false)
            ->assertDontSee('data-refresh-interval="8000"', false);

        $this->assertSame(OrderItem::STATUS_NEW, $barItem->fresh()->status);
        $this->assertSame(OrderItem::STATUS_NEW, $kitchenItem->fresh()->status);
    }

    public function test_bar_dashboard_groups_items_by_order_and_shows_waiting_time(): void
    {
        $this->travelTo(now()->setTime(15, 30));

        $barUser = User::factory()->create(['role' => User::ROLE_BAR]);
        $order = $this->createOrder(status: Order::STATUS_OPEN);
        $this->createOrderItem(
            order: $order,
            name: 'Czasowa lemoniada testowa',
            productionArea: MenuItem::AREA_BAR,
            createdAt: now()->subMinutes(12),
        );
        $this->createOrderItem(
            order: $order,
            name: 'Czasowa kawa testowa',
            productionArea: MenuItem::AREA_BAR,
            createdAt: now()->subMinutes(8),
        );

        $this
            ->actingAs($barUser)
            ->get(route('bar.dashboard'))
            ->assertOk()
            ->assertSee('Zamówienie #'.$order->id)
            ->assertSee('Stolik '.$order->table->number)
            ->assertSee('2 pozycje')
            ->assertSee('Czasowa lemoniada testowa')
            ->assertSee('Czasowa kawa testowa')
            ->assertSee('Godzina złożenia')
            ->assertSee('15:18')
            ->assertSee('15:22')
            ->assertSee('Czeka')
            ->assertSee('12 min')
            ->assertSee('8 min');

        $this->travelBack();
    }

    public function test_bar_user_can_see_current_oldest_bar_order(): void
    {
        $barUser = User::factory()->create(['role' => User::ROLE_BAR]);
        OrderItem::query()->update(['status' => OrderItem::STATUS_DELIVERED]);

        $oldestOrder = $this->createOrder(openedAt: now()->subYears(2));
        $newerOrder = $this->createOrder(openedAt: now()->subMinutes(5));
        $this->createOrderItem($oldestOrder, 'Najstarsza kawa testowa', MenuItem::AREA_BAR, notes: 'Bez cukru');
        $this->createOrderItem($newerOrder, 'Nowsza herbata testowa', MenuItem::AREA_BAR);

        $this
            ->actingAs($barUser)
            ->get(route('bar.current'))
            ->assertOk()
            ->assertSee('Zamówienie #'.$oldestOrder->id)
            ->assertSee('Najstarsza kawa testowa')
            ->assertSee('Bez cukru')
            ->assertDontSee('Nowsza herbata testowa')
            ->assertSee('>Aktualne</a>', false)
            ->assertSee('>Dashboard</a>', false)
            ->assertDontSee('>Start</a>', false)
            ->assertDontSee('>Menu</a>', false)
            ->assertSee('wire:name="production.bar-current"', false)
            ->assertSee('wire:poll.visible.5s', false)
            ->assertDontSee('data-refresh-interval="8000"', false)
            ->assertSee('Pełny dashboard');
    }

    public function test_bar_current_view_shows_empty_state_without_active_items(): void
    {
        $barUser = User::factory()->create(['role' => User::ROLE_BAR]);
        OrderItem::query()->update(['status' => OrderItem::STATUS_DELIVERED]);

        $this
            ->actingAs($barUser)
            ->get(route('bar.current'))
            ->assertOk()
            ->assertSee('Brak aktywnych pozycji baru');
    }

    public function test_bar_user_is_redirected_to_current_view_after_login(): void
    {
        $barUser = User::factory()->create([
            'email' => 'bar-current@example.com',
            'password' => 'password',
            'role' => User::ROLE_BAR,
        ]);

        $this
            ->post(route('login'), [
                'login' => $barUser->email,
                'password' => 'password',
            ])
            ->assertRedirect(route('bar.current'));
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

    public function test_bar_current_view_can_keep_user_on_current_view_after_status_change(): void
    {
        $barUser = User::factory()->create(['role' => User::ROLE_BAR]);
        $order = $this->createOrder(status: Order::STATUS_OPEN);
        $orderItem = $this->createOrderItem($order, 'Kawa do aktualnego widoku', MenuItem::AREA_BAR);

        $this
            ->actingAs($barUser)
            ->patch(route('bar.order-items.status', $orderItem), [
                'status' => OrderItem::STATUS_PREPARING,
                'redirect_to' => 'bar.current',
            ])
            ->assertRedirect(route('bar.current'));
    }

    public function test_bar_user_can_select_bar_item_as_current(): void
    {
        $barUser = User::factory()->create(['role' => User::ROLE_BAR]);
        OrderItem::query()->update(['status' => OrderItem::STATUS_DELIVERED]);

        $oldestOrder = $this->createOrder(openedAt: now()->subDay());
        $selectedOrder = $this->createOrder(openedAt: now());
        $this->createOrderItem($oldestOrder, 'Oldest bar item', MenuItem::AREA_BAR);
        $selectedItem = $this->createOrderItem($selectedOrder, 'Selected bar item', MenuItem::AREA_BAR);
        $otherSelectedOrderItem = $this->createOrderItem($selectedOrder, 'Other bar item', MenuItem::AREA_BAR);

        $this
            ->actingAs($barUser)
            ->post(route('bar.order-items.select-current', $selectedItem))
            ->assertRedirect(route('bar.current'));

        $this
            ->actingAs($barUser)
            ->get(route('bar.current'))
            ->assertOk()
            ->assertSee('Zamówienie #'.$selectedOrder->id)
            ->assertSee('Selected bar item')
            ->assertDontSee('Oldest bar item')
            ->assertDontSee('Other bar item');

        $this->assertSame(OrderItem::STATUS_NEW, $selectedItem->fresh()->status);
        $this->assertSame(OrderItem::STATUS_NEW, $otherSelectedOrderItem->fresh()->status);
    }

    public function test_bar_user_cannot_select_kitchen_item_as_current(): void
    {
        $barUser = User::factory()->create(['role' => User::ROLE_BAR]);
        $order = $this->createOrder();
        $kitchenItem = $this->createOrderItem($order, 'Kitchen item blocked from bar current', MenuItem::AREA_KITCHEN);

        $this
            ->actingAs($barUser)
            ->post(route('bar.order-items.select-current', $kitchenItem))
            ->assertNotFound();

        $this->assertFalse(session()->has('selected_bar_order_id'));
        $this->assertFalse(session()->has('selected_bar_order_item_id'));
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

    public function test_bar_user_can_cancel_item_and_history_is_saved(): void
    {
        $barUser = User::factory()->create(['role' => User::ROLE_BAR]);
        $order = $this->createOrder(status: Order::STATUS_OPEN);
        $orderItem = $this->createOrderItem($order, 'Brakujaca lemoniada testowa', MenuItem::AREA_BAR);

        $this
            ->actingAs($barUser)
            ->patch(route('bar.order-items.cancel', $orderItem))
            ->assertRedirect(route('bar.dashboard'));

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
            'changed_by' => $barUser->id,
            'old_status' => OrderItem::STATUS_NEW,
            'new_status' => OrderItem::STATUS_CANCELLED,
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

    public function test_bar_livewire_dashboard_updates_item_status_without_redirect(): void
    {
        $barUser = User::factory()->create(['role' => User::ROLE_BAR]);
        $order = $this->createOrder(status: Order::STATUS_OPEN);
        $orderItem = $this->createOrderItem($order, 'Livewire kawa testowa', MenuItem::AREA_BAR);

        $this->actingAs($barUser);

        Livewire::test(BarDashboard::class)
            ->call('updateItemStatus', $orderItem->id, OrderItem::STATUS_PREPARING)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('order_items', [
            'id' => $orderItem->id,
            'status' => OrderItem::STATUS_PREPARING,
        ]);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => Order::STATUS_IN_PROGRESS,
        ]);
    }

    private function createOrder(string $status = Order::STATUS_OPEN, $openedAt = null): Order
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
