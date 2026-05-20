<?php

use Illuminate\Support\Facades\Route;

// Ta trasa zostaje – to ona wyświetla naszą nową stronę główną restauracji!
Route::get('/', function () {
    return view('index');
});

// Tutaj w przyszłości dopiszemy kolejne adresy, np.:
// Route::get('/menu', [MenuController::class, 'index']);
// Route::get('/rezerwacje', [ReservationController::class, 'create']);
