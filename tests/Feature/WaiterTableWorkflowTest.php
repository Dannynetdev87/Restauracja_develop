<?php

namespace Tests\Feature;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RestaurantTable;
use App\Models\User;
use App\Models\Zone;
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
            'assigned_waiter_id' => $waiter->id,
        ]);

        $this
            ->actingAs($waiter)
            ->get(route('waiter.tables.index'))
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('Stoliki')
            ->assertSee('wire:name="waiter.tables"', false)
            ->assertSee('wire:poll.5s', false)
            ->assertDontSee('data-refresh-interval="8000"', false)
            ->assertSee('Stolik 920')
            ->assertSee('Wolny')
            ->assertSee('Rozpocznij zamówienie');
    }

    public function test_waiter_sees_free_tables_and_only_own_occupied_tables(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $otherWaiter = User::factory()->create(['role' => User::ROLE_WAITER]);

        $freeTable = RestaurantTable::create([
            'number' => 930,
            'seats' => 2,
            'status' => RestaurantTable::STATUS_FREE,
            'assigned_waiter_id' => $waiter->id,
        ]);
        $ownTable = RestaurantTable::create([
            'number' => 931,
            'seats' => 4,
            'status' => RestaurantTable::STATUS_OCCUPIED,
            'assigned_waiter_id' => $waiter->id,
        ]);
        $otherTable = RestaurantTable::create([
            'number' => 932,
            'seats' => 4,
            'status' => RestaurantTable::STATUS_OCCUPIED,
            'assigned_waiter_id' => $otherWaiter->id,
        ]);
        RestaurantTable::create([
            'number' => 934,
            'seats' => 2,
            'status' => RestaurantTable::STATUS_FREE,
            'assigned_waiter_id' => null,
        ]);

        Order::create([
            'restaurant_table_id' => $ownTable->id,
            'waiter_id' => $waiter->id,
            'status' => Order::STATUS_OPEN,
            'opened_at' => now(),
        ]);
        Order::create([
            'restaurant_table_id' => $otherTable->id,
            'waiter_id' => $otherWaiter->id,
            'status' => Order::STATUS_OPEN,
            'opened_at' => now(),
        ]);

        $this
            ->actingAs($waiter)
            ->get(route('waiter.tables.index'))
            ->assertOk()
            ->assertSee('wire:name="waiter.tables"', false)
            ->assertSee('wire:poll.5s', false)
            ->assertDontSee('data-refresh-interval="8000"', false)
            ->assertSee('Stolik 930')
            ->assertSee('Stolik 931')
            ->assertDontSee('Stolik 932')
            ->assertDontSee('Stolik 934');

        $this->assertTrue($freeTable->fresh()->isFree());
    }

    public function test_waiter_without_assigned_tables_sees_empty_state(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);

        $this
            ->actingAs($waiter)
            ->get(route('waiter.tables.index'))
            ->assertOk()
            ->assertSee('Nie masz aktualnie przypisanych stolików');
    }

    public function test_waiter_table_panel_shows_active_order_status_and_item_alerts(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $table = RestaurantTable::create([
            'number' => 933,
            'seats' => 4,
            'status' => RestaurantTable::STATUS_OCCUPIED,
            'assigned_waiter_id' => $waiter->id,
        ]);
        $order = Order::create([
            'restaurant_table_id' => $table->id,
            'waiter_id' => $waiter->id,
            'status' => Order::STATUS_READY,
            'opened_at' => now(),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'menu_item_id' => $this->createMenuItem(name: 'Gotowy schabowy testowy')->id,
            'quantity' => 2,
            'unit_price' => 25.00,
            'notes' => null,
            'status' => OrderItem::STATUS_READY,
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'menu_item_id' => $this->createMenuItem(name: 'Brakujacy kompot testowy')->id,
            'quantity' => 1,
            'unit_price' => 12.00,
            'notes' => null,
            'status' => OrderItem::STATUS_CANCELLED,
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'menu_item_id' => $this->createMenuItem(name: 'Zupa w trakcie testowa')->id,
            'quantity' => 1,
            'unit_price' => 18.00,
            'notes' => null,
            'status' => OrderItem::STATUS_PREPARING,
        ]);

        $this
            ->actingAs($waiter)
            ->get(route('waiter.dashboard'))
            ->assertOk()
            ->assertSee('W realizacji')
            ->assertSee('Anulowane / braki')
            ->assertSee('Do odbioru')
            ->assertSee('wire:name="waiter.dashboard"', false)
            ->assertSee('wire:poll.5s', false)
            ->assertDontSee('data-refresh-interval="8000"', false)
            ->assertSee('Stolik 933')
            ->assertSee('2x Gotowy schabowy testowy')
            ->assertSee('Stan: Gotowe do dostarczenia')
            ->assertSee('Gotowy schabowy testowy')
            ->assertSee('1x Brakujacy kompot testowy')
            ->assertSee('Brakujacy kompot testowy')
            ->assertSee('1x Zupa w trakcie testowa')
            ->assertSee('Twoja strefa operacyjna')
            ->assertSee('Dzisiejsza zmiana');
    }

    public function test_waiter_dashboard_is_available(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);

        $this
            ->actingAs($waiter)
            ->get(route('waiter.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('Stoliki')
            ->assertSee('wire:name="waiter.dashboard"', false)
            ->assertSee('wire:poll.5s', false)
            ->assertDontSee('data-refresh-interval="8000"', false)
            ->assertSee('W realizacji');
    }

    public function test_waiter_user_is_redirected_to_table_list_after_login(): void
    {
        $waiter = User::factory()->create([
            'email' => 'waiter-tables@example.com',
            'password' => 'password',
            'role' => User::ROLE_WAITER,
        ]);

        $this
            ->post(route('login'), [
                'login' => $waiter->email,
                'password' => 'password',
            ])
            ->assertRedirect(route('waiter.dashboard'));
    }

    public function test_waiter_can_open_order_for_free_table_with_items(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $table = RestaurantTable::create([
            'number' => 921,
            'seats' => 2,
            'status' => RestaurantTable::STATUS_FREE,
            'assigned_waiter_id' => $waiter->id,
        ]);
        $menuItem = $this->createMenuItem(price: 36.50);

        $response = $this
            ->actingAs($waiter)
            ->post(route('waiter.orders.store', $table), [
                'items' => [
                    $menuItem->id => [
                        'quantity' => 2,
                        'notes' => 'Bez cebuli',
                    ],
                ],
            ]);

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
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'menu_item_id' => $menuItem->id,
            'quantity' => 2,
            'unit_price' => '36.50',
            'notes' => 'Bez cebuli',
            'status' => OrderItem::STATUS_NEW,
        ]);
        $this->assertDatabaseHas('order_item_status_histories', [
            'new_status' => OrderItem::STATUS_NEW,
            'changed_by' => $waiter->id,
        ]);
    }

    public function test_waiter_can_use_table_assigned_through_active_zone(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $zone = Zone::create([
            'name' => 'Strefa kelnera testowego',
            'assigned_waiter_id' => $waiter->id,
            'is_active' => true,
        ]);
        $table = RestaurantTable::create([
            'number' => 937,
            'seats' => 4,
            'status' => RestaurantTable::STATUS_FREE,
            'assigned_waiter_id' => null,
            'zone_id' => $zone->id,
        ]);
        $menuItem = $this->createMenuItem(name: 'Testowe danie strefowe');

        $this
            ->actingAs($waiter)
            ->get(route('waiter.tables.index'))
            ->assertOk()
            ->assertSee('wire:name="waiter.tables"', false)
            ->assertSee('wire:poll.5s', false)
            ->assertSee('Stolik 937')
            ->assertSee('Strefa kelnera testowego');

        $response = $this
            ->actingAs($waiter)
            ->post(route('waiter.orders.store', $table), [
                'items' => [
                    $menuItem->id => ['quantity' => 1],
                ],
            ]);

        $order = Order::where('restaurant_table_id', $table->id)->first();

        $response->assertRedirect(route('waiter.orders.show', $order));

        $this->assertDatabaseHas('orders', [
            'restaurant_table_id' => $table->id,
            'waiter_id' => $waiter->id,
            'status' => Order::STATUS_OPEN,
        ]);
    }

    public function test_table_direct_waiter_assignment_overrides_zone_waiter(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $otherWaiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $zone = Zone::create([
            'name' => 'Strefa z innym stolikiem',
            'assigned_waiter_id' => $waiter->id,
        ]);

        RestaurantTable::create([
            'number' => 938,
            'seats' => 4,
            'status' => RestaurantTable::STATUS_FREE,
            'assigned_waiter_id' => $otherWaiter->id,
            'zone_id' => $zone->id,
        ]);

        $this
            ->actingAs($waiter)
            ->get(route('waiter.tables.index'))
            ->assertOk()
            ->assertDontSee('Stolik 938');
    }

    public function test_waiter_can_open_order_form_for_selected_table(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $table = RestaurantTable::create([
            'number' => 925,
            'seats' => 4,
            'status' => RestaurantTable::STATUS_FREE,
            'assigned_waiter_id' => $waiter->id,
        ]);
        $menuItem = $this->createMenuItem(name: 'Testowy rosół');

        $this
            ->actingAs($waiter)
            ->get(route('waiter.orders.create', ['table_id' => $table->id]))
            ->assertOk()
            ->assertSee('Stolik 925')
            ->assertSee('Testowy rosół')
            ->assertSee('Zapisz pozycje');
    }

    public function test_waiter_cannot_open_order_form_for_other_waiters_table(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $otherWaiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $table = RestaurantTable::create([
            'number' => 929,
            'seats' => 4,
            'status' => RestaurantTable::STATUS_FREE,
            'assigned_waiter_id' => $otherWaiter->id,
        ]);

        $this
            ->actingAs($waiter)
            ->get(route('waiter.orders.create', ['table_id' => $table->id]))
            ->assertNotFound();
    }

    public function test_waiter_cannot_open_order_form_for_unassigned_table(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $table = RestaurantTable::create([
            'number' => 935,
            'seats' => 4,
            'status' => RestaurantTable::STATUS_FREE,
            'assigned_waiter_id' => null,
        ]);

        $this
            ->actingAs($waiter)
            ->get(route('waiter.orders.create', ['table_id' => $table->id]))
            ->assertNotFound();
    }

    public function test_waiter_order_form_shows_current_and_selected_total_summary(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $table = RestaurantTable::create([
            'number' => 928,
            'seats' => 4,
            'status' => RestaurantTable::STATUS_OCCUPIED,
            'assigned_waiter_id' => $waiter->id,
        ]);
        $order = Order::create([
            'restaurant_table_id' => $table->id,
            'waiter_id' => $waiter->id,
            'status' => Order::STATUS_OPEN,
            'opened_at' => now(),
        ]);
        $menuItem = $this->createMenuItem(name: 'Testowa kawa', price: 12.50);

        OrderItem::create([
            'order_id' => $order->id,
            'menu_item_id' => $menuItem->id,
            'quantity' => 2,
            'unit_price' => $menuItem->price,
            'notes' => null,
            'status' => OrderItem::STATUS_NEW,
        ]);

        $this
            ->actingAs($waiter)
            ->get(route('waiter.orders.create', ['table_id' => $table->id]))
            ->assertOk()
            ->assertSee('Aktualny rachunek')
            ->assertSee('Nowe pozycje')
            ->assertSee('Razem po dodaniu')
            ->assertSee('25,00 zł');
    }

    public function test_waiter_cannot_open_order_for_unavailable_table(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $table = RestaurantTable::create([
            'number' => 922,
            'seats' => 4,
            'status' => RestaurantTable::STATUS_RESERVED,
            'assigned_waiter_id' => $waiter->id,
        ]);
        $menuItem = $this->createMenuItem();

        $this
            ->actingAs($waiter)
            ->post(route('waiter.orders.store', $table), [
                'items' => [
                    $menuItem->id => ['quantity' => 1],
                ],
            ])
            ->assertSessionHasErrors('table');

        $this->assertDatabaseMissing('orders', [
            'restaurant_table_id' => $table->id,
            'waiter_id' => $waiter->id,
        ]);
    }

    public function test_waiter_cannot_submit_order_for_other_waiters_table(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $otherWaiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $table = RestaurantTable::create([
            'number' => 936,
            'seats' => 4,
            'status' => RestaurantTable::STATUS_FREE,
            'assigned_waiter_id' => $otherWaiter->id,
        ]);
        $menuItem = $this->createMenuItem();

        $this
            ->actingAs($waiter)
            ->post(route('waiter.orders.store', $table), [
                'items' => [
                    $menuItem->id => ['quantity' => 1],
                ],
            ])
            ->assertSessionHasErrors('table');

        $this->assertDatabaseMissing('orders', [
            'restaurant_table_id' => $table->id,
            'waiter_id' => $waiter->id,
        ]);
    }

    public function test_waiter_can_add_items_to_existing_active_order(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $table = RestaurantTable::create([
            'number' => 923,
            'seats' => 4,
            'status' => RestaurantTable::STATUS_OCCUPIED,
            'assigned_waiter_id' => $waiter->id,
        ]);
        $order = Order::create([
            'restaurant_table_id' => $table->id,
            'waiter_id' => $waiter->id,
            'status' => Order::STATUS_OPEN,
            'opened_at' => now(),
        ]);
        $menuItem = $this->createMenuItem();

        $this
            ->actingAs($waiter)
            ->post(route('waiter.orders.store', $table), [
                'items' => [
                    $menuItem->id => ['quantity' => 3],
                ],
            ])
            ->assertRedirect(route('waiter.orders.show', $order));

        $this->assertSame(1, Order::where('restaurant_table_id', $table->id)->count());
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'menu_item_id' => $menuItem->id,
            'quantity' => 3,
        ]);
    }

    public function test_waiter_cannot_submit_order_without_positive_quantity(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $table = RestaurantTable::create([
            'number' => 926,
            'seats' => 2,
            'status' => RestaurantTable::STATUS_FREE,
            'assigned_waiter_id' => $waiter->id,
        ]);
        $menuItem = $this->createMenuItem();

        $this
            ->actingAs($waiter)
            ->post(route('waiter.orders.store', $table), [
                'items' => [
                    $menuItem->id => ['quantity' => 0],
                ],
            ])
            ->assertSessionHasErrors('items');

        $this->assertDatabaseMissing('orders', [
            'restaurant_table_id' => $table->id,
            'waiter_id' => $waiter->id,
        ]);
    }

    public function test_waiter_cannot_order_unavailable_menu_item(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $table = RestaurantTable::create([
            'number' => 927,
            'seats' => 2,
            'status' => RestaurantTable::STATUS_FREE,
            'assigned_waiter_id' => $waiter->id,
        ]);
        $menuItem = $this->createMenuItem(available: false);

        $this
            ->actingAs($waiter)
            ->post(route('waiter.orders.store', $table), [
                'items' => [
                    $menuItem->id => ['quantity' => 1],
                ],
            ])
            ->assertSessionHasErrors('items');

        $this->assertDatabaseMissing('orders', [
            'restaurant_table_id' => $table->id,
            'waiter_id' => $waiter->id,
        ]);
    }

    public function test_waiter_cannot_view_other_waiters_order(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $otherWaiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $table = RestaurantTable::create([
            'number' => 924,
            'seats' => 4,
            'status' => RestaurantTable::STATUS_OCCUPIED,
            'assigned_waiter_id' => $otherWaiter->id,
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

    private function createMenuItem(
        string $name = 'Testowe danie',
        float $price = 25.00,
        bool $available = true,
    ): MenuItem {
        $category = MenuCategory::create([
            'name' => 'Testowa kategoria '.uniqid(),
            'sort_order' => 10,
            'is_active' => true,
        ]);

        return MenuItem::create([
            'menu_category_id' => $category->id,
            'name' => $name,
            'description' => null,
            'price' => $price,
            'production_area' => MenuItem::AREA_KITCHEN,
            'available' => $available,
        ]);
    }
}
