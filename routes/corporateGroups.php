<?php

use App\Http\Controllers\CorporateGroups\CorporateGroupDestroyController;
use App\Http\Controllers\CorporateGroups\CorporateGroupIndexController;
use App\Http\Controllers\CorporateGroups\CorporateGroupStoreController;
use App\Http\Controllers\CorporateGroups\CorporateGroupUpdateController;
use Illuminate\Support\Facades\Route;

// grupos de organizações: usam as mesmas permissões de corporates
Route::get('corporate-groups', CorporateGroupIndexController::class)->name('index')->middleware(['role_or_permission:super-admin|corporates.index']);
Route::post('corporate-groups', CorporateGroupStoreController::class)->name('store')->middleware(['role_or_permission:super-admin|corporates.store']);
Route::put('corporate-groups/{corporateGroup}', CorporateGroupUpdateController::class)->name('update')->middleware(['role_or_permission:super-admin|corporates.update']);
Route::delete('corporate-groups/{corporateGroup}', CorporateGroupDestroyController::class)->name('destroy')->middleware(['role_or_permission:super-admin|corporates.destroy']);
