<?php

use App\Http\Controllers\Collaborators\CollaboratorsShowController;
use App\Http\Controllers\Collaborators\CollaboratorsIndexController;
use App\Http\Controllers\Collaborators\CollaboratorsStoreController;
use App\Http\Controllers\Collaborators\CollaboratorsUpdateController;
use App\Http\Controllers\Collaborators\CollaboratorsDestroyController;
use App\Http\Controllers\Collaborators\CollaboratorsSyncDutyController;
use Illuminate\Support\Facades\Route;

// collaborators
Route::get('collaborators', CollaboratorsIndexController::class)->name('index')->middleware(['role_or_permission:super-admin|collaborators.index']);
Route::get('collaborators/{collaborator}', CollaboratorsShowController::class)->name('show')->middleware(['role_or_permission:super-admin|collaborators.show']);
Route::post('collaborators/sync-duty', CollaboratorsSyncDutyController::class)->name('sync-duty');
Route::post('collaborators', CollaboratorsStoreController::class)->name('store')->middleware(['role_or_permission:super-admin|collaborators.store']);
Route::put('collaborators/{collaborator}', CollaboratorsUpdateController::class)->name('update')->middleware(['role_or_permission:super-admin|collaborators.update']);
Route::delete('collaborators/{collaborator}', CollaboratorsDestroyController::class)->name('destroy')->middleware(['role_or_permission:super-admin|collaborators.destroy']);
