<?php

use App\Http\Controllers\Clients\ClientDestroyController;
use App\Http\Controllers\Clients\ClientIndexController;
use App\Http\Controllers\Clients\ClientShowController;
use App\Http\Controllers\Clients\ClientStoreController;
use App\Http\Controllers\Clients\ClientUpdateController;
use Illuminate\Support\Facades\Route;

// clients
Route::get('clients', ClientIndexController::class)->name('index');
Route::get('clients/{client}', ClientShowController::class)->name('show');
Route::post('clients', ClientStoreController::class)->name('store');
Route::put('clients/{client}', ClientUpdateController::class)->name('update');
Route::delete('clients/{client}', ClientDestroyController::class)->name('destroy');
