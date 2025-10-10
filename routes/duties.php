<?php

use App\Http\Controllers\Duties\DutiesIndexController;
use App\Http\Controllers\Duties\DutiesUserController;
use App\Http\Controllers\Duties\DutiesUserPlantationController;
use Illuminate\Support\Facades\Route;

// corporates
Route::get('duties', DutiesIndexController::class)->name('duties.index');
Route::get('duties/{userid}', DutiesUserController::class)->name('duties.userId');
Route::get('duties/plantation/user', DutiesUserPlantationController::class)->name('duties.user.plantation');
// Route::get('duties/{corporate}', dutieshowController::class)->name('show')->middleware(['role_or_permission:super-admin|duties.show']);
// Route::post('duties', dutiestoreController::class)->name('store')->middleware(['role_or_permission:super-admin|duties.store']);
// Route::put('duties/{corporate}', CorporateUpdateController::class)->name('update')->middleware(['role_or_permission:super-admin|duties.update']);
// Route::delete('duties/{corporate}', CorporateDestroyController::class)->name('destroy')->middleware(['role_or_permission:super-admin|duties.destroy']);
