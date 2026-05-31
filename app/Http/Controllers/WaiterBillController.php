<?php

namespace App\Http\Controllers;

use App\Models\DiscountCode;
use App\Models\Order;
use App\Models\OrderItem;
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
            'order' => $order->load(['table', 'waiter', 'items.menuItem', 'items.payments', 'payments.orderItems', 'payments.discountCode']),
            'paymentMethods' => $this->paymentMethods(),
        ]);
    }

    public function storePayment(Request $request, Order $order)
    {
        $this->authorizeWaiterOrder($order);

        $validated = $request->validate([
            'payment_method' => ['required', Rule::in(array_keys($this->paymentMethods()))],
            'item_ids' => ['required', 'array', 'min:1'],
            'item_ids.*' => ['required', 'integer', Rule::exists('order_items', 'id')],
            'tip_amount' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
            'discount_code' => ['nullable', 'string', 'max:50'],
        ]);

        $orderFullyPaid = DB::transaction(function () use ($order, $validated): bool {
            $order = Order::query()
                ->with('table')
                ->lockForUpdate()
                ->findOrFail($order->id);

            if ($order->waiter_id !== request()->user()->id) {
                abort(403);
            }

            if (in_array($order->status, [Order::STATUS_PAID, Order::STATUS_CLOSED, Order::STATUS_CANCELLED], true)) {
                throw ValidationException::withMessages([
                    'payment_method' => 'To zamówienie jest już rozliczone albo zamknięte.',
                ]);
            }

            if ($order->status !== Order::STATUS_SERVED) {
                throw ValidationException::withMessages([
                    'payment_method' => 'Zamówienie można opłacić dopiero po dostarczeniu wszystkich pozycji.',
                ]);
            }

            $selectedItemIds = collect($validated['item_ids'])
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();

            $itemsToPay = OrderItem::query()
                ->where('order_id', $order->id)
                ->whereIn('id', $selectedItemIds)
                ->with(['menuItem', 'payments'])
                ->lockForUpdate()
                ->get();

            if ($itemsToPay->count() !== $selectedItemIds->count()) {
                throw ValidationException::withMessages([
                    'item_ids' => 'Wybrano pozycję spoza tego zamówienia.',
                ]);
            }

            if ($itemsToPay->contains(fn (OrderItem $item) => $item->status === OrderItem::STATUS_CANCELLED)) {
                throw ValidationException::withMessages([
                    'item_ids' => 'Nie można opłacić anulowanych pozycji.',
                ]);
            }

            $paidItem = $itemsToPay->first(fn (OrderItem $item) => $item->isPaid());

            if ($paidItem) {
                throw ValidationException::withMessages([
                    'item_ids' => 'Pozycja '.$paidItem->menuItem->name.' została już opłacona.',
                ]);
            }

            $amount = $itemsToPay->sum(fn (OrderItem $item) => $item->subtotal());

            if ($amount <= 0) {
                throw ValidationException::withMessages([
                    'item_ids' => 'Suma wybranych pozycji musi być większa od zera.',
                ]);
            }

            $paidAt = now();
            $tipAmount = round((float) ($validated['tip_amount'] ?? 0), 2);
            $discountCode = null;
            $discountAmount = 0.0;
            $discountCodeValue = strtoupper(trim((string) ($validated['discount_code'] ?? '')));

            if ($discountCodeValue !== '') {
                $discountCode = DiscountCode::query()
                    ->where('code', $discountCodeValue)
                    ->lockForUpdate()
                    ->first();

                if (! $discountCode) {
                    throw ValidationException::withMessages([
                        'discount_code' => 'Podany kod rabatowy nie istnieje.',
                    ]);
                }

                if (! $discountCode->isUsable($paidAt)) {
                    throw ValidationException::withMessages([
                        'discount_code' => 'Podany kod rabatowy nie jest aktywny.',
                    ]);
                }

                $discountAmount = $discountCode->calculateDiscount($amount);
            }

            $payment = $order->payments()->create([
                'amount' => round(max(0, $amount - $discountAmount), 2),
                'tip_amount' => $tipAmount,
                'discount_code_id' => $discountCode?->id,
                'discount_amount' => $discountAmount,
                'payment_method' => $validated['payment_method'],
                'status' => Payment::STATUS_PAID,
                'paid_at' => $paidAt,
            ]);

            $payment->orderItems()->attach($itemsToPay->pluck('id')->all());

            if ($discountCode) {
                $discountCode->increment('used_count');
            }

            $hasUnpaidActiveItems = OrderItem::query()
                ->where('order_id', $order->id)
                ->where('status', '!=', OrderItem::STATUS_CANCELLED)
                ->whereDoesntHave('payments', fn ($query) => $query->where('status', Payment::STATUS_PAID))
                ->exists();

            if (! $hasUnpaidActiveItems) {
                $order->update([
                    'status' => Order::STATUS_PAID,
                    'paid_at' => $paidAt,
                    'closed_at' => $paidAt,
                ]);

                $order->table->update([
                    'status' => RestaurantTable::STATUS_FREE,
                ]);

                return true;
            }

            return false;
        });

        $message = $orderFullyPaid
            ? 'Całe zamówienie zostało opłacone, a stolik zwolniony.'
            : 'Płatność za wybrane pozycje została zapisana.';

        return redirect()
            ->route('waiter.orders.bill', $order)
            ->with('success', $message);
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
