<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\RestaurantTable;
use App\Models\TableReport;
use Illuminate\Http\RedirectResponse;

class ManagerDashboardController extends Controller
{
    public function index()
    {
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();
        $attentionThreshold = now()->subMinutes(30);

        $tableStatusCounts = RestaurantTable::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('manager.dashboard', [
            'todaySales' => Payment::query()
                ->where('status', Payment::STATUS_PAID)
                ->whereBetween('paid_at', [$todayStart, $todayEnd])
                ->sum('amount'),
            'todayOrdersCount' => Order::query()
                ->whereBetween('opened_at', [$todayStart, $todayEnd])
                ->count(),
            'activeOrdersCount' => Order::query()
                ->whereIn('status', Order::activeStatuses())
                ->count(),
            'paidOrdersCount' => Order::query()
                ->where('status', Order::STATUS_PAID)
                ->whereBetween('paid_at', [$todayStart, $todayEnd])
                ->count(),
            'freeTablesCount' => (int) ($tableStatusCounts[RestaurantTable::STATUS_FREE] ?? 0),
            'occupiedTablesCount' => (int) ($tableStatusCounts[RestaurantTable::STATUS_OCCUPIED] ?? 0),
            'reservedTablesCount' => (int) ($tableStatusCounts[RestaurantTable::STATUS_RESERVED] ?? 0),
            'inactiveTablesCount' => (int) ($tableStatusCounts[RestaurantTable::STATUS_INACTIVE] ?? 0),
            'recentOrders' => Order::query()
                ->with(['table', 'waiter', 'items', 'payments'])
                ->latest('opened_at')
                ->limit(6)
                ->get(),
            'topItems' => OrderItem::query()
                ->select('menu_item_id')
                ->selectRaw('SUM(quantity) as quantity_sold')
                ->where('status', '!=', OrderItem::STATUS_CANCELLED)
                ->whereHas('order', fn ($query) => $query->whereBetween('opened_at', [$todayStart, $todayEnd]))
                ->with('menuItem')
                ->groupBy('menu_item_id')
                ->orderByDesc('quantity_sold')
                ->limit(5)
                ->get(),
            'attentionOrders' => Order::query()
                ->with(['table', 'waiter', 'items', 'payments'])
                ->where(function ($query) use ($attentionThreshold) {
                    $query
                        ->where(function ($query) use ($attentionThreshold) {
                            $query
                                ->whereIn('status', [
                                    Order::STATUS_OPEN,
                                    Order::STATUS_IN_PROGRESS,
                                    Order::STATUS_READY,
                                ])
                                ->where('opened_at', '<=', $attentionThreshold);
                        })
                        ->orWhere(function ($query) {
                            $query
                                ->where('status', Order::STATUS_SERVED)
                                ->whereDoesntHave('payments', fn ($query) => $query->where('status', Payment::STATUS_PAID));
                        });
                })
                ->oldest('opened_at')
                ->limit(5)
                ->get(),
            'reports' => TableReport::with(['table', 'waiter'])
                ->where('status', 'open')
                ->latest()
                ->get(),
        ]);
    }

    public function resolveReport(TableReport $tableReport): RedirectResponse
    {
        if (! auth()->user()->isManager() && ! auth()->user()->isAdmin()) {
            abort(403, 'Brak uprawnień do wykonania tej akcji.');
        }

        $tableReport->update([
            'status' => 'resolved',
            'resolved_by' => auth()->id(),
            'resolved_at' => now(),
        ]);

        return back()->with('success', 'Zgłoszenie zostało oznaczone jako rozwiązane.');
    }
}
