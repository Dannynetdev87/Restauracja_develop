<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\RestaurantTable;
use Illuminate\Support\Facades\DB;

class WaiterOrderController extends Controller
{
    public function store(RestaurantTable $restaurantTable)
    {
        if (! $restaurantTable->canOpenOrder()) {
            return redirect()
                ->route('waiter.tables.index')
                ->with('error', 'Zamówienie można rozpocząć tylko dla wolnego stolika bez aktywnego zamówienia.');
        }

        $order = DB::transaction(function () use ($restaurantTable) {
            $order = Order::create([
                'restaurant_table_id' => $restaurantTable->id,
                'waiter_id' => request()->user()->id,
                'status' => Order::STATUS_OPEN,
                'opened_at' => now(),
            ]);

            $restaurantTable->update([
                'status' => RestaurantTable::STATUS_OCCUPIED,
            ]);

            return $order;
        });

        return redirect()
            ->route('waiter.orders.show', $order)
            ->with('success', 'Zamówienie zostało rozpoczęte.');
    }

    public function show(Order $order)
    {
        if ($order->waiter_id !== request()->user()->id) {
            abort(403);
        }

        return view('waiter.orders.show', [
            'order' => $order->load(['table', 'items.menuItem']),
        ]);
    }
}
