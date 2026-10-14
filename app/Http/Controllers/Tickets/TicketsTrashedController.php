<?php

namespace App\Http\Controllers\Tickets;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;

class TicketsTrashedController extends Controller
{
    // protocolos apagados (exclusão lógica), do mais recente para o mais antigo
    public function __invoke()
    {
        $tickets = DB::table('tickets')
            ->leftJoin('clients', 'clients.id', '=', 'tickets.client_id')
            ->whereNotNull('tickets.deleted_at')
            ->orderByDesc('tickets.deleted_at')
            ->get([
                'tickets.id',
                'tickets.code',
                'tickets.subject',
                'tickets.platform',
                DB::raw("CONCAT_WS(' ', clients.first_name, clients.last_name) as client"),
                'tickets.deleted_at',
            ]);

        // quem apagou vem da auditoria; protocolos apagados antes dela não têm essa informação
        $deletedBy = DB::table('audits')
            ->where('auditable_type', (new Ticket)->getMorphClass())
            ->where('event', 'deleted')
            ->whereIn('auditable_id', $tickets->pluck('id'))
            ->orderBy('id')
            ->pluck('user_name', 'auditable_id');

        return response()->json($tickets->map(function ($ticket) use ($deletedBy) {
            $ticket->deleted_by = $deletedBy[$ticket->id] ?? null;
            return $ticket;
        })->values());
    }
}
