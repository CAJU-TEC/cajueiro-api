<?php

namespace App\Http\Controllers\Tickets;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketsControlByClientController extends Controller
{
    public function __construct(private Ticket $ticket)
    {
    }

    public function __invoke(Request $request)
    {
        $query = $this->ticket
            ->with([
                'client.corporate.image',
                'collaborator',
                'tester',
                'impact'
            ])
            ->whereIn('status', ['backlog', 'analyze', 'development', 'test', 'pending', 'validation', 'todo'])
            ->whereHas('client.corporate'); // Apenas tickets com client que tem corporate

        // Filtro por corporate
        if ($request->has('corporate_id')) {
            $query->whereHas('client', function ($q) use ($request) {
                $q->where('corporate_id', $request->corporate_id);
            });
        }

        // Filtro por status
        if ($request->has('status') && $request->status !== 'todos') {
            $query->where('status', $request->status);
        }

        // Filtro por ano
        if ($request->has('periodo')) {
            $ano = $request->periodo; // Formato: 2026
            $query->whereYear('created_at', $ano);
        }

        $tickets = $query->orderBy('created_at', 'desc')->get();

        // Agrupar por corporate (filtrar tickets sem corporate)
        $grouped = $tickets
            ->filter(function ($ticket) {
                return $ticket->client && $ticket->client->corporate;
            })
            ->groupBy(function ($ticket) {
                return $ticket->client->corporate_id;
            })
            ->map(function ($corporateTickets) {
                $corporate = $corporateTickets->first()->client->corporate;

                // Verificação adicional de segurança
                if (!$corporate) {
                    return null;
                }

                return [
                    'id' => $corporate->id,
                    'nome' => $corporate->full_name ?? $corporate->first_name,
                    'initials' => $corporate->initials ?? null,
                    'expanded' => true,
                    'protocolos' => $corporateTickets->map(function ($ticket) {
                    $dataAbertura = \Carbon\Carbon::parse($ticket->created_at);
                    $agora = \Carbon\Carbon::now();

                    // Calcular SLA (exemplo: dias desde abertura)
                    $diasAberto = $dataAbertura->diffInDays($agora);
                    $horasAberto = $dataAbertura->diffInHours($agora);

                    // Determinar se está atrasado (exemplo: mais de 3 dias)
                    $atrasado = $diasAberto > 3;

                    return [
                        'id' => $ticket->id,
                        'numero' => $ticket->code,
                        'descricao' => $ticket->subject,
                        'dataAbertura' => $dataAbertura->format('d/m/Y'),
                        'dev' => $ticket->collaborator
                            ? $ticket->collaborator->first_name . ' ' . $ticket->collaborator->last_name
                            : 'Não atribuído',
                        'qa' => $ticket->tester
                            ? $ticket->tester->first_name . ' ' . $ticket->tester->last_name
                            : 'Não atribuído',
                        'status' => $ticket->status,
                        'sla' => $diasAberto > 0 ? $diasAberto : $horasAberto,
                        'slaUnidade' => $diasAberto > 0 ? 'dias' : 'horas',
                        'atrasado' => $atrasado,
                    ];
                })->values()
            ];
        })
        ->filter() // Remove valores null
        ->values();

        return response()->json([
            'success' => true,
            'data' => $grouped
        ], 200);
    }
}
