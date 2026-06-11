<?php

namespace App\Livewire\Production;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

trait HandlesProductionItems
{
    abstract protected function productionArea(): string;

    public function updateItemStatus(int $orderItemId, string $targetStatus): void
    {
        if (! in_array($targetStatus, [OrderItem::STATUS_PREPARING, OrderItem::STATUS_READY], true)) {
            throw ValidationException::withMessages([
                'status' => 'Nie można wykonać takiej zmiany statusu pozycji.',
            ]);
        }

        $orderItem = $this->findProductionItem($orderItemId);
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

        session()->flash('success', 'Status pozycji został zaktualizowany.');
    }

    public function cancelItem(int $orderItemId): void
    {
        $orderItem = $this->findProductionItem($orderItemId);

        DB::transaction(function () use ($orderItem) {
            $orderItem->refresh();
            $oldStatus = $orderItem->status;

            if (! in_array($oldStatus, [OrderItem::STATUS_NEW, OrderItem::STATUS_PREPARING], true)) {
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

        session()->flash('success', 'Pozycja została oznaczona jako niemożliwa do przygotowania.');
    }

    private function findProductionItem(int $orderItemId): OrderItem
    {
        $orderItem = OrderItem::query()
            ->with('menuItem')
            ->findOrFail($orderItemId);

        if ($orderItem->menuItem?->production_area !== $this->productionArea()) {
            abort(404);
        }

        return $orderItem;
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
