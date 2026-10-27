<?php

use App\Http\Controllers\Dumps\DumpsCancelController;
use App\Http\Controllers\Dumps\DumpsIndexController;
use App\Http\Controllers\Dumps\DumpsOrganizacoesController;
use App\Http\Controllers\Dumps\DumpsShowController;
use App\Http\Controllers\Dumps\DumpsStoreController;
use Illuminate\Support\Facades\Route;

// dumps (atualização de DEV/HMG a partir de PROD). Aberto a qualquer usuário logado.
Route::get('dumps', DumpsIndexController::class)->name('index');
// antes de dumps/{dump}, senão "organizacoes" seria lido como um id
Route::get('dumps/organizacoes', DumpsOrganizacoesController::class)->name('organizacoes');
Route::get('dumps/{dump}', DumpsShowController::class)->name('show');
Route::post('dumps', DumpsStoreController::class)->name('store');
Route::post('dumps/{dump}/cancel', DumpsCancelController::class)->name('cancel');
