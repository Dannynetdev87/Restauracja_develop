<?php

namespace App\Livewire\Production;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use Livewire\Component;

class KitchenCurrent extends Component
{
    private const SELECTED_ORDER_KEY = 'selected_kitchen_order_id';

    private const SELECTED_ORDER_ITEM_KEY = 'selected_kitchen_order_item_id';

    public function render()
    {
        $order = $this->selectedOrder() ?? $this->oldestOrder();

        return view('livewire.production.current', [
            'order' => $order,
            'items' => $order?->items ?? collect(),
            'panelLabel' => 'Panel kuchni',
            'heading' => 'Aktualne zamówienie',
            'description' => 'Widok pokazuje najstarsze aktywne zamówienie z pozycjami kuchennymi. Pełną kolejkę znajdziesz w dashboardzie.',
            'dashboardRouteName' => 'kitchen.dashboard',
            'statusRouteName' => 'kitchen.order-items.status',
            'cancelRouteName' => 'kitchen.order-items.cancel',
            'emptyHeading' => 'Brak aktywnych pozycji kuchni',
            'emptyDescription' => 'Nowe zamówienia pojawią się tutaj automatycznie po dodaniu pozycji przez kelnera.',
        ]);
    }

    private function selectedOrder(): ?Order
    {
        $selectedOrderId = session(self::SELECTED_ORDER_KEY);
        $selectedItemId = session(self::SELECTED_ORDER_ITEM_KEY);

        if (! $selectedOrderId || ! $selectedItemId) {
            return null;
        }

        $order = $this->activeOrderQuery((int) $selectedOrderId, (int) $selectedItemId)->first();

        if (! $order) {
            session()->forget([self::SELECTED_ORDER_KEY, self::SELECTED_ORDER_ITEM_KEY]);
        }

        return $order;
    }

    private function oldestOrder(): ?Order
    {
        return $this->activeOrderQuery()
            ->orderBy('opened_at')
            ->first();
    }

    private function activeOrderQuery(?int $orderId = null, ?int $itemId = null)
    {
        return Order::query()
            ->when($orderId, fn ($query) => $query->whereKey($orderId))
            ->whereHas('items', function ($query) use ($itemId) {
                $this->activeProductionItems($query, $itemId);
            })
            ->with([
                'table',
                'items' => function ($query) use ($itemId) {
                    $this->activeProductionItems($query, $itemId)
                        ->with(['menuItem', 'statusHistory' => fn ($query) => $query->orderBy('created_at')])
                        ->orderByRaw("case status when 'preparing' then 1 when 'new' then 2 when 'ready' then 3 else 4 end")
                        ->orderBy('created_at');
                },
            ]);
    }

    private function activeProductionItems($query, ?int $itemId = null)
    {
        return $query
            ->whereIn('status', [
                OrderItem::STATUS_NEW,
                OrderItem::STATUS_PREPARING,
                OrderItem::STATUS_READY,
            ])
            ->whereHas('menuItem', fn ($query) => $query->where('production_area', MenuItem::AREA_KITCHEN))
            ->when($itemId, fn ($query) => $query->whereKey($itemId));
    }
}
