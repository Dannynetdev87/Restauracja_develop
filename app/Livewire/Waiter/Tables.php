<?php

namespace App\Livewire\Waiter;

use App\Models\RestaurantTable;
use Livewire\Component;

class Tables extends Component
{
    public function render()
    {
        $waiterId = request()->user()->id;

        return view('livewire.waiter.tables', [
            'tables' => $this->visibleTables($waiterId),
            'statuses' => $this->statuses(),
        ]);
    }

    private function visibleTables(int $waiterId)
    {
        return RestaurantTable::query()
            ->visibleForWaiter($waiterId)
            ->with([
                'zone',
                'activeOrders' => fn ($query) => $query
                    ->where('waiter_id', $waiterId)
                    ->with(['items.menuItem'])
                    ->latest('opened_at'),
            ])
            ->orderBy('number')
            ->get();
    }

    private function statuses(): array
    {
        return [
            RestaurantTable::STATUS_FREE => 'Wolny',
            RestaurantTable::STATUS_OCCUPIED => 'Zajęty',
            RestaurantTable::STATUS_RESERVED => 'Zarezerwowany',
            RestaurantTable::STATUS_INACTIVE => 'Nieaktywny',
        ];
    }
}
