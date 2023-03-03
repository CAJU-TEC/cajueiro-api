<?php

use App\Http\Controllers\Corporates\CorporateDestroyController;
use App\Http\Controllers\Corporates\CorporateIndexController;
use App\Http\Controllers\Corporates\CorporateShowController;
use App\Http\Controllers\Corporates\CorporateStoreController;
use App\Http\Controllers\Corporates\CorporateUpdateController;
use Illuminate\Support\Facades\Route;

// corporates
Route::get('corporates', CorporateIndexController::class)->name('index')->middleware(['role_or_permission:super-admin|corporates.index']);
Route::get('corporates/{corporate}', CorporateShowController::class)->name('show')->middleware(['role_or_permission:super-admin|corporates.show']);
Route::post('corporates', CorporateStoreController::class)->name('store')->middleware(['role_or_permission:super-admin|corporates.store']);
Route::put('corporates/{corporate}', CorporateUpdateController::class)->name('update')->middleware(['role_or_permission:super-admin|corporates.update']);
Route::delete('corporates/{corporate}', CorporateDestroyController::class)->name('destroy')->middleware(['role_or_permission:super-admin|corporates.destroy']);
