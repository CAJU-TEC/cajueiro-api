<?php

namespace App\Http\Controllers\Tickets;

use App\Http\Controllers\Controller;
use App\Models\Ticket;

class TicketsIndexController extends Controller
{
    public function __construct(private Ticket $tickets)
    {
    }

    //
    public function __invoke()
    {
        return response()->json($this->tickets->with([
            'image',
            'client.corporate.image',
            'collaborator.email',
            'collaborator.image',
            'comments',
            'impact'
        ])->latest()->get(), 200);
    }
}
