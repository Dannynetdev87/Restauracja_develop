<?php

namespace App\Http\Controllers;

use App\Models\RestaurantTable;

class WaiterTableController extends Controller
{
    public function index()
    {
        $waiterId = request()->user()->id;

        return view('waiter.tables.index', [
            'tables' => RestaurantTable::query()
                ->visibleForWaiter($waiterId)
                ->with(['activeOrders' => fn ($query) => $query
                    ->where('waiter_id', $waiterId)
                    ->latest('opened_at')])
                ->orderBy('number')
                ->get(),
            'statuses' => $this->statuses(),
        ]);
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
