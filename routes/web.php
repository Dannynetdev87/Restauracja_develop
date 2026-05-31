<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BarDashboardController;
use App\Http\Controllers\GuestTableController;
use App\Http\Controllers\KitchenDashboardController;
use App\Http\Controllers\ManagerDashboardController;
use App\Http\Controllers\ManagerDiscountCodeController;
use App\Http\Controllers\ManagerOrderHistoryController;
use App\Http\Controllers\MenuCategoryController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\MenuManagementController;
use App\Http\Controllers\RestaurantTableController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\WaiterBillController;
use App\Http\Controllers\WaiterOrderController;
use App\Http\Controllers\WaiterStatsController;
use App\Http\Controllers\WaiterTableController;
use App\Http\Controllers\ZoneController;
use App\Models\MenuCategory;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', [GuestTableController::class, 'index'])->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', function () {
        $user = request()->user();

        return match ($user->role) {
            User::ROLE_ADMIN => redirect()->route('admin.dashboard'),
            User::ROLE_MANAGER => redirect()->route('manager.dashboard'),
            User::ROLE_KITCHEN => redirect()->route('kitchen.current'),
            User::ROLE_BAR => redirect()->route('bar.current'),
            default => redirect()->route('waiter.dashboard'),
        };
    })->name('dashboard');

    Route::get('/waiter/dashboard', [WaiterTableController::class, 'dashboard'])
        ->middleware('role:kelner')
        ->name('waiter.dashboard');

    Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule.index');

    Route::middleware('role:kelner')->group(function () {
        Route::get('/waiter/tables', [WaiterTableController::class, 'index'])->name('waiter.tables.index');
        Route::get('/waiter/orders/create', [WaiterOrderController::class, 'create'])->name('waiter.orders.create');
        Route::post('/waiter/tables/{restaurantTable}/orders', [WaiterOrderController::class, 'store'])->name('waiter.orders.store');
        Route::get('/waiter/orders/{order}', [WaiterOrderController::class, 'show'])->name('waiter.orders.show');
        Route::get('/waiter/orders/{order}/bill', [WaiterBillController::class, 'show'])->name('waiter.orders.bill');
        Route::post('/waiter/orders/{order}/payments', [WaiterBillController::class, 'storePayment'])->name('waiter.orders.payments.store');
        Route::patch('/waiter/order-items/{orderItem}/deliver', [WaiterOrderController::class, 'deliverItem'])->name('waiter.order-items.deliver');
        Route::get('/waiter/stats', [WaiterStatsController::class, 'stats'])->name('waiter.stats');
    });

    Route::middleware('role:kuchnia')->group(function () {
        Route::get('/kitchen/current', [KitchenDashboardController::class, 'current'])->name('kitchen.current');
        Route::get('/kitchen/dashboard', [KitchenDashboardController::class, 'index'])->name('kitchen.dashboard');
        Route::patch('/kitchen/order-items/{orderItem}/status', [KitchenDashboardController::class, 'updateStatus'])->name('kitchen.order-items.status');
        Route::patch('/kitchen/order-items/{orderItem}/cancel', [KitchenDashboardController::class, 'cancel'])->name('kitchen.order-items.cancel');
    });

    Route::middleware('role:bar')->group(function () {
        Route::get('/bar/current', [BarDashboardController::class, 'current'])->name('bar.current');
        Route::get('/bar/dashboard', [BarDashboardController::class, 'index'])->name('bar.dashboard');
        Route::patch('/bar/order-items/{orderItem}/status', [BarDashboardController::class, 'updateStatus'])->name('bar.order-items.status');
        Route::patch('/bar/order-items/{orderItem}/cancel', [BarDashboardController::class, 'cancel'])->name('bar.order-items.cancel');
    });

    Route::get('/admin/dashboard', function () {
        return view('dashboard', [
            'title' => 'Panel administratora',
            'description' => 'Zarządzanie użytkownikami, konfiguracją systemu oraz podstawowymi danymi restauracji.',
        ]);
    })->middleware('role:admin')->name('admin.dashboard');

    Route::middleware('role:manager,admin')->group(function () {
        Route::get('/manager/dashboard', [ManagerDashboardController::class, 'index'])->name('manager.dashboard');
        Route::get('/manager/orders/history', [ManagerOrderHistoryController::class, 'index'])->name('manager.orders.history');
        Route::get('/manager/discount-codes', [ManagerDiscountCodeController::class, 'index'])->name('manager.discount-codes.index');
        Route::post('/manager/discount-codes', [ManagerDiscountCodeController::class, 'store'])->name('manager.discount-codes.store');
        Route::get('/manager/discount-codes/{discountCode}/edit', [ManagerDiscountCodeController::class, 'edit'])->name('manager.discount-codes.edit');
        Route::put('/manager/discount-codes/{discountCode}', [ManagerDiscountCodeController::class, 'update'])->name('manager.discount-codes.update');
        Route::patch('/manager/discount-codes/{discountCode}/toggle', [ManagerDiscountCodeController::class, 'toggle'])->name('manager.discount-codes.toggle');
        Route::post('/manager/schedules', [ScheduleController::class, 'store'])->name('manager.schedules.store');
        Route::get('/manager/schedules/{schedule}/edit', [ScheduleController::class, 'edit'])->name('manager.schedules.edit');
        Route::put('/manager/schedules/{schedule}', [ScheduleController::class, 'update'])->name('manager.schedules.update');
        Route::delete('/manager/schedules/{schedule}', [ScheduleController::class, 'destroy'])->name('manager.schedules.destroy');

        Route::get('/manager/menu', [MenuManagementController::class, 'index'])->name('manager.podglad');

        Route::get('/manager/tables', [RestaurantTableController::class, 'index'])->name('manager.tables.index');
        Route::post('/manager/tables', [RestaurantTableController::class, 'store'])->name('manager.tables.store');
        Route::get('/manager/tables/{restaurantTable}/edit', [RestaurantTableController::class, 'edit'])->name('manager.tables.edit');
        Route::put('/manager/tables/{restaurantTable}', [RestaurantTableController::class, 'update'])->name('manager.tables.update');
        Route::delete('/manager/tables/{restaurantTable}', [RestaurantTableController::class, 'destroy'])->name('manager.tables.destroy');
        Route::post('/manager/zones', [ZoneController::class, 'store'])->name('manager.zones.store');
        Route::put('/manager/zones/{zone}', [ZoneController::class, 'update'])->name('manager.zones.update');
        Route::patch('/manager/zones/{zone}/toggle', [ZoneController::class, 'toggle'])->name('manager.zones.toggle');
        Route::delete('/manager/zones/{zone}', [ZoneController::class, 'destroy'])->name('manager.zones.destroy');

        Route::post('/manager/menu/categories', [MenuCategoryController::class, 'store'])->name('manager.menu-categories.store');
        Route::get('/manager/menu/categories/{menuCategory}/edit', [MenuCategoryController::class, 'edit'])->name('manager.menu-categories.edit');
        Route::put('/manager/menu/categories/{menuCategory}', [MenuCategoryController::class, 'update'])->name('manager.menu-categories.update');
        Route::delete('/manager/menu/categories/{menuCategory}', [MenuCategoryController::class, 'destroy'])->name('manager.menu-categories.destroy');

        Route::post('/manager/menu/items', [MenuItemController::class, 'store'])->name('manager.menu-items.store');
        Route::get('/manager/menu/items/{menuItem}/edit', [MenuItemController::class, 'edit'])->name('manager.menu-items.edit');
        Route::put('/manager/menu/items/{menuItem}', [MenuItemController::class, 'update'])->name('manager.menu-items.update');
        Route::patch('/manager/menu/items/{menuItem}/availability', [MenuItemController::class, 'toggleAvailability'])->name('manager.menu-items.availability');
        Route::delete('/manager/menu/items/{menuItem}', [MenuItemController::class, 'destroy'])->name('manager.menu-items.destroy');
    });
});

Route::get('/menu', function () {
    return view('menu', [
        'categories' => MenuCategory::query()
            ->where('is_active', true)
            ->with(['availableItems' => fn ($query) => $query->orderBy('name')])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(),
    ]);
})->name('menu.index');
