<?php

use App\Http\Controllers\Groups\GroupsIndexController;
use App\Http\Controllers\Groups\GroupsListController;
use App\Http\Controllers\Groups\GroupsDestroyController;
use App\Http\Controllers\Groups\GroupsShowController;
use App\Http\Controllers\Groups\GroupsStoreController;
use App\Http\Controllers\Groups\GroupsUpdateController;
use Illuminate\Support\Facades\Route;

// users
Route::delete('roles/{role}', GroupsDestroyController::class)->name('destroy');
Route::get('rolesIndex', GroupsIndexController::class)->name('index');
Route::get('roles', GroupsListController::class)->name('list');
Route::post('roles', GroupsStoreController::class)->name('store');
Route::get('roles/{role}', GroupsShowController::class)->name('show');
Route::put('roles/{role}', GroupsUpdateController::class)->name('update');

// fazer o logout...
