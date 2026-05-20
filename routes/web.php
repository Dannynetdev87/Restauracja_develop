<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;

// 1. Strona główna
// Wymaga pliku dokładnie tutaj: resources/views/index.blade.php
Route::get('/', function () {
    return view('index');
})->name('home');

// 2. Menu dla gości
// Wymaga pliku dokładnie tutaj: resources/views/menu/menu.blade.php
Route::get('/menu', function () {
    return view('menu.menu');
})->name('menu.index');

// 3. Panel zarządzania menu (Manager)
// Wymaga pliku dokładnie tutaj: resources/views/menu/menu-manager.blade.php
Route::get('/manager/menu', function () {
    return view('menu.menu-manager');
})->name('manager.podglad');

// Ścieżki dla gości (niezalogowanych)
// Wymaga pliku dokładnie tutaj: resources/views/auth/login.blade.php
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

// Ścieżki dla zalogowanych użytkowników
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    // Twój przycisk "Dashboard" w Navbarze prowadzi tutaj.
    // Przekierowujemy go po prostu na stronę główną, bo tam masz już zrobioną logikę ze stolikami.
    Route::get('/dashboard', function () {
        return redirect()->route('home');
    })->name('dashboard');
});
