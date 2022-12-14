<?php

use App\Http\Controllers\Impacts\ImpactDestroyController;
use App\Http\Controllers\Impacts\ImpactIndexController;
use App\Http\Controllers\Impacts\ImpactShowController;
use App\Http\Controllers\Impacts\ImpactStoreController;
use App\Http\Controllers\Impacts\ImpactUpdateController;
use Illuminate\Support\Facades\Route;

// impacts
Route::get('impacts', ImpactIndexController::class)->name('index');
Route::get('impacts/{impact}', ImpactShowController::class)->name('show');
Route::post('impacts', ImpactStoreController::class)->name('store');
Route::put('impacts/{impact}', ImpactUpdateController::class)->name('update');
Route::delete('impacts/{impact}', ImpactDestroyController::class)->name('destroy');
