<?php

use App\Http\Controllers\Permissions\PermissionsListController;
use Illuminate\Support\Facades\Route;

// users
// Route::delete('roles/{role}', GroupsDestroyController::class)->name('destroy');
// Route::get('rolesIndex', GroupsIndexController::class)->name('index');
Route::get('permissions', PermissionsListController::class)->name('list');
// Route::post('roles', GroupsStoreController::class)->name('store');
// Route::get('roles/{role}', GroupsShowController::class)->name('show');
// Route::put('roles/{role}', GroupsUpdateController::class)->name('update');

// fazer o logout...
