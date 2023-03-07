<?php

namespace App\Http\Controllers\Tickets;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketsShowController extends Controller
{
    public function __construct(private Ticket $ticket)
    {
    }

    public function __invoke($id)
    {
        $ticket = $this->ticket->with([
            'impact',
            'client.email',
            'images',
            'user.collaborator.image',
            'collaborator.image',
            'comments.collaborator.image',
            'comments.image',
        ])->findOrFail($id);
        return response()->json($ticket, 200);
    }
}
