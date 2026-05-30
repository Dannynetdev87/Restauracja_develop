<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RestaurantTable;
use App\Models\Schedule;
use App\Models\User;

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
