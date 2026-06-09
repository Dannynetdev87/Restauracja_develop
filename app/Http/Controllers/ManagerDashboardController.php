<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\MenuItem;
use App\Models\Payment;
use App\Models\RestaurantTable;
use App\Models\Schedule;
use App\Models\TableReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

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

    public function statistics()
    {
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        $paidPayments = Payment::query()
            ->where('status', Payment::STATUS_PAID);

        $todayPaidPayments = (clone $paidPayments)
            ->where(function ($query) use ($todayStart, $todayEnd) {
                $query
                    ->whereBetween('paid_at', [$todayStart, $todayEnd])
                    ->orWhere(function ($query) use ($todayStart, $todayEnd) {
                        $query
                            ->whereNull('paid_at')
                            ->whereBetween('created_at', [$todayStart, $todayEnd]);
                    });
            });

        $topMenuItem = fn (string $productionArea) => OrderItem::query()
            ->join('menu_items', 'order_items.menu_item_id', '=', 'menu_items.id')
            ->select('menu_items.name')
            ->selectRaw('SUM(order_items.quantity) as quantity_sold')
            ->where('menu_items.production_area', $productionArea)
            ->where('order_items.status', '!=', OrderItem::STATUS_CANCELLED)
            ->whereHas('order', function ($query) use ($todayStart, $todayEnd) {
                $query
                    ->whereBetween('opened_at', [$todayStart, $todayEnd])
                    ->orWhere(function ($query) use ($todayStart, $todayEnd) {
                        $query
                            ->whereNull('opened_at')
                            ->whereBetween('created_at', [$todayStart, $todayEnd]);
                    });
            })
            ->groupBy('menu_items.id', 'menu_items.name')
            ->orderByDesc('quantity_sold')
            ->first();

        $topEmployee = Payment::query()
            ->join('orders', 'payments.order_id', '=', 'orders.id')
            ->join('users', 'orders.waiter_id', '=', 'users.id')
            ->select('users.name')
            ->selectRaw('SUM(payments.amount) as total_sales')
            ->where('payments.status', Payment::STATUS_PAID)
            ->where(function ($query) use ($monthStart, $monthEnd) {
                $query
                    ->whereBetween('payments.paid_at', [$monthStart, $monthEnd])
                    ->orWhere(function ($query) use ($monthStart, $monthEnd) {
                        $query
                            ->whereNull('payments.paid_at')
                            ->whereBetween('payments.created_at', [$monthStart, $monthEnd]);
                    });
            })
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_sales')
            ->first();

        $employeeHours = Schedule::query()
            ->with('user')
            ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get()
            ->groupBy('user_id')
            ->map(function ($schedules) {
                $user = $schedules->first()->user;
                $minutes = $schedules->sum(fn (Schedule $schedule) => max(
                    0,
                    $schedule->startsAtDateTime()->diffInMinutes($schedule->endsAtDateTime(), false)
                ));

                return [
                    'name' => $user?->full_name ?? 'Brak danych',
                    'role' => $user?->role ?? 'brak roli',
                    'hours' => round($minutes / 60, 1),
                ];
            })
            ->sortByDesc('hours')
            ->values()
            ->take(5);

        return view('manager.statistics', [
            'todaySales' => (float) $todayPaidPayments->sum('amount'),
            'todayOrdersCount' => Order::query()
                ->where(function ($query) use ($todayStart, $todayEnd) {
                    $query
                        ->whereBetween('opened_at', [$todayStart, $todayEnd])
                        ->orWhere(function ($query) use ($todayStart, $todayEnd) {
                            $query
                                ->whereNull('opened_at')
                                ->whereBetween('created_at', [$todayStart, $todayEnd]);
                        });
                })
                ->count(),
            'activeOrdersCount' => Order::query()
                ->whereIn('status', Order::activeStatuses())
                ->count(),
            'paidOrdersCount' => Order::query()
                ->where('status', Order::STATUS_PAID)
                ->whereBetween('paid_at', [$todayStart, $todayEnd])
                ->count(),
            'totalSales' => (float) (clone $paidPayments)->sum('amount'),
            'totalOrdersCount' => Order::query()->count(),
            'totalTips' => (float) (clone $paidPayments)->sum('tip_amount'),
            'guestCount' => null,
            'topBarItem' => $topMenuItem(MenuItem::AREA_BAR),
            'topKitchenItem' => $topMenuItem(MenuItem::AREA_KITCHEN),
            'topEmployee' => $topEmployee,
            'employeeHours' => $employeeHours,
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
