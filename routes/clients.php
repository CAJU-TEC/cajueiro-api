<?php

use App\Http\Controllers\Clients\ClientDestroyController;
use App\Http\Controllers\Clients\ClientIndexController;
use App\Http\Controllers\Clients\ClientShowController;
use App\Http\Controllers\Clients\ClientStoreController;
use App\Http\Controllers\Clients\ClientStoreSimpliedController;
use App\Http\Controllers\Clients\ClientUpdateController;
use Illuminate\Support\Facades\Route;

// clients
Route::get('clients', ClientIndexController::class)->name('index')->middleware(['role_or_permission:super-admin|clients.index']);
Route::post('clients/storeSimplified', ClientStoreSimpliedController::class)->name('storeSimplified');
Route::post('clients', ClientStoreController::class)->name('store');
Route::get('clients/{client}', ClientShowController::class)->name('show');
Route::put('clients/{client}', ClientUpdateController::class)->name('update');
Route::delete('clients/{client}', ClientDestroyController::class)->name('destroy');
