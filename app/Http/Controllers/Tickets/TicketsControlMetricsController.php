<?php

namespace App\Http\Controllers\Tickets;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketsControlMetricsController extends Controller
{
    public function __construct(private Ticket $ticket)
    {
    }

    public function __invoke(Request $request)
    {
        $query = $this->ticket
            ->whereIn('status', ['backlog', 'analyze', 'development', 'test', 'pending', 'validation', 'todo', 'done'])
            ->whereHas('client.corporate'); // Apenas tickets com client que tem corporate

        // Aplicar os mesmos filtros do controller de listagem
        if ($request->has('corporate_id')) {
            $query->whereHas('client', function ($q) use ($request) {
                $q->where('corporate_id', $request->corporate_id);
            });
        }

        if ($request->has('status') && $request->status !== 'todos') {
            $query->where('status', $request->status);
        }

        if ($request->has('periodo')) {
            $ano = $request->periodo; // Formato: 2026
            $query->whereYear('created_at', $ano);
        }

        $total = $query->count();
        $aguardandoValidacao = (clone $query)->where('status', 'backlog')->count();
        $emAnalise = (clone $query)->where('status', 'analyze')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $total,
                'aguardandoValidacao' => $aguardandoValidacao,
                'emAnalise' => $emAnalise,
            ]
        ], 200);
    }
}
