<?php

namespace App\Livewire\Production;

use App\Models\MenuItem;
use App\Models\OrderItem;
use Livewire\Component;

class BarDashboard extends Component
{
    public function render()
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

        return view('livewire.production.dashboard', [
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
            'panelLabel' => 'Panel baru',
            'title' => 'Napoje do przygotowania',
            'description' => 'Podgląd pozycji przypisanych do baru oraz zmiana statusów przygotowania.',
            'queueDescription' => 'Zamówienia zawierające napoje do obsłużenia.',
            'statusRouteName' => 'bar.order-items.status',
            'cancelRouteName' => 'bar.order-items.cancel',
            'selectCurrentRouteName' => 'bar.order-items.select-current',
            'containerClass' => 'w-full px-1 py-4 sm:px-2 lg:px-3',
        ]);
    }
}
