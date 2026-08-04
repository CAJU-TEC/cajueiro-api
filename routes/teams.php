<?php

use App\Http\Controllers\Teams\TeamsDestroyController;
use App\Http\Controllers\Teams\TeamsIndexController;
use App\Http\Controllers\Teams\TeamsShowController;
use App\Http\Controllers\Teams\TeamsStoreController;
use App\Http\Controllers\Teams\TeamsUpdateController;
use Illuminate\Support\Facades\Route;

// teams
Route::get('teams', TeamsIndexController::class)->name('index')->middleware(['role_or_permission:super-admin|teams.index']);
Route::get('teams/{team}', TeamsShowController::class)->name('show')->middleware(['role_or_permission:super-admin|teams.show']);
Route::post('teams', TeamsStoreController::class)->name('store')->middleware(['role_or_permission:super-admin|teams.store']);
Route::put('teams/{team}', TeamsUpdateController::class)->name('update')->middleware(['role_or_permission:super-admin|teams.update']);
Route::delete('teams/{team}', TeamsDestroyController::class)->name('destroy')->middleware(['role_or_permission:super-admin|teams.destroy']);
