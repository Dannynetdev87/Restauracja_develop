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

class WaiterBillingTest extends TestCase
{
    use DatabaseTransactions;

    public function test_waiter_can_see_bill_for_own_order_with_correct_total(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $order = $this->createOrder($waiter, Order::STATUS_SERVED);
        $this->createOrderItem($order, 'Pierogi testowe', 2, 18.50);
        $this->createOrderItem($order, 'Kompot testowy', 1, 7.00);

        $this
            ->actingAs($waiter)
            ->get(route('waiter.orders.bill', $order))
            ->assertOk()
            ->assertSee('Rachunek #'.$order->id)
            ->assertSee('Pierogi testowe')
            ->assertSee('Kompot testowy')
            ->assertSee('44,00 zł')
            ->assertSee('Zatwierdź płatność');
    }

    public function test_waiter_cannot_see_other_waiters_bill(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $otherWaiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $order = $this->createOrder($otherWaiter, Order::STATUS_SERVED);

        $this
            ->actingAs($waiter)
            ->get(route('waiter.orders.bill', $order))
            ->assertForbidden();
    }

    public function test_waiter_cannot_pay_order_before_all_items_are_served(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $order = $this->createOrder($waiter, Order::STATUS_READY);
        $this->createOrderItem($order, 'Gotowe danie testowe', 1, 30.00, OrderItem::STATUS_READY);

        $this
            ->actingAs($waiter)
            ->post(route('waiter.orders.payments.store', $order), [
                'payment_method' => Payment::METHOD_CARD,
            ])
            ->assertSessionHasErrors('payment_method');

        $this->assertDatabaseMissing('payments', [
            'order_id' => $order->id,
            'status' => Payment::STATUS_PAID,
        ]);
        $this->assertDatabaseHas('restaurant_tables', [
            'id' => $order->restaurant_table_id,
            'status' => RestaurantTable::STATUS_OCCUPIED,
        ]);
    }

    public function test_waiter_can_record_payment_and_free_table(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $order = $this->createOrder($waiter, Order::STATUS_SERVED);
        $this->createOrderItem($order, 'Schabowy testowy', 2, 32.00);
        $this->createOrderItem($order, 'Lemoniada testowa', 1, 12.00);

        $this
            ->actingAs($waiter)
            ->post(route('waiter.orders.payments.store', $order), [
                'payment_method' => Payment::METHOD_CASH,
            ])
            ->assertRedirect(route('waiter.orders.bill', $order));

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'amount' => '76.00',
            'payment_method' => Payment::METHOD_CASH,
            'status' => Payment::STATUS_PAID,
        ]);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => Order::STATUS_PAID,
        ]);
        $this->assertNotNull($order->fresh()->paid_at);
        $this->assertNotNull($order->fresh()->closed_at);
        $this->assertDatabaseHas('restaurant_tables', [
            'id' => $order->restaurant_table_id,
            'status' => RestaurantTable::STATUS_FREE,
        ]);
    }

    public function test_waiter_cannot_pay_same_order_twice(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $order = $this->createOrder($waiter, Order::STATUS_SERVED);
        $this->createOrderItem($order, 'Deser testowy', 1, 19.00);

        $this
            ->actingAs($waiter)
            ->post(route('waiter.orders.payments.store', $order), [
                'payment_method' => Payment::METHOD_BLIK,
            ])
            ->assertRedirect(route('waiter.orders.bill', $order));

        $this
            ->actingAs($waiter)
            ->post(route('waiter.orders.payments.store', $order), [
                'payment_method' => Payment::METHOD_BLIK,
            ])
            ->assertSessionHasErrors('payment_method');

        $this->assertSame(1, Payment::where('order_id', $order->id)->where('status', Payment::STATUS_PAID)->count());
    }

    public function test_waiter_cannot_add_items_to_served_order(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $order = $this->createOrder($waiter, Order::STATUS_SERVED);
        $menuItem = $this->createMenuItem('Nowa kawa testowa', 12.00);

        $this
            ->actingAs($waiter)
            ->post(route('waiter.orders.store', $order->table), [
                'items' => [
                    $menuItem->id => ['quantity' => 1],
                ],
            ])
            ->assertSessionHasErrors('table');

        $this->assertDatabaseMissing('order_items', [
            'order_id' => $order->id,
            'menu_item_id' => $menuItem->id,
        ]);
    }

    private function createOrder(User $waiter, string $status): Order
    {
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
        int $quantity,
        float $price,
        string $status = OrderItem::STATUS_DELIVERED,
    ): OrderItem {
        $menuItem = $this->createMenuItem($name, $price);

        return OrderItem::create([
            'order_id' => $order->id,
            'menu_item_id' => $menuItem->id,
            'quantity' => $quantity,
            'unit_price' => $price,
            'notes' => null,
            'status' => $status,
        ]);
    }

    private function createMenuItem(string $name, float $price): MenuItem
    {
        $category = MenuCategory::firstOrCreate(
            ['name' => 'Testowa kategoria rachunków'],
            ['sort_order' => 10, 'is_active' => true],
        );

        return MenuItem::create([
            'menu_category_id' => $category->id,
            'name' => $name,
            'description' => null,
            'price' => $price,
            'production_area' => MenuItem::AREA_KITCHEN,
            'available' => true,
        ]);
    }
}
