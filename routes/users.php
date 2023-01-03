<?php

use App\Http\Controllers\Auth\AuthIndexController;
use App\Http\Controllers\Auth\AuthLoginController;
use App\Http\Controllers\Auth\AuthStoreController;
use Illuminate\Support\Facades\Route;

// users
// Route::delete('tickets/{client}', TicketsDestroyController::class)->name('destroy');
Route::get('users', AuthIndexController::class)->name('index');
// Route::get('tickets/{client}', TicketsShowController::class)->name('show');
Route::post('users', AuthStoreController::class)->name('store');
// Route::put('tickets/{client}', TicketsUpdateController::class)->name('update');
Route::post('users/login', AuthLoginController::class)->name('login');

// fazer o logout...
