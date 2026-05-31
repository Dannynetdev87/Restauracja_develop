<?php

namespace App\Livewire\Production;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use Livewire\Component;

class KitchenCurrent extends Component
{
    public function render()
    {
        $order = Order::query()
            ->whereHas('items', function ($query) {
                $query
                    ->whereIn('status', [
                        OrderItem::STATUS_NEW,
                        OrderItem::STATUS_PREPARING,
                        OrderItem::STATUS_READY,
                    ])
                    ->whereHas('menuItem', fn ($query) => $query->where('production_area', MenuItem::AREA_KITCHEN));
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
                        ->whereHas('menuItem', fn ($query) => $query->where('production_area', MenuItem::AREA_KITCHEN))
                        ->with(['menuItem', 'statusHistory' => fn ($query) => $query->orderBy('created_at')])
                        ->orderByRaw("case status when 'preparing' then 1 when 'new' then 2 when 'ready' then 3 else 4 end")
                        ->orderBy('created_at');
                },
            ])
            ->orderBy('opened_at')
            ->first();

        return view('livewire.production.current', [
            'order' => $order,
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
}
