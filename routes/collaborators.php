<?php

use App\Http\Controllers\Collaborators\CollaboratorsShowController;
use App\Http\Controllers\Collaborators\CollaboratorsIndexController;
use App\Http\Controllers\Collaborators\CollaboratorsStoreController;
use App\Http\Controllers\Collaborators\CollaboratorsUpdateController;
use App\Http\Controllers\Collaborators\CollaboratorsDestroyController;
use Illuminate\Support\Facades\Route;

// collaborators
Route::get('collaborators', CollaboratorsIndexController::class)->name('index');
Route::get('collaborators/{collaborator}', CollaboratorsShowController::class)->name('show');
Route::post('collaborators', CollaboratorsStoreController::class)->name('store');
Route::put('collaborators/{collaborator}', CollaboratorsUpdateController::class)->name('update');
Route::delete('collaborators/{collaborator}', CollaboratorsDestroyController::class)->name('destroy');
