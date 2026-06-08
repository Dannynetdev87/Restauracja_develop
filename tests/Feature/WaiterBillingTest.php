<?php

namespace Tests\Feature;

use App\Models\DiscountCode;
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
            'payment_method' => Payment::METHOD_CARD,
            'status' => Payment::STATUS_PAID,
        ]);
        $this->assertSame(Order::STATUS_SERVED, $order->refresh()->status);
        $this->assertSame(RestaurantTable::STATUS_OCCUPIED, $order->table->refresh()->status);
    }

    public function test_waiter_can_pay_selected_items_with_percent_discount_code(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $order = $this->createOrder($waiter, Order::STATUS_SERVED);
        $firstItem = $this->createOrderItem($order, 'Rabat procentowy', 2, 50.00);
        $secondItem = $this->createOrderItem($order, 'Bez rabatu jeszcze', 1, 20.00);
        $discountCode = DiscountCode::factory()->percent(10.00)->create(['code' => 'KOD10']);

        $this
            ->actingAs($waiter)
            ->post(route('waiter.orders.payments.store', $order), [
                'payment_method' => Payment::METHOD_CARD,
                'item_ids' => [$firstItem->id],
                'discount_code' => ' kod10 ',
            ])
            ->assertRedirect(route('waiter.orders.bill', $order));

        $payment = Payment::where('order_id', $order->id)->latest('id')->firstOrFail();

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'amount' => '90.00',
            'discount_amount' => '10.00',
            'discount_code_id' => $discountCode->id,
        ]);
    }

    public function test_fixed_discount_code_does_not_make_payment_amount_negative(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $order = $this->createOrder($waiter, Order::STATUS_SERVED);
        $item = $this->createOrderItem($order, 'Rabat kwotowy', 1, 30.00);
        $discountCode = DiscountCode::factory()->fixed(50.00)->create(['code' => 'FIX50']);

        $this
            ->actingAs($waiter)
            ->post(route('waiter.orders.payments.store', $order), [
                'payment_method' => Payment::METHOD_CASH,
                'item_ids' => [$item->id],
                'discount_code' => 'FIX50',
            ])
            ->assertRedirect(route('waiter.orders.bill', $order));

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'amount' => '0.00',
            'discount_amount' => '30.00',
            'discount_code_id' => $discountCode->id,
        ]);
    }

    public function test_tip_amount_is_saved_independently_from_discount(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $order = $this->createOrder($waiter, Order::STATUS_SERVED);
        $item = $this->createOrderItem($order, 'Rabat i napiwek', 1, 100.00);
        $discountCode = DiscountCode::factory()->percent(25.00)->create(['code' => 'TIP25']);

        $this
            ->actingAs($waiter)
            ->post(route('waiter.orders.payments.store', $order), [
                'payment_method' => Payment::METHOD_CARD,
                'item_ids' => [$item->id],
                'discount_code' => 'TIP25',
                'tip_amount' => '12.34',
            ])
            ->assertRedirect(route('waiter.orders.bill', $order));

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'amount' => '75.00',
            'discount_amount' => '25.00',
            'discount_code_id' => $discountCode->id,
            'tip_amount' => '12.34',
        ]);
    }

    public function test_partial_billing_with_discount_keeps_order_and_table_open(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $order = $this->createOrder($waiter, Order::STATUS_SERVED);
        $firstItem = $this->createOrderItem($order, 'Pierwsza rabatowana', 1, 100.00);
        $secondItem = $this->createOrderItem($order, 'Druga nieopłacona', 1, 40.00);
        DiscountCode::factory()->percent(50.00)->create(['code' => 'HALF']);

        $this
            ->actingAs($waiter)
            ->post(route('waiter.orders.payments.store', $order), [
                'payment_method' => Payment::METHOD_CARD,
                'item_ids' => [$firstItem->id],
                'discount_code' => 'HALF',
            ])
            ->assertRedirect(route('waiter.orders.bill', $order));

        $this->assertSame(Order::STATUS_SERVED, $order->refresh()->status);
        $this->assertSame(RestaurantTable::STATUS_OCCUPIED, $order->table->refresh()->status);
    }

    public function test_last_discounted_payment_closes_order_and_table_after_all_active_items_are_paid(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $order = $this->createOrder($waiter, Order::STATUS_SERVED);
        $firstItem = $this->createOrderItem($order, 'Pierwsza opłata', 1, 20.00);
        $secondItem = $this->createOrderItem($order, 'Ostatnia z rabatem', 1, 50.00);
        $this->createOrderItem($order, 'Anulowana z rabatem', 1, 99.00, OrderItem::STATUS_CANCELLED);
        DiscountCode::factory()->percent(20.00)->create(['code' => 'LAST20']);

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
                'discount_code' => 'LAST20',
            ])
            ->assertRedirect(route('waiter.orders.bill', $order))
            ->assertSessionHas('success', 'Całe zamówienie zostało opłacone, a stolik zwolniony.');

        $this->assertSame(Order::STATUS_PAID, $order->refresh()->status);
        $this->assertSame(RestaurantTable::STATUS_FREE, $order->table->refresh()->status);
    }

    public function test_unusable_discount_code_does_not_create_payment(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $cases = [
            DiscountCode::factory()->create(['code' => 'OFF', 'is_active' => false])->code,
            DiscountCode::factory()->create(['code' => 'OLD', 'expires_at' => now()->subMinute()])->code,
            DiscountCode::factory()->create(['code' => 'USED', 'usage_limit' => 1, 'used_count' => 1])->code,
        ];

        foreach ($cases as $code) {
            $order = $this->createOrder($waiter, Order::STATUS_SERVED);
            $item = $this->createOrderItem($order, 'Niepoprawny kod '.$code, 1, 25.00);

            $this
                ->actingAs($waiter)
                ->post(route('waiter.orders.payments.store', $order), [
                    'payment_method' => Payment::METHOD_CARD,
                    'item_ids' => [$item->id],
                    'discount_code' => $code,
                ])
                ->assertSessionHasErrors('discount_code');
        }
    }

    public function test_successful_discount_code_usage_increments_used_count(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $order = $this->createOrder($waiter, Order::STATUS_SERVED);
        $item = $this->createOrderItem($order, 'Licznik kodu', 1, 80.00);
        $discountCode = DiscountCode::factory()->percent(10.00)->create([
            'code' => 'COUNT10',
            'usage_limit' => 5,
            'used_count' => 2,
        ]);

        $this
            ->actingAs($waiter)
            ->post(route('waiter.orders.payments.store', $order), [
                'payment_method' => Payment::METHOD_CARD,
                'item_ids' => [$item->id],
                'discount_code' => 'COUNT10',
            ])
            ->assertRedirect(route('waiter.orders.bill', $order));

        $this->assertSame(3, $discountCode->refresh()->used_count);
    }

    public function test_order_closes_after_last_active_item_is_paid(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $order = $this->createOrder($waiter, Order::STATUS_SERVED);
        $firstItem = $this->createOrderItem($order, 'Pierwsza część', 1, 15.00);
        $secondItem = $this->createOrderItem($order, 'Druga część', 1, 17.00);

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

        $this->assertSame(Order::STATUS_PAID, $order->refresh()->status);
        $this->assertSame(RestaurantTable::STATUS_FREE, $order->table->refresh()->status);
    }

    public function test_waiter_cannot_pay_already_paid_item(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $order = $this->createOrder($waiter, Order::STATUS_SERVED);
        $paidItem = $this->createOrderItem($order, 'Raz opłacone', 1, 22.00);
        $this->createOrderItem($order, 'Jeszcze nieopłacone', 1, 18.00);

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
    }

    public function test_waiter_must_select_at_least_one_item_for_payment(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $order = $this->createOrder($waiter, Order::STATUS_SERVED);

        $this
            ->actingAs($waiter)
            ->post(route('waiter.orders.payments.store', $order), [
                'payment_method' => Payment::METHOD_CARD,
                'item_ids' => [],
            ])
            ->assertSessionHasErrors('item_ids');
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
    }

    /** @test */
    public function test_waiter_can_pay_partial_quantity_of_an_item_and_keep_order_open(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $order = $this->createOrder($waiter, Order::STATUS_SERVED);
        $item = $this->createOrderItem($order, 'Piwo z beczki', 4, 10.00);

        // Osoba płaci tylko za 2 z 4 sztuk (2 * 10.00 = 20.00)
        $this->actingAs($waiter)
            ->post(route('waiter.orders.payments.store', $order), [
                'payment_method' => Payment::METHOD_BLIK,
                'item_ids' => [$item->id],
                'quantities' => [
                    $item->id => 2
                ]
            ])
            ->assertRedirect(route('waiter.orders.bill', $order))
            ->assertSessionHas('success', 'Płatność za wybrane pozycje została zapisana.');

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'amount' => '20.00',
        ]);

        $this->assertSame(Order::STATUS_SERVED, $order->refresh()->status);
    }

    /** @test */
    public function test_final_partial_quantity_payment_closes_order_and_frees_table(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $order = $this->createOrder($waiter, Order::STATUS_SERVED);
        $item = $this->createOrderItem($order, 'Kawa czarna', 2, 12.00);

        // Pierwsza płatność za 1 sztukę
        $payment1 = $order->payments()->create([
            'amount' => 12.00,
            'payment_method' => Payment::METHOD_CARD,
            'status' => Payment::STATUS_PAID,
            'paid_at' => now(),
        ]);
        $payment1->orderItems()->attach($item->id, ['quantity' => 1]);

        // Druga płatność kończy rozliczanie pozycji (ostatnia 1 sztuka)
        $this->actingAs($waiter)
            ->post(route('waiter.orders.payments.store', $order), [
                'payment_method' => Payment::METHOD_CASH,
                'item_ids' => [$item->id],
                'quantities' => [
                    $item->id => 1
                ]
            ])
            ->assertRedirect(route('waiter.orders.bill', $order))
            ->assertSessionHas('success', 'Całe zamówienie zostało opłacone, a stolik zwolniony.');

        $this->assertSame(Order::STATUS_PAID, $order->refresh()->status);
        $this->assertSame(RestaurantTable::STATUS_FREE, $order->table->refresh()->status);
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
