<?php

use App\Http\Controllers\Auth\LoginController;
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

    Route::middleware('role:manager')->group(function () {
        Route::get('/manager/dashboard', function () {
            return view('index');
        })->name('manager.dashboard');

        Route::get('/manager/menu', function () {
            return view('menu-manager');
        })->name('manager.podglad');
    });
});

Route::get('/menu', function () {
    return view('menu');
})->name('menu.index');
