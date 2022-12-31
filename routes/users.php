<?php

// use App\Http\Controllers\Tickets\TicketsDestroyController;
// use App\Http\Controllers\Tickets\TicketsIndexController;
// use App\Http\Controllers\Tickets\TicketsShowController;

use App\Http\Controllers\Auth\AuthLoginController;
use App\Http\Controllers\Auth\AuthStoreController;
// use App\Http\Controllers\Tickets\TicketsUpdateController;
use Illuminate\Support\Facades\Route;

// tickets
// Route::delete('tickets/{client}', TicketsDestroyController::class)->name('destroy');
// Route::get('tickets', TicketsIndexController::class)->name('index');
// Route::get('tickets/{client}', TicketsShowController::class)->name('show');
Route::post('users', AuthStoreController::class)->name('store')->middleware('auth:sanctum');
// Route::put('tickets/{client}', TicketsUpdateController::class)->name('update');
Route::post('users/login', AuthLoginController::class)->name('login');
