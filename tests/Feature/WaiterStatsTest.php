<?php

namespace Tests\Feature;

use App\Http\Controllers\WaiterStatsController;
use App\Models\Order;
use App\Models\Payment;
use App\Models\RestaurantTable;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class WaiterStatsTest extends TestCase
{
    use DatabaseTransactions;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_waiter_stats_route_is_registered_for_backend_integration(): void
    {
        $this->assertTrue(Route::has('waiter.stats'));
        $this->assertSame(url('/waiter/stats'), route('waiter.stats'));
    }

    public function test_waiter_stats_page_renders_tip_totals(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);

        $this->createPayment($waiter, 12.50, Payment::STATUS_PAID);

        $this
            ->actingAs($waiter)
            ->get(route('waiter.stats'))
            ->assertOk()
            ->assertSee('Moje napiwki')
            ->assertSee('12,50 zł')
            ->assertSee('Brak aktywnej zmiany');
    }

    public function test_waiter_dashboard_links_to_tip_stats(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);

        $this
            ->actingAs($waiter)
            ->get(route('waiter.dashboard'))
            ->assertOk()
            ->assertSee('Napiwki')
            ->assertSee(route('waiter.stats'), false);
    }

    public function test_stats_data_counts_only_paid_tips_for_selected_waiter(): void
    {
        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);
        $otherWaiter = User::factory()->create(['role' => User::ROLE_WAITER]);

        $this->createPayment($waiter, 10.50, Payment::STATUS_PAID);
        $this->createPayment($waiter, 4.25, Payment::STATUS_PENDING);
        $this->createPayment($otherWaiter, 99.99, Payment::STATUS_PAID);

        $stats = app(WaiterStatsController::class)->statsData($waiter);

        $this->assertSame(10.50, $stats['totalTips']);
        $this->assertSame(0.0, $stats['shiftTips']);
        $this->assertSame(0, $stats['shiftOrdersCount']);
        $this->assertFalse($stats['hasActiveShift']);
    }

    public function test_stats_data_sums_current_shift_tips_and_counts_unique_orders(): void
    {
        Carbon::setTestNow(Carbon::today()->setTime(12, 0));

        $waiter = User::factory()->create(['role' => User::ROLE_WAITER]);

        Schedule::create([
            'user_id' => $waiter->id,
            'date' => now()->toDateString(),
            'start_time' => '10:00',
            'end_time' => '18:00',
        ]);

        $order = $this->createOrder($waiter);
        $this->createPayment($waiter, 5.00, Payment::STATUS_PAID, now()->copy()->subHour(), $order);
        $this->createPayment($waiter, 3.00, Payment::STATUS_PAID, now()->copy()->subMinutes(30), $order);
        $this->createPayment($waiter, 7.00, Payment::STATUS_PAID, now()->copy()->subDay());

        $stats = app(WaiterStatsController::class)->statsData($waiter);

        $this->assertSame(15.0, $stats['totalTips']);
        $this->assertSame(8.0, $stats['shiftTips']);
        $this->assertSame(1, $stats['shiftOrdersCount']);
        $this->assertTrue($stats['hasActiveShift']);
    }

    private function createPayment(
        User $waiter,
        float $tipAmount,
        string $status,
        mixed $paidAt = null,
        ?Order $order = null,
    ): Payment {
        $order ??= $this->createOrder($waiter);

        return Payment::create([
            'order_id' => $order->id,
            'amount' => 25.00,
            'tip_amount' => $tipAmount,
            'payment_method' => Payment::METHOD_CASH,
            'status' => $status,
            'paid_at' => $paidAt ?? now(),
        ]);
    }

    private function createOrder(User $waiter): Order
    {
        $table = RestaurantTable::create([
            'number' => random_int(100000, 999999),
            'seats' => 4,
            'status' => RestaurantTable::STATUS_OCCUPIED,
            'assigned_waiter_id' => $waiter->id,
        ]);

        return Order::create([
            'restaurant_table_id' => $table->id,
            'waiter_id' => $waiter->id,
            'status' => Order::STATUS_SERVED,
            'opened_at' => now(),
        ]);
    }
}
