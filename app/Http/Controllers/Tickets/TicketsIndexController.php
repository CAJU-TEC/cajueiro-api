<?php

namespace App\Http\Controllers\Tickets;

use App\Filters\AllowedFinishedFilter;
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
        $model = Ticket::query();
        $tickets = QueryBuilder::for($model)
            ->allowedFields(
                'tickets.id',
                'tickets.client_id',
                'tickets.created_id',
                'tickets.collaborator_id',
                'tickets.impact_id',
                'tickets.code',
                'tickets.priority',
                'tickets.type',
                'tickets.dufy',
                'tickets.subject',
                'tickets.status',
                'tickets.date_attribute_ticket',
                'tickets.created_at',
                'tickets.updated_at',
                'tickets.deleted_at',
            )
            ->allowedIncludes(
                'collaborator',
                'impact',
                'user.collaborator'
            )
            ->allowedFilters([
                'code',
                'priority',
                'status',
                AllowedFilter::custom('date_finish_ticket', new AllowedFinishedFilter()),
                AllowedFilter::custom('collaborator_id', new AllowedNullableFilter()),
            ])
            ->orderBy('tickets.code', 'desc')
            ->whereYear('tickets.created_at', date('Y'))
            ->paginate(15)
            ->appends(request()->query());
        return response()->json($tickets, 200);
    }
}
