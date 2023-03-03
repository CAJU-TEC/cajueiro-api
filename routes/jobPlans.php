<?php

use App\Http\Controllers\JobPlans\JobPlansIndexController;
use App\Http\Controllers\JobPlans\JobPlansShowController;
use App\Http\Controllers\JobPlans\JobPlansStoreController;
use App\Http\Controllers\JobPlans\JobPlansUpdateController;
use App\Http\Controllers\JobPlans\JobPlansDestroyController;
use Illuminate\Support\Facades\Route;

// job plans
Route::get('jobPlans', JobPlansIndexController::class)->name('index')->middleware(['role_or_permission:super-admin|jobplans.index']);
Route::get('jobPlans/{jobPlan}', JobPlansShowController::class)->name('show')->middleware(['role_or_permission:super-admin|jobplans.show']);
Route::post('jobPlans', JobPlansStoreController::class)->name('store')->middleware(['role_or_permission:super-admin|jobplans.store']);
Route::put('jobPlans/{jobPlan}', JobPlansUpdateController::class)->name('update')->middleware(['role_or_permission:super-admin|jobplans.update']);
Route::delete('jobPlans/{jobPlan}', JobPlansDestroyController::class)->name('destroy')->middleware(['role_or_permission:super-admin|jobplans.destroy']);
