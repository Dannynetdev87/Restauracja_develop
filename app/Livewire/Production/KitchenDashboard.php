<?php

namespace App\Livewire\Production;

use App\Models\MenuItem;
use App\Models\OrderItem;
use Livewire\Component;

class KitchenDashboard extends Component
{
    public function render()
    {
        $items = OrderItem::query()
            ->with(['order.table', 'menuItem', 'statusHistory' => fn ($query) => $query->orderBy('created_at')])
            ->whereHas('menuItem', fn ($query) => $query->where('production_area', MenuItem::AREA_KITCHEN))
            ->whereIn('status', [
                OrderItem::STATUS_NEW,
                OrderItem::STATUS_PREPARING,
                OrderItem::STATUS_READY,
            ])
            ->orderByRaw("case status when 'new' then 1 when 'preparing' then 2 when 'ready' then 3 else 4 end")
            ->orderBy('created_at')
            ->get()
            ->groupBy('status');

        return view('livewire.production.dashboard', [
            'columns' => [
                OrderItem::STATUS_NEW => [
                    'title' => 'Nowe',
                    'description' => 'Pozycje oczekujące na rozpoczęcie przygotowania.',
                    'items' => $items->get(OrderItem::STATUS_NEW, collect()),
                ],
                OrderItem::STATUS_PREPARING => [
                    'title' => 'W przygotowaniu',
                    'description' => 'Pozycje aktualnie obsługiwane przez kuchnię.',
                    'items' => $items->get(OrderItem::STATUS_PREPARING, collect()),
                ],
                OrderItem::STATUS_READY => [
                    'title' => 'Gotowe',
                    'description' => 'Pozycje gotowe do odbioru przez kelnera.',
                    'items' => $items->get(OrderItem::STATUS_READY, collect()),
                ],
            ],
            'panelLabel' => 'Panel kuchni',
            'title' => 'Pozycje do przygotowania',
            'description' => 'Podgląd pozycji przypisanych do kuchni oraz zmiana statusów przygotowania.',
            'queueDescription' => 'Zamówienia zawierające pozycje kuchenne do obsłużenia.',
            'statusRouteName' => 'kitchen.order-items.status',
            'cancelRouteName' => 'kitchen.order-items.cancel',
            'selectCurrentRouteName' => 'kitchen.order-items.select-current',
            'containerClass' => 'w-full px-1 py-4 sm:px-2 lg:px-3',
        ]);
    }
}
