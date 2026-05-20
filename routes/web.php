<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('menu');
});

Route::get('/manager', function ()
{
    return view('menu-manager');
})->name('manager.podglad');
