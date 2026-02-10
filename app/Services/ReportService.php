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

    // IDs dos desenvolvedores para retrospectiva
    protected array $retrospectivaDevelopers = [
        '351300e0-d5fd-43cd-82b0-c8e13472839c',
        'c0b6d6d6-dbfa-4270-96f1-d7aa39335947',
        'b34a6dfa-c20a-4d49-813b-daad5b21c934',
        'fd6e0d9d-eafa-422e-a797-ae183b1e0726',
        '791bc2fb-b90f-489b-970a-57b4899e3246',
        '5190db91-d7f8-4f7e-80cf-a499d503dfbf',
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

        return Comment::select(
            'comments.collaborator_id',
            'collaborators.first_name',
            DB::raw('COUNT(DISTINCT comments.commentable_id) as quantidade'),
            DB::raw('COUNT(DISTINCT CASE WHEN tickets.validated = "yes" THEN comments.commentable_id END) as externos'),
            DB::raw('COUNT(DISTINCT CASE WHEN tickets.validated = "no" THEN comments.commentable_id END) as internos'),
            DB::raw('COALESCE(AVG(impacts.points), 0) as pontuacao_media_impacto'),
            DB::raw('COALESCE(SUM(impacts.points), 0) as pontuacao_total_impacto')
        )
            ->join('tickets', 'comments.commentable_id', '=', 'tickets.id')
            ->join('collaborators', 'comments.collaborator_id', '=', 'collaborators.id')
            ->leftJoin('impacts', 'tickets.impact_id', '=', 'impacts.id')
            ->where('comments.commentable_type', Ticket::class)
            ->whereBetween('comments.created_at', [$start, $end])
            ->where('comments.status', 'test')
            ->whereNull('collaborators.deleted_at')
            ->whereNull('comments.deleted_at')
            ->whereNull('tickets.deleted_at')
            ->whereNotIn('comments.collaborator_id', $this->excludedIds)
            ->groupBy('comments.collaborator_id', 'collaborators.first_name')
            ->get()
            ->map(fn($item) => [
                'colaborador' => $item->first_name ?? 'Desconhecido',
                'quantidade'  => (int) $item->quantidade,
                'externos'    => (int) $item->externos,
                'internos'    => (int) $item->internos,
                'pontuacao_media_impacto' => round((float) $item->pontuacao_media_impacto, 1),
                'pontuacao_total_impacto' => round((float) $item->pontuacao_total_impacto, 1),
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

    /**
     * Relatório anual completo de desenvolvimento para retrospectiva
     */
    public function getYearlyDevelopmentReport(?int $year = null): array
    {
        $year = $year ?? now()->year;
        $start = Carbon::create($year, 1, 1)->startOfYear();
        $end = Carbon::create($year, 12, 31)->endOfYear();

        // Buscar dados do ano anterior para comparação
        $previousYearStart = Carbon::create($year - 1, 1, 1)->startOfYear();
        $previousYearEnd = Carbon::create($year - 1, 12, 31)->endOfYear();

        // Dados principais do ano atual
        $currentYearData = Comment::select(
            'comments.collaborator_id',
            DB::raw('COUNT(DISTINCT comments.commentable_id) as quantidade'),
            DB::raw('COUNT(DISTINCT CASE WHEN tickets.validated = "yes" THEN comments.commentable_id END) as externos'),
            DB::raw('COUNT(DISTINCT CASE WHEN tickets.validated = "no" THEN comments.commentable_id END) as internos'),
            DB::raw('COALESCE(AVG(impacts.points), 0) as pontuacao_media_impacto'),
            DB::raw('COALESCE(SUM(impacts.points), 0) as pontuacao_total_impacto'),
            DB::raw('COUNT(DISTINCT CASE WHEN impacts.points >= 8 THEN comments.commentable_id END) as tickets_alto_impacto'),
            DB::raw('COUNT(DISTINCT CASE WHEN impacts.points >= 5 THEN comments.commentable_id END) as tickets_critico_alto')
        )
            ->join('tickets', 'comments.commentable_id', '=', 'tickets.id')
            ->leftJoin('impacts', 'tickets.impact_id', '=', 'impacts.id')
            ->whereBetween('comments.created_at', [$start, $end])
            ->where('comments.status', 'test')
            ->whereNull('comments.deleted_at')
            ->whereNull('tickets.deleted_at')
            ->whereIn('comments.collaborator_id', $this->retrospectivaDevelopers)
            ->groupBy('comments.collaborator_id')
            ->with('collaborator:id,first_name')
            ->get();

        // Dados do ano anterior para cálculo de crescimento
        $previousYearData = Comment::select(
            'comments.collaborator_id',
            DB::raw('COUNT(DISTINCT comments.commentable_id) as quantidade')
        )
            ->join('tickets', 'comments.commentable_id', '=', 'tickets.id')
            ->whereBetween('comments.created_at', [$previousYearStart, $previousYearEnd])
            ->where('comments.status', 'test')
            ->whereNull('comments.deleted_at')
            ->whereNull('tickets.deleted_at')
            ->whereIn('comments.collaborator_id', $this->retrospectivaDevelopers)
            ->groupBy('comments.collaborator_id')
            ->get()
            ->keyBy('collaborator_id');

        // Calcular tempo médio e taxa de retrabalho
        $timeData = $this->calculateTimeMetrics($start, $end);
        $reworkData = $this->calculateReworkRate($start, $end);

        // Total de tickets do ano para calcular percentual
        $totalTickets = $currentYearData->sum('quantidade');

        // Preparar dados consolidados com todas as métricas
        $dadosConsolidados = $currentYearData->map(function ($item) use ($previousYearData, $timeData, $reworkData, $totalTickets) {
            $collaboratorId = $item->collaborator_id;
            $quantidadeAtual = (int) $item->quantidade;
            $quantidadeAnterior = $previousYearData->get($collaboratorId)?->quantidade ?? 0;

            // Calcular crescimento
            $crescimento = $quantidadeAnterior > 0
                ? round((($quantidadeAtual - $quantidadeAnterior) / $quantidadeAnterior) * 100, 1)
                : ($quantidadeAtual > 0 ? 100 : 0);

            // Calcular percentual de participação
            $percentual = $totalTickets > 0 ? round(($quantidadeAtual / $totalTickets) * 100, 1) : 0;

            // Descrição de impacto baseada na pontuação média
            $pontuacaoMedia = (float) $item->pontuacao_media_impacto;
            $descricaoImpacto = $this->getImpactDescription($pontuacaoMedia);

            // Dados de tempo
            $tempoInfo = $timeData->get($collaboratorId);
            $tempoMedioDias = $tempoInfo['media'] ?? 0;
            $tempoMedioMinutos = $tempoMedioDias * 24 * 60;
            $tempoMedio = $this->formatTime($tempoMedioDias);

            // Dados de retrabalho
            $rework = $reworkData->get($collaboratorId);
            $taxaRetrabalho = $rework['taxa'] ?? 0;
            $retrabalhos = $rework['quantidade'] ?? 0;

            // Calcular score ponderado para performance (60% complexidade + 40% volume)
            $scorePonderado = ($pontuacaoMedia * 0.6) + ($quantidadeAtual * 0.4);
            $performance = $this->getPerformanceLevel($scorePonderado, $quantidadeAtual);

            // Score de consistência
            $scoreConsistencia = $quantidadeAtual * ($percentual / 100);

            return [
                'colaborador_id' => $collaboratorId,
                'colaborador' => $item->collaborator->first_name ?? 'Desconhecido',
                'quantidade' => $quantidadeAtual,
                'externos' => (int) $item->externos,
                'internos' => (int) $item->internos,
                'pontuacao_media_impacto' => round($pontuacaoMedia, 2),
                'pontuacao_total_impacto' => round((float) $item->pontuacao_total_impacto, 1),
                'descricao_impacto' => $descricaoImpacto,
                'percentual' => $percentual,
                'tickets_alto_impacto' => (int) $item->tickets_alto_impacto,
                'tickets_critico_alto' => (int) $item->tickets_critico_alto,
                'tempo_medio' => $tempoMedio,
                'tempo_medio_minutos' => round($tempoMedioMinutos, 0),
                'crescimento_percentual' => $crescimento,
                'quantidade_ano_anterior' => (int) $quantidadeAnterior,
                'taxa_retrabalho' => round($taxaRetrabalho, 1),
                'retrabalhos' => (int) $retrabalhos,
                'score_ponderado' => round($scorePonderado, 1),
                'score_consistencia' => round($scoreConsistencia, 1),
                'performance' => $performance,
            ];
        })->keyBy('colaborador_id');

        // Atribuir categorias exclusivas
        return $this->distributeCategories($dadosConsolidados)->values()->toArray();
    }

    /**
     * Distribuir categorias exclusivas entre desenvolvedores
     */
    protected function distributeCategories(\Illuminate\Support\Collection $dados): \Illuminate\Support\Collection
    {
        $atribuidos = collect();
        $categorias = [];

        // 1º MVP - Maior pontuação total
        $mvp = $dados->sortByDesc('pontuacao_total_impacto')->first();
        if ($mvp) {
            $categorias[$mvp['colaborador_id']] = 'MVP';
            $atribuidos->push($mvp['colaborador_id']);
        }

        // 2º Especialista - Mais tickets de alto impacto (excluindo já atribuídos)
        $especialista = $dados
            ->whereNotIn('colaborador_id', $atribuidos->toArray())
            ->sortByDesc('tickets_alto_impacto')
            ->first();

        if ($especialista) {
            $categorias[$especialista['colaborador_id']] = 'Especialista';
            $atribuidos->push($especialista['colaborador_id']);
        }

        // 3º Velocista - Menor tempo médio (excluindo já atribuídos e sem tempo)
        $velocista = $dados
            ->whereNotIn('colaborador_id', $atribuidos->toArray())
            ->where('tempo_medio_minutos', '>', 0)
            ->sortBy('tempo_medio_minutos')
            ->first();

        if ($velocista) {
            $categorias[$velocista['colaborador_id']] = 'Velocista';
            $atribuidos->push($velocista['colaborador_id']);
        }

        // 4º Consistência - Melhor score de consistência (excluindo já atribuídos)
        $consistente = $dados
            ->whereNotIn('colaborador_id', $atribuidos->toArray())
            ->sortByDesc('score_consistencia')
            ->first();

        if ($consistente) {
            $categorias[$consistente['colaborador_id']] = 'Consistencia';
            $atribuidos->push($consistente['colaborador_id']);
        }

        // 5º Evolução - Maior crescimento (excluindo já atribuídos)
        $evolucao = $dados
            ->whereNotIn('colaborador_id', $atribuidos->toArray())
            ->sortByDesc('crescimento_percentual')
            ->first();

        if ($evolucao) {
            $categorias[$evolucao['colaborador_id']] = 'Evolucao';
            $atribuidos->push($evolucao['colaborador_id']);
        }

        // 6º Qualidade - Menor taxa de retrabalho (excluindo já atribuídos)
        $qualidade = $dados
            ->whereNotIn('colaborador_id', $atribuidos->toArray())
            ->sortBy('taxa_retrabalho')
            ->first();

        if ($qualidade) {
            $categorias[$qualidade['colaborador_id']] = 'Qualidade';
            $atribuidos->push($qualidade['colaborador_id']);
        }

        // Mapear categorias de volta aos dados - GARANTIR UMA CATEGORIA POR DEV
        return $dados->map(function ($item) use ($categorias) {
            $item['categoria_vencida'] = $categorias[$item['colaborador_id']] ?? null;
            return $item;
        })->filter(function ($item) {
            // Retornar apenas devs que ganharam categoria
            return $item['categoria_vencida'] !== null;
        });
    }

    /**
     * Calcular métricas de tempo médio por colaborador
     */
    protected function calculateTimeMetrics($start, $end): \Illuminate\Support\Collection
    {
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
            ->whereIn('tickets.collaborator_id', $this->retrospectivaDevelopers)
            ->whereBetween('finalizacoes.data_finalizacao', [$start, $end])
            ->addBinding([$start, $end], 'select')
            ->get();

        return $ticketsComTempo->groupBy('collaborator_id')->map(function ($tickets) {
            $quantidade = $tickets->count();
            $media = $quantidade > 0 ? round($tickets->sum('lead_time') / $quantidade, 1) : 0;

            return [
                'media' => $media,
                'quantidade' => $quantidade,
            ];
        });
    }

    /**
     * Calcular taxa de retrabalho por colaborador
     */
    protected function calculateReworkRate($start, $end): \Illuminate\Support\Collection
    {
        // Consideramos retrabalho quando um ticket volta para desenvolvimento após teste
        $retrabalhos = DB::table('comments as c1')
            ->join('comments as c2', function ($join) {
                $join->on('c1.commentable_id', '=', 'c2.commentable_id')
                    ->whereRaw('c2.created_at > c1.created_at')
                    ->where('c2.status', 'dev')
                    ->whereNull('c2.deleted_at');
            })
            ->join('tickets', 'c1.commentable_id', '=', 'tickets.id')
            ->where('c1.status', 'test')
            ->whereBetween('c1.created_at', [$start, $end])
            ->whereNull('c1.deleted_at')
            ->whereNull('tickets.deleted_at')
            ->whereIn('tickets.collaborator_id', $this->retrospectivaDevelopers)
            ->select(
                'tickets.collaborator_id',
                DB::raw('COUNT(DISTINCT c1.commentable_id) as retrabalhos')
            )
            ->groupBy('tickets.collaborator_id')
            ->get()
            ->keyBy('collaborator_id');

        // Total de tickets por colaborador
        $totais = Comment::select(
            'comments.collaborator_id',
            DB::raw('COUNT(DISTINCT comments.commentable_id) as total')
        )
            ->join('tickets', 'comments.commentable_id', '=', 'tickets.id')
            ->whereBetween('comments.created_at', [$start, $end])
            ->where('comments.status', 'test')
            ->whereNull('comments.deleted_at')
            ->whereNull('tickets.deleted_at')
            ->whereIn('comments.collaborator_id', $this->retrospectivaDevelopers)
            ->groupBy('comments.collaborator_id')
            ->get()
            ->keyBy('collaborator_id');

        return $totais->map(function ($total, $collabId) use ($retrabalhos) {
            $qtdRetrabalhos = $retrabalhos->get($collabId)?->retrabalhos ?? 0;
            $qtdTotal = $total->total;
            $taxa = $qtdTotal > 0 ? ($qtdRetrabalhos / $qtdTotal) * 100 : 0;

            return [
                'quantidade' => (int) $qtdRetrabalhos,
                'taxa' => $taxa,
            ];
        });
    }

    /**
     * Obter descrição do impacto baseada na pontuação
     */
    protected function getImpactDescription(float $points): string
    {
        if ($points >= 10) return 'Crítico';
        if ($points >= 8) return 'Alto';
        if ($points >= 5) return 'Médio';
        return 'Baixo';
    }

    /**
     * Formatar tempo em dias para formato legível
     */
    protected function formatTime(float $dias): string
    {
        if ($dias < 1) {
            $horas = round($dias * 24, 1);
            return $horas . 'h';
        }

        return round($dias, 1) . ' dias';
    }

    /**
     * Determinar nível de performance
     */
    protected function getPerformanceLevel(float $score, int $quantidade): string
    {
        if ($score >= 50 && $quantidade >= 20) return 'Excelente';
        if ($score >= 30 && $quantidade >= 15) return 'Ótimo';
        if ($score >= 20 && $quantidade >= 10) return 'Bom';
        if ($score >= 10) return 'Regular';
        return 'Iniciante';
    }

    // === QA ===
    public function getQATicketsByStatus(?int $month = null, ?int $year = null): array
    {
        [$start, $end] = $this->getDateRange($month, $year);

        $comments = DB::table('comments')
            ->select(
                'comments.collaborator_id',
                'comments.status',
                DB::raw('COUNT(DISTINCT comments.commentable_id) as total'),
                DB::raw('COUNT(DISTINCT CASE WHEN tickets.validated = "yes" THEN comments.commentable_id END) as externos'),
                DB::raw('COUNT(DISTINCT CASE WHEN tickets.validated = "no" THEN comments.commentable_id END) as internos')
            )
            ->join('tickets', 'comments.commentable_id', '=', 'tickets.id')
            ->whereIn('comments.collaborator_id', $this->excludedIds)
            ->whereNull('comments.deleted_at')
            ->whereNull('tickets.deleted_at')
            ->whereBetween('comments.created_at', [$start, $end])
            ->whereIn('comments.status', ['test', 'pending', 'validation', 'done'])
            ->groupBy('comments.collaborator_id', 'comments.status')
            ->get();

        return $comments
            ->groupBy('collaborator_id')
            ->map(function ($group) {

                $collab = Collaborator::find($group->first()->collaborator_id);
                $qaName = $collab
                    ? $collab->first_name . ' ' . $collab->last_name
                    : 'Desconhecido';

                $statusCounts = [
                    'teste' => [
                        'total'    => (int) $group->where('status', 'test')->sum('total'),
                        'externos' => (int) $group->where('status', 'test')->sum('externos'),
                        'internos' => (int) $group->where('status', 'test')->sum('internos'),
                    ],
                    'validacao' => [
                        'total'    => (int) $group->where('status', 'validation')->sum('total'),
                        'externos' => (int) $group->where('status', 'validation')->sum('externos'),
                        'internos' => (int) $group->where('status', 'validation')->sum('internos'),
                    ],
                    'pendente' => [
                        'total'    => (int) $group->where('status', 'pending')->sum('total'),
                        'externos' => (int) $group->where('status', 'pending')->sum('externos'),
                        'internos' => (int) $group->where('status', 'pending')->sum('internos'),
                    ],
                    'finalizado' => [
                        'total'    => (int) $group->where('status', 'done')->sum('total'),
                        'externos' => (int) $group->where('status', 'done')->sum('externos'),
                        'internos' => (int) $group->where('status', 'done')->sum('internos'),
                    ],
                ];

                return compact('qaName', 'statusCounts');
            })
            ->values()
            ->toArray();
    }


    // Helper
    protected function getDateRange(?int $month, ?int $year): array
    {
        $date = now();

        if ($month && $year) {
            $date = Carbon::parse("$year-$month-01");
        }

        return [
            $date->copy()->startOfMonth(),
            $date->copy()->endOfMonth(),
        ];
    }
}
