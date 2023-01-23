<?php

use App\Http\Controllers\Comments\CommentsIndexController;
use App\Http\Controllers\Comments\CommentsStoreController;
use Illuminate\Support\Facades\Route;

// corporates
Route::get('comments', CommentsIndexController::class)->name('index');
// Route::get('corporates/{corporate}', CorporateShowController::class)->name('show');
Route::post('comments', CommentsStoreController::class)->name('store');
// Route::put('corporates/{corporate}', CorporateUpdateController::class)->name('update');
// Route::delete('corporates/{corporate}', CorporateDestroyController::class)->name('destroy');
