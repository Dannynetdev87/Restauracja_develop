<?php

namespace App\Http\Controllers;

use App\Models\RestaurantTable;

class GuestTableController extends Controller
{
    public function index()
    {
        $tables = RestaurantTable::query()
            ->with(['activeOrders' => fn ($query) => $query
                ->select('id', 'restaurant_table_id', 'status', 'opened_at')
                ->oldest('opened_at')])
            ->orderBy('number')
            ->get()
            ->map(fn (RestaurantTable $table) => $this->withGuestDisplayData($table));

        return view('index', [
            'tables' => $tables,
        ]);
    }

    private function withGuestDisplayData(RestaurantTable $table): RestaurantTable
    {
        $activeOrder = $table->activeOrders->first();

        $table->setAttribute('waiting_minutes', $activeOrder
            ? (int) $activeOrder->opened_at->diffInMinutes(now())
            : null);

        $table->setAttribute('status_label', match ($table->status) {
            RestaurantTable::STATUS_FREE => 'Wolny',
            RestaurantTable::STATUS_OCCUPIED => 'Zajęty',
            RestaurantTable::STATUS_RESERVED => 'Zarezerwowany',
            RestaurantTable::STATUS_INACTIVE => 'Nieaktywny',
            default => ucfirst($table->status),
        });

        $table->setAttribute('status_class', match ($table->status) {
            RestaurantTable::STATUS_FREE => 'status-free',
            RestaurantTable::STATUS_OCCUPIED => 'status-occupied',
            RestaurantTable::STATUS_RESERVED => 'status-reserved',
            RestaurantTable::STATUS_INACTIVE => 'status-inactive',
            default => 'status-inactive',
        });

        $table->unsetRelation('activeOrders');

        return $table;
    }
}
