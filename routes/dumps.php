<?php

use App\Http\Controllers\Dumps\DumpsCancelController;
use App\Http\Controllers\Dumps\DumpsIndexController;
use App\Http\Controllers\Dumps\DumpsOrganizacoesController;
use App\Http\Controllers\Dumps\DumpsShowController;
use App\Http\Controllers\Dumps\DumpsStoreController;
use Illuminate\Support\Facades\Route;

// dumps (atualização de DEV/HMG a partir de PROD): usam as mesmas permissões de corporates
Route::get('dumps', DumpsIndexController::class)->name('index')->middleware(['role_or_permission:super-admin|corporates.index']);
// antes de dumps/{dump}, senão "organizacoes" seria lido como um id
Route::get('dumps/organizacoes', DumpsOrganizacoesController::class)->name('organizacoes')->middleware(['role_or_permission:super-admin|corporates.index']);
Route::get('dumps/{dump}', DumpsShowController::class)->name('show')->middleware(['role_or_permission:super-admin|corporates.show']);
Route::post('dumps', DumpsStoreController::class)->name('store')->middleware(['role_or_permission:super-admin|corporates.store']);
Route::post('dumps/{dump}/cancel', DumpsCancelController::class)->name('cancel')->middleware(['role_or_permission:super-admin|corporates.update']);
