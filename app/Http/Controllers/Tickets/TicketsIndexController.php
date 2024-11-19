<?php

namespace App\Http\Controllers\Tickets;

use App\Filters\AllowedFinishedFilter;
use App\Filters\AllowedNullableFilter;
use App\Filters\AllowedNullableOrIdFilter;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class TicketsIndexController extends Controller
{
    // Constantes para os campos de filtros e includes permitidos
    private const ALLOWED_FIELDS = [
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
    ];

    private const ALLOWED_INCLUDES = [
        'collaborator.image',
        'impact',
        'user.collaborator',
        'client.corporate.image',
    ];

    private $allowedFilters = [
        'code',
        'priority',
        'status',
    ];

    public function addAllowedFilter()
    {
        $this->allowedFilters[] = AllowedFilter::custom('date_finish_ticket', new AllowedFinishedFilter());
        $this->allowedFilters[] = AllowedFilter::custom('collaborator_id', new AllowedNullableFilter());
        $this->allowedFilters[] = AllowedFilter::scope('starts_before');
        $this->allowedFilters[] = AllowedFilter::scope('today');
    }

    // Método para obter todos os filtros
    public function getAllowedFilters()
    {
        return $this->allowedFilters;
    }

    public function __construct(private Ticket $tickets)
    {
        $this->addAllowedFilter();
    }

    public function __invoke(Request $request)
    {
        $query = QueryBuilder::for(Ticket::query())
            ->allowedFields(self::ALLOWED_FIELDS)
            ->allowedIncludes(self::ALLOWED_INCLUDES)
            ->allowedFilters($this->getAllowedFilters())
            ->orderBy('tickets.code', 'desc');

        $tickets = $request->paginate
            ? $query->paginate($request->paginate)->appends($request->query())
            : $query->get();

        return response()->json($tickets, 200);
    }
}
