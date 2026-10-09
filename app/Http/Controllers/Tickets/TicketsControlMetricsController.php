<?php

namespace App\Http\Controllers\Tickets;

use App\Http\Controllers\Controller;
use App\Queries\TicketsControlQuery;
use Illuminate\Http\Request;

class TicketsControlMetricsController extends Controller
{
    public function __invoke(Request $request)
    {
        // Uma única query agrupada alimenta todos os cards.
        $porStatus = TicketsControlQuery::base($request)
            ->selectRaw('tickets.status, COUNT(tickets.id) as total')
            ->groupBy('tickets.status')
            ->pluck('total', 'status')
            ->map(fn ($total) => (int) $total);

        return response()->json([
            'success' => true,
            'data' => [
                // Total = tudo que os filtros listam, inclusive finalizados quando incluídos.
                'total' => $porStatus->sum(),
                'aguardandoValidacao' => $porStatus->get('backlog', 0),
                'emAnalise' => $porStatus->get('analyze', 0),
                'todo' => $porStatus->get('todo', 0),
                'development' => $porStatus->get('development', 0),
                'test' => $porStatus->get('test', 0),
            ],
        ], 200);
    }
}
