<?php

namespace App\Http\Controllers;

use App\Models\RestaurantTable;

class WaiterTableController extends Controller
{
    public function index()
    {
        return view('waiter.tables.index', [
            'tables' => RestaurantTable::query()
                ->with(['activeOrders' => fn ($query) => $query->latest('opened_at')])
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
