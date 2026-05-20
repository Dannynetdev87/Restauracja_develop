<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;

// Trasa do indexu
Route::get('/', function () {
    return view('index');
})->name('home');

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

// Trasa do Menu (na ten moment zwraca po prostu Twój widok menu.blade.php)
Route::get('/menu', function () {
    return view('menu');
})->name('menu.index');

// Tymczasowa trasa dla managera, żeby link w menu.blade.php nie wywalał błędu 500
Route::get('/manager/menu', function () {
    return view('menu-manager');
})->name('manager.podglad');

// Tutaj w przyszłości dopiszemy kolejne adresy, np.:
// Route::get('/menu', [MenuController::class, 'index']);
// Route::get('/rezerwacje', [ReservationController::class, 'create']);
