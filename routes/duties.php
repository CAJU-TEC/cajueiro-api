<?php

use App\Http\Controllers\Duties\DutiesIndexController;
use Illuminate\Support\Facades\Route;

// corporates
Route::get('duties', DutiesIndexController::class)->name('index');
// Route::get('duties/{corporate}', dutieshowController::class)->name('show')->middleware(['role_or_permission:super-admin|duties.show']);
// Route::post('duties', dutiestoreController::class)->name('store')->middleware(['role_or_permission:super-admin|duties.store']);
// Route::put('duties/{corporate}', CorporateUpdateController::class)->name('update')->middleware(['role_or_permission:super-admin|duties.update']);
// Route::delete('duties/{corporate}', CorporateDestroyController::class)->name('destroy')->middleware(['role_or_permission:super-admin|duties.destroy']);
