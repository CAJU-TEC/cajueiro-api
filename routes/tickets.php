<?php

use App\Http\Controllers\Tickets\TicketsDestroyController;
use App\Http\Controllers\Tickets\TicketsIndexController;
use App\Http\Controllers\Tickets\TicketsPatchCollaboratorController;
use App\Http\Controllers\Tickets\TicketsShowController;
use App\Http\Controllers\Tickets\TicketsStoreController;
use App\Http\Controllers\Tickets\TicketsUpdateController;
use Illuminate\Support\Facades\Route;

// tickets
Route::get('tickets', TicketsIndexController::class)->name('index');
Route::get('tickets/{ticket}', TicketsShowController::class)->name('show');
Route::post('tickets', TicketsStoreController::class)->name('store');
Route::put('tickets/{ticket}', TicketsUpdateController::class)->name('update');
Route::delete('tickets/{ticket}', TicketsDestroyController::class)->name('destroy');
Route::patch('tickets/{ticket}', TicketsPatchCollaboratorController::class)->name('patchCollaborator');
