<?php

use App\Http\Controllers\Email\EmailTicketController;
use Illuminate\Support\Facades\Route;

// users
Route::get('notification', EmailTicketController::class)->name('ticket')->middleware(['role_or_permission:super-admin|emails.ticket']);

// fazer o logout...
