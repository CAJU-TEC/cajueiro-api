<?php

namespace App\Http\Controllers\Tickets;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Auth;

class TicketsNotificationsController extends Controller
{
    public function __construct(private Ticket $ticket)
    {
    }

    public function __invoke()
    {
        $notifications = Auth::user();
        $payload = [
            'unRead' => $notifications->unreadNotifications,
            'read' => $notifications->readNotifications,
            'all' => $notifications->notifications
        ];
        return response()->json($payload, 200);
    }
}
