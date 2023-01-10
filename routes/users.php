<?php

use App\Http\Controllers\Auth\AuthIndexController;
use App\Http\Controllers\Auth\AuthLoginController;
use App\Http\Controllers\Auth\AuthRefreshTokenController;
use App\Http\Controllers\Auth\AuthStoreController;
use Illuminate\Support\Facades\Route;

// users
Route::get('users', AuthIndexController::class)->name('index');
Route::post('users/refresh', AuthRefreshTokenController::class)->name('refresh');
Route::post('users', AuthStoreController::class)->name('store');
Route::post('users/login', AuthLoginController::class)->name('login');

// fazer o logout...
