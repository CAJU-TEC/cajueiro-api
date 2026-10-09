<?php

namespace App\Http\Controllers\Tickets;

use App\Http\Controllers\Controller;
use App\Queries\TicketsControlQuery;
use Illuminate\Http\Request;

/**
 * Resumo leve por empresa (uma linha por corporate, com contagens).
 * Os protocolos de cada empresa são carregados sob demanda por
 * TicketsControlProtocolsController.
 */
class TicketsControlByClientController extends Controller
{
    public function __invoke(Request $request)
    {
        $rows = TicketsControlQuery::base($request)
            ->selectRaw(
                "corporates.id, corporates.first_name, corporates.last_name, corporates.initials,
                COUNT(tickets.id) as total,
                SUM(tickets.status <> 'done') as abertos,
                SUM(tickets.status <> 'done' AND tickets.created_at <= ?) as atrasados",
                [TicketsControlQuery::limiteAtraso()]
            )
            ->groupBy('corporates.id', 'corporates.first_name', 'corporates.last_name', 'corporates.initials')
            ->orderByDesc('total')
            ->orderBy('corporates.first_name')
            ->get()
            ->map(fn ($row) => [
                'id' => $row->id,
                'nome' => $row->first_name && $row->last_name
                    ? $row->first_name . ' ' . $row->last_name
                    : $row->first_name,
                'initials' => $row->initials,
                'total' => (int) $row->total,
                'abertos' => (int) $row->abertos,
                'atrasados' => (int) $row->atrasados,
            ]);

        return response()->json([
            'success' => true,
            'data' => $rows,
        ], 200);
    }
}
