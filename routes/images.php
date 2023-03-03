<?php

use App\Http\Controllers\Images\ImageDestroyController;
use Illuminate\Support\Facades\Route;

// clients
// Route::get('clients', ClientIndexController::class)->name('index');
// Route::get('clients/{client}', ClientShowController::class)->name('show');
// Route::post('clients', ClientStoreController::class)->name('store');
// Route::put('clients/{client}', ClientUpdateController::class)->name('update');
Route::delete('images/{document}', ImageDestroyController::class)->name('destroy')->middleware(['role_or_permission:super-admin|images.destroy']);
