<?php

// app/Services/ReportService.php
namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Ticket;
use App\Models\Comment;
use App\Models\Collaborator;

class ReportService
{
    protected array $excludedIds = [
        '5b499a26-6f55-459a-b350-6c2d62c2100f',
        'f8d16225-970d-459c-b955-62ed1d8f2b43',
        '776ca974-2d03-4816-a3a1-2cb5a7d64efa',
    ];

    // === SUPPORT ===
    public function getSupportTicketsByCollaborator(?int $month = null, ?int $year = null): array
    {
        [$start, $end] = $this->getDateRange($month, $year);

        $tickets = Ticket::select('created_id', DB::raw('COUNT(*) as total'))
            ->with([
                'user.collaborator' => function ($q) {
                    $q->select('id', 'user_id', 'first_name')
                        ->whereNull('deleted_at');
                }
            ])
            ->whereNull('deleted_at')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('created_id')
            ->orderByDesc('total')
            ->get();

        return $tickets->map(fn($t) => [
            'nome' => optional(optional($t->user)->collaborator)->first_name ?? 'Desconhecido',
            'total' => (int) $t->total,
        ])->toArray();
    }

    public function getSupportTicketsByClient(?int $month = null, ?int $year = null): array
    {
        [$start, $end] = $this->getDateRange($month, $year);

        $tickets = Ticket::select(
            DB::raw("TRIM(BOTH ' ' FROM TRIM(BOTH '[]' FROM SUBSTRING_INDEX(SUBSTRING_INDEX(subject, ']', 1), '[', -1))) AS cliente_abrev"),
            DB::raw("SUM(CASE WHEN validated = 'yes' THEN 1 ELSE 0 END) AS externos"),
            DB::raw("SUM(CASE WHEN validated = 'no' THEN 1 ELSE 0 END) AS internos"),
            DB::raw("SUM(CASE WHEN platform = 'mobile' THEN 1 ELSE 0 END) AS mobile"),
            DB::raw("SUM(CASE WHEN platform = 'web' THEN 1 ELSE 0 END) AS web")
        )
            ->whereNull('deleted_at')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('cliente_abrev')
            ->orderByDesc(DB::raw("(SUM(CASE WHEN validated = 'yes' THEN 1 ELSE 0 END) + SUM(CASE WHEN validated = 'no' THEN 1 ELSE 0 END))"))
            ->get();

        return $tickets->map(fn($t) => [
            'cliente' => $t->cliente_abrev ?: 'Não identificado',
            'externos' => (int) $t->externos,
            'internos' => (int) $t->internos,
            'mobile' => (int) $t->mobile,
            'web' => (int) $t->web,
            'total' => (int) $t->externos + (int) $t->internos,
        ])->toArray();
    }


    // === DEVELOPMENT ===
    public function getDevTicketsCountByCollaborator(?int $month = null, ?int $year = null): array
    {
        [$start, $end] = $this->getDateRange($month, $year);

        return Comment::select('collaborator_id', DB::raw('COUNT(DISTINCT commentable_id) as quantidade'))
            ->whereBetween('created_at', [$start, $end])
            ->where('status', 'test')
            ->whereNull('deleted_at')
            ->whereNotIn('collaborator_id', $this->excludedIds)
            ->groupBy('collaborator_id')
            ->with('collaborator:id,first_name')
            ->get()
            ->map(fn($item) => [
                'colaborador' => $item->collaborator->first_name ?? 'Desconhecido',
                'quantidade'  => (int) $item->quantidade,
            ])
            ->toArray();
    }

    public function getDevAverageCompletionTimeByCollaborator(?int $month = null, ?int $year = null): array
    {
        [$start, $end] = $this->getDateRange($month, $year);

        $ticketsComTempo = DB::table('tickets')
            ->join(DB::raw('(
                SELECT commentable_id, MAX(created_at) as data_finalizacao
                FROM comments
                WHERE status = "test" AND deleted_at IS NULL AND created_at BETWEEN ? AND ?
                GROUP BY commentable_id
            ) as finalizacoes'), 'tickets.id', '=', 'finalizacoes.commentable_id')
            ->select(
                'tickets.collaborator_id',
                DB::raw('DATEDIFF(finalizacoes.data_finalizacao, tickets.date_attribute_ticket) as lead_time')
            )
            ->whereNotIn('tickets.collaborator_id', $this->excludedIds)
            ->whereBetween('finalizacoes.data_finalizacao', [$start, $end])
            ->addBinding([$start, $end], 'select')
            ->get();

        $agrupado = $ticketsComTempo->groupBy('collaborator_id');
        $result = [];

        foreach ($agrupado as $collabId => $tickets) {
            $quantidade = $tickets->count();
            $media = $quantidade > 0 ? round($tickets->sum('lead_time') / $quantidade, 1) : 0;

            $collab = Collaborator::find($collabId);
            $result[] = [
                'colaborador' => $collab?->first_name ?? 'Desconhecido',
                'tempo_medio' => $media . ' dias',
                'quantidade'  => $quantidade,
            ];
        }

        return $result;
    }

    // === QA ===
    public function getQATicketsByStatus(?int $month = null, ?int $year = null): array
    {
        [$start, $end] = $this->getDateRange($month, $year);

        $comments = DB::table('comments')
            ->select('collaborator_id', 'status', DB::raw('COUNT(DISTINCT commentable_id) as total'))
            ->whereIn('collaborator_id', $this->excludedIds)
            ->whereNull('deleted_at')
            ->whereBetween('created_at', [$start, $end])
            ->whereIn('status', ['test', 'pending', 'validation', 'done'])
            ->groupBy('collaborator_id', 'status')
            ->get();

        return $comments->groupBy('collaborator_id')->map(function ($group) {
            $collab = Collaborator::find($group->first()->collaborator_id);
            $qaName = $collab ? $collab->first_name . ' ' . $collab->last_name : 'Desconhecido';

            $statusCounts = [
                'teste'      => $group->where('status', 'test')->sum('total'),
                'validacao'  => $group->where('status', 'validation')->sum('total'),
                'pendente'   => $group->where('status', 'pending')->sum('total'),
                'finalizado' => $group->where('status', 'done')->sum('total'),
            ];

            return compact('qaName', 'statusCounts');
        })->values()->toArray();
    }

    // Helper
    protected function getDateRange(?int $month, ?int $year): array
    {
        $year = $year ?? now()->year;
        $startDate = Carbon::create($year, $month ?? 1, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        return [$startDate, $endDate];
    }
}
