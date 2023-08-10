<?php

use App\Http\Controllers\Tickets\TicketsDestroyController;
use App\Http\Controllers\Tickets\TicketsGraphDashboardController;
use App\Http\Controllers\Tickets\TicketsIndexController;
use App\Http\Controllers\Tickets\TicketsKanbanController;
use App\Http\Controllers\Tickets\TicketsNotificationsController;
use App\Http\Controllers\Tickets\TicketsPatchCollaboratorController;
use App\Http\Controllers\Tickets\TicketsShowController;
use App\Http\Controllers\Tickets\TicketsStoreController;
use App\Http\Controllers\Tickets\TicketsUpdateController;
use Illuminate\Support\Facades\Route;

// tickets
Route::post('tickets/graphDashboard/', TicketsGraphDashboardController::class)->name('graphDashboard')->middleware(['role_or_permission:super-admin|tickets.index']);
Route::get('tickets/notifications/', TicketsNotificationsController::class)->name('notifications')->middleware(['role_or_permission:super-admin|tickets.index']);

Route::get('tickets', TicketsIndexController::class)->name('index')->middleware(['role_or_permission:super-admin|tickets.index']);
Route::get('tickets/{ticket}', TicketsShowController::class)->name('show')->middleware(['role_or_permission:super-admin|tickets.show']);
Route::get('tickets/kanban/{ticket}', TicketsKanbanController::class)->name('kanban');
Route::post('tickets', TicketsStoreController::class)->name('store')->middleware(['role_or_permission:super-admin|tickets.store']);
Route::put('tickets/{ticket}', TicketsUpdateController::class)->name('update')->middleware(['role_or_permission:super-admin|tickets.update']);
Route::delete('tickets/{ticket}', TicketsDestroyController::class)->name('destroy')->middleware(['role_or_permission:super-admin|tickets.destroy']);
Route::patch('tickets/{ticket}', TicketsPatchCollaboratorController::class)->name('patchCollaborator');
