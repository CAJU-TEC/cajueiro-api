<?php

use App\Http\Controllers\Schedules\SchedulesDestroyController;
use App\Http\Controllers\Schedules\SchedulesIndexController;
use App\Http\Controllers\Schedules\SchedulesShowController;
use App\Http\Controllers\Schedules\SchedulesStoreController;
use App\Http\Controllers\Schedules\SchedulesTodayController;
use App\Http\Controllers\Schedules\SchedulesUpdateController;
use Illuminate\Support\Facades\Route;

// schedules
Route::get('schedules', SchedulesIndexController::class)->name('index')->middleware(['role_or_permission:super-admin|schedules.list']);
Route::get('schedules/today', SchedulesTodayController::class)->name('today')->middleware(['role_or_permission:super-admin|schedules.index']);
Route::get('schedules/{schedule}', SchedulesShowController::class)->name('show')->middleware(['role_or_permission:super-admin|schedules.show']);
Route::post('schedules', SchedulesStoreController::class)->name('store')->middleware(['role_or_permission:super-admin|schedules.store']);
Route::put('schedules/{schedule}', SchedulesUpdateController::class)->name('update')->middleware(['role_or_permission:super-admin|schedules.update']);
Route::delete('schedules/{schedule}', SchedulesDestroyController::class)->name('destroy')->middleware(['role_or_permission:super-admin|schedules.destroy']);
