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
    public function selectCurrent(OrderItem $item)
    {
        if ($item->menuItem()->where('production_area', MenuItem::AREA_BAR)->doesntExist()) {
            abort(404);
        }

        if (! in_array($item->status, [
            OrderItem::STATUS_NEW,
            OrderItem::STATUS_PREPARING,
            OrderItem::STATUS_READY,
        ], true)) {
            abort(404);
        }

        session([
            'selected_bar_order_id' => $item->order_id,
            'selected_bar_order_item_id' => $item->id,
        ]);

        return redirect()->route('bar.current');
    }

    public function current()
    {
        $order = Order::query()
            ->whereHas('items', function ($query) {
                $query
                    ->whereIn('status', [
                        OrderItem::STATUS_NEW,
                        OrderItem::STATUS_PREPARING,
                        OrderItem::STATUS_READY,
                    ])
                    ->whereHas('menuItem', fn ($query) => $query->where('production_area', MenuItem::AREA_BAR));
            })
            ->with([
                'table',
                'items' => function ($query) {
                    $query
                        ->whereIn('status', [
                            OrderItem::STATUS_NEW,
                            OrderItem::STATUS_PREPARING,
                            OrderItem::STATUS_READY,
                        ])
                        ->whereHas('menuItem', fn ($query) => $query->where('production_area', MenuItem::AREA_BAR))
                        ->with(['menuItem', 'statusHistory' => fn ($query) => $query->orderBy('created_at')])
                        ->orderByRaw("case status when 'preparing' then 1 when 'new' then 2 when 'ready' then 3 else 4 end")
                        ->orderBy('created_at');
                },
            ])
            ->orderBy('opened_at')
            ->first();

        return view('bar.current', [
            'order' => $order,
        ]);
    }

    public function index()
    {
        $items = OrderItem::query()
            ->with(['order.table', 'menuItem', 'statusHistory' => fn ($query) => $query->orderBy('created_at')])
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
            'redirect_to' => ['nullable', 'in:bar.current,bar.dashboard'],
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
            ->route($validated['redirect_to'] ?? 'bar.dashboard')
            ->with('success', 'Status napoju został zaktualizowany.');
    }

    public function cancel(Request $request, OrderItem $orderItem)
    {
        $validated = $request->validate([
            'redirect_to' => ['nullable', 'in:bar.current,bar.dashboard'],
        ]);

        if ($orderItem->menuItem()->where('production_area', MenuItem::AREA_BAR)->doesntExist()) {
            abort(404);
        }

        DB::transaction(function () use ($orderItem) {
            $orderItem->refresh();
            $oldStatus = $orderItem->status;

            if (! in_array($oldStatus, [
                OrderItem::STATUS_NEW,
                OrderItem::STATUS_PREPARING,
            ], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Anulować można tylko pozycję nową albo w przygotowaniu.',
                ]);
            }

            $orderItem->update([
                'status' => OrderItem::STATUS_CANCELLED,
            ]);

            $orderItem->statusHistory()->create([
                'changed_by' => request()->user()->id,
                'old_status' => $oldStatus,
                'new_status' => OrderItem::STATUS_CANCELLED,
            ]);

            $this->syncOrderStatus($orderItem->order()->with('items')->firstOrFail());
        });

        return redirect()
            ->route($validated['redirect_to'] ?? 'bar.dashboard')
            ->with('success', 'Pozycja została oznaczona jako niemożliwa do przygotowania.');
    }

    private function syncOrderStatus(Order $order): void
    {
        if ($order->status === Order::STATUS_OPEN) {
            $order->update(['status' => Order::STATUS_IN_PROGRESS]);
        }

        $order->loadMissing('items');

        $activeItems = $order->items->where('status', '!=', OrderItem::STATUS_CANCELLED);

        if ($activeItems->isNotEmpty() && $activeItems->every(fn (OrderItem $item) => in_array($item->status, [
            OrderItem::STATUS_READY,
            OrderItem::STATUS_DELIVERED,
        ], true))) {
            $order->update(['status' => Order::STATUS_READY]);
        }
    }
}
