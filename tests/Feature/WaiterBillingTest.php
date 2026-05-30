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

    public function test_cancelled_items_are_excluded_from_bill_total_and_payment_amount(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $order = $this->createOrder($waiter, Order::STATUS_SERVED);
        $billableItem = $this->createOrderItem($order, 'Danie rozliczane', 2, 20.00);
        $this->createOrderItem($order, 'Danie anulowane', 3, 50.00, OrderItem::STATUS_CANCELLED);

        $this
            ->actingAs($waiter)
            ->get(route('waiter.orders.bill', $order))
            ->assertOk()
            ->assertSee('Danie anulowane')
            ->assertSee('Anulowane')
            ->assertSee('40,00 zł')
            ->assertDontSee('150,00 zł');

        $this
            ->actingAs($waiter)
            ->post(route('waiter.orders.payments.store', $order), [
                'payment_method' => Payment::METHOD_CARD,
                'item_ids' => [$billableItem->id],
            ])
            ->assertRedirect(route('waiter.orders.bill', $order));

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'amount' => '40.00',
            'payment_method' => Payment::METHOD_CARD,
            'status' => Payment::STATUS_PAID,
        ]);
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
        $item = $this->createOrderItem($order, 'Gotowe danie testowe', 1, 30.00, OrderItem::STATUS_READY);

        $this
            ->actingAs($waiter)
            ->post(route('waiter.orders.payments.store', $order), [
                'payment_method' => Payment::METHOD_CARD,
                'item_ids' => [$item->id],
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
        $firstItem = $this->createOrderItem($order, 'Schabowy testowy', 2, 32.00);
        $secondItem = $this->createOrderItem($order, 'Lemoniada testowa', 1, 12.00);

        $this
            ->actingAs($waiter)
            ->post(route('waiter.orders.payments.store', $order), [
                'payment_method' => Payment::METHOD_CASH,
                'item_ids' => [$firstItem->id, $secondItem->id],
                'tip_amount' => '5.50',
            ])
            ->assertRedirect(route('waiter.orders.bill', $order));

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'amount' => '76.00',
            'tip_amount' => '5.50',
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

    public function test_waiter_can_pay_selected_items_and_keep_order_open(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $order = $this->createOrder($waiter, Order::STATUS_SERVED);
        $firstItem = $this->createOrderItem($order, 'Pierogi częściowe', 1, 21.00);
        $secondItem = $this->createOrderItem($order, 'Kompot częściowy', 2, 8.00);

        $this
            ->actingAs($waiter)
            ->post(route('waiter.orders.payments.store', $order), [
                'payment_method' => Payment::METHOD_CARD,
                'item_ids' => [$firstItem->id],
            ])
            ->assertRedirect(route('waiter.orders.bill', $order))
            ->assertSessionHas('success', 'Płatność za wybrane pozycje została zapisana.');

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'amount' => '21.00',
            'tip_amount' => '0.00',
            'payment_method' => Payment::METHOD_CARD,
            'status' => Payment::STATUS_PAID,
        ]);
        $this->assertTrue($firstItem->refresh()->isPaid());
        $this->assertFalse($secondItem->refresh()->isPaid());
        $this->assertSame(Order::STATUS_SERVED, $order->refresh()->status);
        $this->assertSame(RestaurantTable::STATUS_OCCUPIED, $order->table->refresh()->status);
    }

    public function test_order_closes_after_last_active_item_is_paid(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $order = $this->createOrder($waiter, Order::STATUS_SERVED);
        $firstItem = $this->createOrderItem($order, 'Pierwsza część', 1, 15.00);
        $secondItem = $this->createOrderItem($order, 'Druga część', 1, 17.00);
        $this->createOrderItem($order, 'Anulowana część', 1, 99.00, OrderItem::STATUS_CANCELLED);

        $this
            ->actingAs($waiter)
            ->post(route('waiter.orders.payments.store', $order), [
                'payment_method' => Payment::METHOD_CARD,
                'item_ids' => [$firstItem->id],
            ])
            ->assertRedirect(route('waiter.orders.bill', $order));

        $this
            ->actingAs($waiter)
            ->post(route('waiter.orders.payments.store', $order), [
                'payment_method' => Payment::METHOD_CASH,
                'item_ids' => [$secondItem->id],
                'tip_amount' => '3.25',
            ])
            ->assertRedirect(route('waiter.orders.bill', $order))
            ->assertSessionHas('success', 'Całe zamówienie zostało opłacone, a stolik zwolniony.');

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'amount' => '17.00',
            'tip_amount' => '3.25',
            'payment_method' => Payment::METHOD_CASH,
        ]);
        $this->assertSame(Order::STATUS_PAID, $order->refresh()->status);
        $this->assertNotNull($order->paid_at);
        $this->assertNotNull($order->closed_at);
        $this->assertSame(RestaurantTable::STATUS_FREE, $order->table->refresh()->status);
    }

    public function test_waiter_cannot_pay_already_paid_item(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $order = $this->createOrder($waiter, Order::STATUS_SERVED);
        $paidItem = $this->createOrderItem($order, 'Raz opłacone', 1, 22.00);
        $remainingItem = $this->createOrderItem($order, 'Jeszcze nieopłacone', 1, 18.00);

        $this
            ->actingAs($waiter)
            ->post(route('waiter.orders.payments.store', $order), [
                'payment_method' => Payment::METHOD_CARD,
                'item_ids' => [$paidItem->id],
            ])
            ->assertRedirect(route('waiter.orders.bill', $order));

        $this
            ->actingAs($waiter)
            ->post(route('waiter.orders.payments.store', $order), [
                'payment_method' => Payment::METHOD_CARD,
                'item_ids' => [$paidItem->id],
            ])
            ->assertSessionHasErrors('item_ids');

        $this->assertFalse($remainingItem->refresh()->isPaid());
        $this->assertSame(1, Payment::where('order_id', $order->id)->where('status', Payment::STATUS_PAID)->count());
    }

    public function test_waiter_cannot_pay_cancelled_item(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $order = $this->createOrder($waiter, Order::STATUS_SERVED);
        $cancelledItem = $this->createOrderItem($order, 'Anulowany rachunek', 1, 20.00, OrderItem::STATUS_CANCELLED);

        $this
            ->actingAs($waiter)
            ->post(route('waiter.orders.payments.store', $order), [
                'payment_method' => Payment::METHOD_CARD,
                'item_ids' => [$cancelledItem->id],
            ])
            ->assertSessionHasErrors('item_ids');

        $this->assertDatabaseMissing('payments', [
            'order_id' => $order->id,
            'status' => Payment::STATUS_PAID,
        ]);
    }

    public function test_waiter_cannot_pay_item_from_another_order(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $order = $this->createOrder($waiter, Order::STATUS_SERVED);
        $otherOrder = $this->createOrder($waiter, Order::STATUS_SERVED);
        $foreignItem = $this->createOrderItem($otherOrder, 'Cudzy element rachunku', 1, 20.00);

        $this
            ->actingAs($waiter)
            ->post(route('waiter.orders.payments.store', $order), [
                'payment_method' => Payment::METHOD_CARD,
                'item_ids' => [$foreignItem->id],
            ])
            ->assertSessionHasErrors('item_ids');

        $this->assertDatabaseMissing('payments', [
            'order_id' => $order->id,
            'status' => Payment::STATUS_PAID,
        ]);
    }

    public function test_waiter_must_select_at_least_one_item_for_payment(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $order = $this->createOrder($waiter, Order::STATUS_SERVED);
        $this->createOrderItem($order, 'Niekliknięte danie', 1, 20.00);

        $this
            ->actingAs($waiter)
            ->post(route('waiter.orders.payments.store', $order), [
                'payment_method' => Payment::METHOD_CARD,
                'item_ids' => [],
            ])
            ->assertSessionHasErrors('item_ids');

        $this->assertDatabaseMissing('payments', [
            'order_id' => $order->id,
            'status' => Payment::STATUS_PAID,
        ]);
    }

    public function test_waiter_cannot_pay_same_order_twice(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $order = $this->createOrder($waiter, Order::STATUS_SERVED);
        $item = $this->createOrderItem($order, 'Deser testowy', 1, 19.00);

        $this
            ->actingAs($waiter)
            ->post(route('waiter.orders.payments.store', $order), [
                'payment_method' => Payment::METHOD_BLIK,
                'item_ids' => [$item->id],
            ])
            ->assertRedirect(route('waiter.orders.bill', $order));

        $this
            ->actingAs($waiter)
            ->post(route('waiter.orders.payments.store', $order), [
                'payment_method' => Payment::METHOD_BLIK,
                'item_ids' => [$item->id],
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
