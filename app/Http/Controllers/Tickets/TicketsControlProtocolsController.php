<?php

namespace App\Http\Controllers\Tickets;

use App\Http\Controllers\Controller;
use App\Queries\TicketsControlQuery;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Protocolos paginados de uma empresa (carregados ao expandir o painel).
 */
class TicketsControlProtocolsController extends Controller
{
    private const SORTS = [
        'numero' => 'tickets.code',
        'descricao' => 'tickets.subject',
        'dataAbertura' => 'tickets.created_at',
        'sla' => 'tickets.created_at',
        'status' => 'tickets.status',
        'dev' => 'dev.first_name',
        'qa' => 'qa.first_name',
    ];

    public function __invoke(Request $request)
    {
        $request->validate([
            'corporate_id' => 'required|string',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:50',
            'sort_by' => 'nullable|string',
            'descending' => 'nullable|boolean',
        ]);

        $perPage = (int) $request->input('per_page', 10);
        $coluna = self::SORTS[$request->input('sort_by')] ?? 'tickets.created_at';
        $direcao = $request->boolean('descending', true) ? 'desc' : 'asc';
        // 'sla' cresce ao contrário da data de abertura.
        if ($request->input('sort_by') === 'sla') {
            $direcao = $direcao === 'desc' ? 'asc' : 'desc';
        }

        $page = TicketsControlQuery::base($request, true)
            ->select([
                'tickets.id',
                'tickets.code',
                'tickets.subject',
                'tickets.status',
                'tickets.created_at',
                'dev.first_name as dev_first',
                'dev.last_name as dev_last',
                'qa.first_name as qa_first',
                'qa.last_name as qa_last',
            ])
            ->orderBy($coluna, $direcao)
            ->orderBy('tickets.id')
            ->paginate($perPage);

        $agora = now();

        $protocolos = collect($page->items())->map(function ($t) use ($agora) {
            $abertura = Carbon::parse($t->created_at);
            $dias = $abertura->diffInDays($agora);
            $horas = $abertura->diffInHours($agora);

            return [
                'id' => $t->id,
                'numero' => $t->code,
                'descricao' => $t->subject,
                'dataAbertura' => $abertura->format('d/m/Y'),
                'dev' => $t->dev_first ? trim($t->dev_first . ' ' . $t->dev_last) : 'Não atribuído',
                'qa' => $t->qa_first ? trim($t->qa_first . ' ' . $t->qa_last) : 'Não atribuído',
                'status' => $t->status,
                'sla' => $dias > 0 ? $dias : $horas,
                'slaUnidade' => $dias > 0 ? 'dias' : 'horas',
                'atrasado' => $dias >= TicketsControlQuery::DIAS_ATRASO,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $protocolos,
            'meta' => [
                'total' => $page->total(),
                'page' => $page->currentPage(),
                'per_page' => $page->perPage(),
            ],
        ], 200);
    }
}
