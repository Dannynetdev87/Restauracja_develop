<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\MenuCategoryController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\MenuManagementController;
use App\Http\Controllers\RestaurantTableController;
use App\Http\Controllers\WaiterOrderController;
use App\Http\Controllers\WaiterTableController;
use App\Models\MenuCategory;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
})->name('home');

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
            User::ROLE_KITCHEN => redirect()->route('kitchen.dashboard'),
            User::ROLE_BAR => redirect()->route('bar.dashboard'),
            default => redirect()->route('waiter.dashboard'),
        };
    })->name('dashboard');

    Route::get('/waiter/dashboard', function () {
        return view('dashboard', [
            'title' => 'Panel kelnera',
            'description' => 'Obsługa stolików, przyjmowanie zamówień i zamykanie rachunków.',
        ]);
    })->middleware('role:kelner')->name('waiter.dashboard');

    Route::middleware('role:kelner')->group(function () {
        Route::get('/waiter/tables', [WaiterTableController::class, 'index'])->name('waiter.tables.index');
        Route::post('/waiter/tables/{restaurantTable}/orders', [WaiterOrderController::class, 'store'])->name('waiter.orders.store');
        Route::get('/waiter/orders/{order}', [WaiterOrderController::class, 'show'])->name('waiter.orders.show');

        Route::get('/waiter/orders/{order}/receipt', [WaiterOrderController::class, 'receipt'])->name('waiter.orders.receipt');
        Route::post('/waiter/orders/{order}/finish', [WaiterOrderController::class, 'finish'])->name('waiter.orders.finish');
    });

    Route::get('/kitchen/dashboard', function () {
        return view('dashboard', [
            'title' => 'Panel kuchni',
            'description' => 'Podgląd pozycji do przygotowania oraz zmiana statusów zamówień.',
        ]);
    })->middleware('role:kuchnia')->name('kitchen.dashboard');

    Route::get('/bar/dashboard', function () {
        return view('dashboard', [
            'title' => 'Panel baru',
            'description' => 'Podgląd napojów do przygotowania i obsługa statusów baru.',
        ]);
    })->middleware('role:bar')->name('bar.dashboard');

    Route::get('/admin/dashboard', function () {
        return view('dashboard', [
            'title' => 'Panel administratora',
            'description' => 'Zarządzanie użytkownikami, konfiguracją systemu oraz podstawowymi danymi restauracji.',
        ]);
    })->middleware('role:admin')->name('admin.dashboard');

    Route::middleware('role:manager,admin')->group(function () {
        Route::get('/manager/dashboard', function () {
            return view('index');
        })->name('manager.dashboard');

        Route::get('/manager/menu', [MenuManagementController::class, 'index'])->name('manager.podglad');

        Route::get('/manager/tables', [RestaurantTableController::class, 'index'])->name('manager.tables.index');
        Route::post('/manager/tables', [RestaurantTableController::class, 'store'])->name('manager.tables.store');
        Route::get('/manager/tables/{restaurantTable}/edit', [RestaurantTableController::class, 'edit'])->name('manager.tables.edit');
        Route::put('/manager/tables/{restaurantTable}', [RestaurantTableController::class, 'update'])->name('manager.tables.update');
        Route::delete('/manager/tables/{restaurantTable}', [RestaurantTableController::class, 'destroy'])->name('manager.tables.destroy');

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

Route::middleware('role:kelner')->group(function () {
    Route::get('/waiter/tables', [WaiterTableController::class, 'index'])->name('waiter.tables.index');
    Route::post('/waiter/tables/{restaurantTable}/orders', [WaiterOrderController::class, 'store'])->name('waiter.orders.store');
    Route::get('/waiter/orders/{order}', [WaiterOrderController::class, 'show'])->name('waiter.orders.show');

    Route::get('/waiter/orders/{order}/receipt', [WaiterOrderController::class, 'receipt'])->name('waiter.orders.receipt');
    Route::get('/waiter/orders/{order}/final-receipt', [WaiterOrderController::class, 'showReceipt'])->name('waiter.orders.final-receipt');
    Route::post('/waiter/orders/{order}/finish', [WaiterOrderController::class, 'finish'])->name('waiter.orders.finish');
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


