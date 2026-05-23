<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\RestaurantTable;
use Illuminate\Support\Facades\DB;

class WaiterOrderController extends Controller
{
    public function store(RestaurantTable $restaurantTable)
    {
    }

    public function show(Order $order)
    {
        if ($order->waiter_id !== request()->user()->id) {
            abort(403);
        }
        return view('waiter.orders.show', ['order' => $order->load(['table', 'items.menuItem'])]);
    }

    public function receipt(Order $order)
    {
    }

    /**
     * Wyświetla podsumowanie rachunku przed ostatecznym zamknięciem.
     */
    public function showReceipt(Order $order)
    {
        if ($order->waiter_id !== request()->user()->id) {
            abort(403);
        }
        $order->load(['table', 'items.menuItem']);
        return view('waiter.orders.final-receipt', ['order' => $order]);
    }

    /**
     * Zamyka zamówienie i zwalnia stolik.
     */
    public function finish(Order $order)
    {
        if ($order->waiter_id !== request()->user()->id) {
            abort(403, 'Brak uprawnień.');
        }

        DB::transaction(function () use ($order) {
            $order->update(['status' => Order::STATUS_CLOSED, 'closed_at' => now()]);
            $order->table->update(['status' => RestaurantTable::STATUS_FREE]);
        });

        return redirect()->route('waiter.tables.index')
            ->with('success', 'Zamówienie zostało zakończone i stolik zwolniony.');
    }
}
