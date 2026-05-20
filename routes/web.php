<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Widok strony głównej (tymczasowy)
Route::get('/', function () {
    return view('login');
});

// Ścieżki dla gości (niezalogowanych)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

// Ścieżki dla zalogowanych użytkowników
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    // Przykładowy panel po zalogowaniu
    Route::get('/dashboard', function () {
        return 'Witaj w panelu restauracji! <form method="POST" action="'.route('logout').'">'.csrf_field().'<button type="submit">Wyloguj</button></form>';
    })->name('dashboard');
});
