<?php

use App\Http\Controllers\Email\EmailTicketController;
use Illuminate\Support\Facades\Route;

// users
Route::get('notification', EmailTicketController::class)->name('ticket');

// fazer o logout...
