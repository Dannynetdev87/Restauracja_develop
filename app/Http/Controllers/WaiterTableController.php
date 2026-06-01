<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RestaurantTable;
use App\Models\Schedule;
use App\Models\TableReport;
use App\Models\User;
use Illuminate\Http\Request;

class WaiterTableController extends Controller
{
    public function dashboard()
    {
        $waiter = request()->user();
        $waiterId = $waiter->id;

        $activeOrders = Order::query()
            ->where('waiter_id', $waiterId)
            ->whereIn('status', Order::activeStatuses())
            ->whereHas('table', fn ($query) => $query->visibleForWaiter($waiterId))
            ->with(['table', 'items.menuItem', 'items.statusHistory'])
            ->orderBy('opened_at')
            ->get();

        $activeItems = $activeOrders
            ->flatMap(fn (Order $order) => $order->items->map(function (OrderItem $item) use ($order) {
                $item->setRelation('order', $order);

                return $item;
            }))
            ->values();

        return view('waiter.dashboard', [
            'activeOrders' => $activeOrders,
            'inProgressItems' => $activeItems
                ->whereIn('status', [OrderItem::STATUS_NEW, OrderItem::STATUS_PREPARING])
                ->values(),
            'readyItems' => $activeItems
                ->where('status', OrderItem::STATUS_READY)
                ->values(),
            'cancelledItems' => $activeItems
                ->where('status', OrderItem::STATUS_CANCELLED)
                ->values(),
            'tables' => $this->visibleTables($waiterId),
            'todaySchedule' => Schedule::query()
                ->where('user_id', $waiterId)
                ->whereDate('date', today())
                ->orderBy('start_time')
                ->first(),
            'shiftManager' => User::query()
                ->where('role', User::ROLE_MANAGER)
                ->where('is_active', true)
                ->orderBy('name')
                ->first(),
        ]);
    }

    public function index()
    {
        $waiterId = request()->user()->id;

        return view('waiter.tables.index', [
            'tables' => $this->visibleTables($waiterId),
            'statuses' => $this->statuses(),
        ]);
    }

    public function storeReport(Request $request, RestaurantTable $restaurantTable)
    {
        if (! $restaurantTable->isVisibleForWaiter(auth()->id())) {
            abort(403);
        }

        $validated = $request->validate([
            'type' => 'required|in:brudny stolik,brak sztućców,potrzebna pomoc,długi czas oczekiwania,problem z zamówieniem,inne',
            'message' => 'nullable|string|max:255',
        ]);

        TableReport::create([
            'restaurant_table_id' => $restaurantTable->id,
            'reported_by' => auth()->id(),
            ...$validated,
        ]);

        return back()->with('success', 'Zgłoszenie zostało wysłane do managera.');
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
