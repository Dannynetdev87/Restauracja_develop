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

    public function test_waiter_can_open_order_for_free_table_with_items(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $table = RestaurantTable::create([
            'number' => 921,
            'seats' => 2,
            'status' => RestaurantTable::STATUS_FREE,
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

    public function test_waiter_can_open_order_form_for_selected_table(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $table = RestaurantTable::create([
            'number' => 925,
            'seats' => 4,
            'status' => RestaurantTable::STATUS_FREE,
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

    public function test_waiter_order_form_shows_current_and_selected_total_summary(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $table = RestaurantTable::create([
            'number' => 928,
            'seats' => 4,
            'status' => RestaurantTable::STATUS_OCCUPIED,
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
