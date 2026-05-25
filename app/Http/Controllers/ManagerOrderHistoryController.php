<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\RestaurantTable;
use App\Models\User;
use Illuminate\Http\Request;

class ManagerOrderHistoryController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'waiter_id' => ['nullable', 'integer', 'exists:users,id'],
            'restaurant_table_id' => ['nullable', 'integer', 'exists:restaurant_tables,id'],
        ]);

        $orders = Order::query()
            ->with(['table', 'waiter', 'items.menuItem', 'payments'])
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('opened_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('opened_at', '<=', $date))
            ->when($filters['waiter_id'] ?? null, fn ($query, $waiterId) => $query->where('waiter_id', $waiterId))
            ->when($filters['restaurant_table_id'] ?? null, fn ($query, $tableId) => $query->where('restaurant_table_id', $tableId))
            ->latest('opened_at')
            ->limit(100)
            ->get();

        return view('manager.orders.history', [
            'orders' => $orders,
            'waiters' => User::query()
                ->where('role', User::ROLE_WAITER)
                ->orderBy('name')
                ->get(),
            'tables' => RestaurantTable::query()
                ->orderBy('number')
                ->get(),
            'paidStatus' => Payment::STATUS_PAID,
        ]);
    }
}
