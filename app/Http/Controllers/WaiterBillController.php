<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\RestaurantTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class WaiterBillController extends Controller
{
    public function show(Order $order)
    {
        $this->authorizeWaiterOrder($order);

        return view('waiter.orders.bill', [
            'order' => $order->load(['table', 'waiter', 'items.menuItem', 'payments']),
            'paymentMethods' => $this->paymentMethods(),
        ]);
    }

    public function storePayment(Request $request, Order $order)
    {
        $this->authorizeWaiterOrder($order);

        $validated = $request->validate([
            'payment_method' => ['required', Rule::in(array_keys($this->paymentMethods()))],
        ]);

        DB::transaction(function () use ($order, $validated) {
            $order = Order::query()
                ->with(['items', 'payments', 'table'])
                ->lockForUpdate()
                ->findOrFail($order->id);

            if ($order->waiter_id !== request()->user()->id) {
                abort(403);
            }

            if ($order->payments()->where('status', Payment::STATUS_PAID)->exists()) {
                throw ValidationException::withMessages([
                    'payment_method' => 'To zamówienie zostało już opłacone.',
                ]);
            }

            if ($order->status !== Order::STATUS_SERVED) {
                throw ValidationException::withMessages([
                    'payment_method' => 'Zamówienie można opłacić dopiero po dostarczeniu wszystkich pozycji.',
                ]);
            }

            $total = $order->total();

            if ($total <= 0) {
                throw ValidationException::withMessages([
                    'payment_method' => 'Nie można opłacić pustego zamówienia.',
                ]);
            }

            $paidAt = now();

            $order->payments()->create([
                'amount' => $total,
                'payment_method' => $validated['payment_method'],
                'status' => Payment::STATUS_PAID,
                'paid_at' => $paidAt,
            ]);

            $order->update([
                'status' => Order::STATUS_PAID,
                'paid_at' => $paidAt,
                'closed_at' => $paidAt,
            ]);

            $order->table->update([
                'status' => RestaurantTable::STATUS_FREE,
            ]);
        });

        return redirect()
            ->route('waiter.orders.bill', $order)
            ->with('success', 'Płatność została zapisana, a stolik zwolniony.');
    }

    private function authorizeWaiterOrder(Order $order): void
    {
        if ($order->waiter_id !== request()->user()->id) {
            abort(403);
        }
    }

    private function paymentMethods(): array
    {
        return [
            Payment::METHOD_CASH => 'Gotówka',
            Payment::METHOD_CARD => 'Karta',
            Payment::METHOD_BLIK => 'BLIK',
            Payment::METHOD_OTHER => 'Inna metoda',
        ];
    }
}
