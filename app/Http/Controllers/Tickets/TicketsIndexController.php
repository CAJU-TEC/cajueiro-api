<?php

namespace App\Http\Controllers\Tickets;

use App\Filters\AllowedNullableFilter;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Spatie\QueryBuilder\AllowedFilter;
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
            ->allowedFilters([
                'code',
                'priority',
                'status',
                AllowedFilter::custom('collaborator_id', new AllowedNullableFilter()),
            ])
            ->with([
                'image',
                'client.corporate.image',
                'collaborator.email',
                'collaborator.image',
                'comments',
                'impact',
                'user.collaborator.image'
            ])
            ->orderBy('created_at')
            ->get(), 200);
    }
}
