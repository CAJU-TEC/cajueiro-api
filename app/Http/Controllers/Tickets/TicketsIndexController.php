<?php

namespace App\Http\Controllers\Tickets;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Spatie\QueryBuilder\QueryBuilder;

class TicketsIndexController extends Controller
{

    public function __construct(private Ticket $tickets)
    {
    }

    //
    public function __invoke()
    {
        return response()->json(QueryBuilder::for(Ticket::class)
            ->allowedFilters(['collaborator_id', 'code'])
            ->with([
                'image',
                'client.corporate.image',
                'collaborator.email',
                'collaborator.image',
                'comments',
                'impact',
                'user.collaborator.image'
            ])
            ->latest()
            ->get(), 200);
    }
}
