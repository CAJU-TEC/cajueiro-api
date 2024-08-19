<?php

use App\Http\Controllers\CheckLists\CheckListsIndexController;
use App\Http\Controllers\CheckLists\CheckListsShowController;
use App\Http\Controllers\CheckLists\CheckListsStoreController;
use App\Http\Controllers\CheckLists\CheckListsUpdateController;
use App\Http\Controllers\CheckLists\CheckListsDestroyController;
use Illuminate\Support\Facades\Route;

// job plans
Route::get('checkLists', CheckListsIndexController::class)->name('index')->middleware(['role_or_permission:super-admin|tickets.index']);
Route::get('checkLists/{checkList}', CheckListsShowController::class)->name('show')->middleware(['role_or_permission:super-admin|jobplans.show']);
Route::post('checkLists', CheckListsStoreController::class)->name('store')->middleware(['role_or_permission:super-admin|jobplans.store']);
Route::put('checkLists/{checkList}', CheckListsUpdateController::class)->name('update')->middleware(['role_or_permission:super-admin|jobplans.update']);
Route::delete('checkLists/{checkList}', CheckListsDestroyController::class)->name('destroy')->middleware(['role_or_permission:super-admin|jobplans.destroy']);
