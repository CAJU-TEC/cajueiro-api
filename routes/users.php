<?php

use App\Http\Controllers\Auth\AuthIndexController;
use App\Http\Controllers\Auth\AuthLoginController;
use App\Http\Controllers\Auth\AuthRefreshTokenController;
use App\Http\Controllers\Auth\AuthShowController;
use App\Http\Controllers\Auth\AuthStoreController;
use App\Http\Controllers\Auth\AuthUpdateController;
use Illuminate\Support\Facades\Route;

// users
Route::get('users', AuthIndexController::class)->name('index');
Route::get('users/{user}', AuthShowController::class)->name('show');
Route::put('users/{user}', AuthUpdateController::class)->name('update')->middleware(['role_or_permission:super-admin|users.update']);
Route::post('users', AuthStoreController::class)->name('store')->middleware(['role_or_permission:super-admin|users.show']);
Route::post('users/refresh', AuthRefreshTokenController::class)->name('refresh');
Route::post('users/login', AuthLoginController::class)->name('login');

// fazer o logout...
