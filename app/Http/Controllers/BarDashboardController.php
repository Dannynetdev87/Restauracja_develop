<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BarDashboardController extends Controller
{
    public function index()
    {
        $items = OrderItem::query()
            ->with(['order.table', 'menuItem'])
            ->whereHas('menuItem', fn ($query) => $query->where('production_area', MenuItem::AREA_BAR))
            ->whereIn('status', [
                OrderItem::STATUS_NEW,
                OrderItem::STATUS_PREPARING,
                OrderItem::STATUS_READY,
            ])
            ->orderByRaw("case status when 'new' then 1 when 'preparing' then 2 when 'ready' then 3 else 4 end")
            ->orderBy('created_at')
            ->get()
            ->groupBy('status');

        return view('bar.index', [
            'columns' => [
                OrderItem::STATUS_NEW => [
                    'title' => 'Nowe',
                    'description' => 'Napoje oczekujące na rozpoczęcie przygotowania.',
                    'items' => $items->get(OrderItem::STATUS_NEW, collect()),
                ],
                OrderItem::STATUS_PREPARING => [
                    'title' => 'W przygotowaniu',
                    'description' => 'Napoje aktualnie obsługiwane przez bar.',
                    'items' => $items->get(OrderItem::STATUS_PREPARING, collect()),
                ],
                OrderItem::STATUS_READY => [
                    'title' => 'Gotowe',
                    'description' => 'Napoje gotowe do odbioru przez kelnera.',
                    'items' => $items->get(OrderItem::STATUS_READY, collect()),
                ],
            ],
        ]);
    }

    public function updateStatus(Request $request, OrderItem $orderItem)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:'.OrderItem::STATUS_PREPARING.','.OrderItem::STATUS_READY],
        ]);

        if ($orderItem->menuItem()->where('production_area', MenuItem::AREA_BAR)->doesntExist()) {
            abort(404);
        }

        $targetStatus = $validated['status'];
        $allowedTransitions = [
            OrderItem::STATUS_NEW => [OrderItem::STATUS_PREPARING],
            OrderItem::STATUS_PREPARING => [OrderItem::STATUS_READY],
        ];

        DB::transaction(function () use ($orderItem, $targetStatus, $allowedTransitions) {
            $orderItem->refresh();
            $oldStatus = $orderItem->status;

            if (! in_array($targetStatus, $allowedTransitions[$oldStatus] ?? [], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Nie można wykonać takiej zmiany statusu pozycji.',
                ]);
            }

            $orderItem->update([
                'status' => $targetStatus,
            ]);

            $orderItem->statusHistory()->create([
                'changed_by' => request()->user()->id,
                'old_status' => $oldStatus,
                'new_status' => $targetStatus,
            ]);

            $this->syncOrderStatus($orderItem->order()->with('items')->firstOrFail());
        });

        return redirect()
            ->route('bar.dashboard')
            ->with('success', 'Status napoju został zaktualizowany.');
    }

    private function syncOrderStatus(Order $order): void
    {
        if ($order->status === Order::STATUS_OPEN) {
            $order->update(['status' => Order::STATUS_IN_PROGRESS]);
        }

        $order->loadMissing('items');

        if ($order->items->isNotEmpty() && $order->items->every(fn (OrderItem $item) => in_array($item->status, [
            OrderItem::STATUS_READY,
            OrderItem::STATUS_DELIVERED,
        ], true))) {
            $order->update(['status' => Order::STATUS_READY]);
        }
    }
}
