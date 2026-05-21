<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\MenuCategoryController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\MenuManagementController;
use App\Http\Controllers\RestaurantTableController;
use App\Http\Controllers\WaiterOrderController;
use App\Http\Controllers\WaiterTableController;
use App\Models\MenuCategory;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Publiczne Trasy (Dostępne dla każdego)
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('index');
})->name('home');

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

/*
|--------------------------------------------------------------------------
| Trasy dla Gości (Niezalogowanych)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

/*
|--------------------------------------------------------------------------
| Trasy Wymagające Zalogowania (Auth)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Wylogowanie
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    // Centralny punkt przekierowań po logowaniu (obsługiwany przez middleware role.redirect)
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->middleware('role.redirect')->name('dashboard');

    /*
     |--- PANEL KELNERA ---
     */
    Route::get('/waiter/dashboard', function () {
        return view('dashboard', [
            'title' => 'Panel kelnera',
            'description' => 'Obsługa stolików, przyjmowanie zamówień i zamykanie rachunków.',
        ]);
    })->middleware('role:kelner')->name('waiter.dashboard');

    Route::middleware('role:kelner')->group(function () {
        // Stoliki kelnera
        Route::get('/waiter/tables', [WaiterTableController::class, 'index'])->name('waiter.tables.index');

        // Formularz i składanie zamówień
        Route::get('/waiter/orders/create', [WaiterOrderController::class, 'create'])->name('waiter.orders.create');
        Route::post('/waiter/tables/{restaurantTable}/orders', [WaiterOrderController::class, 'store'])->name('waiter.orders.store');
        Route::get('/waiter/orders/{order}', [WaiterOrderController::class, 'show'])->name('waiter.orders.show');
    });

    /*
     |--- PANEL KUCHNI ---
     */
    Route::get('/kitchen/dashboard', function () {
        return view('dashboard', [
            'title' => 'Panel kuchni',
            'description' => 'Podgląd pozycji do przygotowania oraz zmiana statusów zamówień.',
        ]);
    })->middleware('role:kuchnia')->name('kitchen.dashboard');

    /*
     |--- PANEL BARU ---
     */
    Route::get('/bar/dashboard', function () {
        return view('dashboard', [
            'title' => 'Panel baru',
            'description' => 'Podgląd napojów do przygotowania i obsługa statusów baru.',
        ]);
    })->middleware('role:bar')->name('bar.dashboard');

    /*
     |--- PANEL ADMINISTRATORA ---
     */
    Route::get('/admin/dashboard', function () {
        return view('dashboard', [
            'title' => 'Panel administratora',
            'description' => 'Zarządzanie użytkownikami, konfiguracją systemu oraz podstawowymi danymi restauracji.',
        ]);
    })->middleware('role:admin')->name('admin.dashboard');

    /*
     |--- PANEL MANAGERA & ADMINA ---
     */
    Route::middleware('role:manager,admin')->group(function () {
        Route::get('/manager/dashboard', function () {
            return view('index');
        })->name('manager.dashboard');

        // Podgląd i główne zarządzanie menu
        Route::get('/manager/menu', [MenuManagementController::class, 'index'])->name('manager.podglad');

        // Zarządzanie stolikami przez Managera
        Route::get('/manager/tables', [RestaurantTableController::class, 'index'])->name('manager.tables.index');
        Route::post('/manager/tables', [RestaurantTableController::class, 'store'])->name('manager.tables.store');
        Route::get('/manager/tables/{restaurantTable}/edit', [RestaurantTableController::class, 'edit'])->name('manager.tables.edit');
        Route::put('/manager/tables/{restaurantTable}', [RestaurantTableController::class, 'update'])->name('manager.tables.update');
        Route::delete('/manager/tables/{restaurantTable}', [RestaurantTableController::class, 'destroy'])->name('manager.tables.destroy');

        // Zarządzanie kategoriami menu
        Route::get('/manager/menu/categories/create', [MenuCategoryController::class, 'create'])->name('manager.menu-categories.create');
        Route::post('/manager/menu/categories', [MenuCategoryController::class, 'store'])->name('manager.menu-categories.store');
        Route::get('/manager/menu/categories/{menuCategory}/edit', [MenuCategoryController::class, 'edit'])->name('manager.menu-categories.edit');
        Route::put('/manager/menu/categories/{menuCategory}', [MenuCategoryController::class, 'update'])->name('manager.menu-categories.update');
        Route::delete('/manager/menu/categories/{menuCategory}', [MenuCategoryController::class, 'destroy'])->name('manager.menu-categories.destroy');

        // Zarządzanie pozycjami menu
        Route::get('/manager/menu/items/create', [MenuItemController::class, 'create'])->name('manager.menu-items.create');
        Route::post('/manager/menu/items', [MenuItemController::class, 'store'])->name('manager.menu-items.store');
        Route::get('/manager/menu/items/{menuItem}/edit', [MenuItemController::class, 'edit'])->name('manager.menu-items.edit');
        Route::put('/manager/menu/items/{menuItem}', [MenuItemController::class, 'update'])->name('manager.menu-items.update');
        Route::patch('/manager/menu/items/{menuItem}/availability', [MenuItemController::class, 'toggleAvailability'])->name('manager.menu-items.availability');
        Route::delete('/manager/menu/items/{menuItem}', [MenuItemController::class, 'destroy'])->name('manager.menu-items.destroy');
    });
});
