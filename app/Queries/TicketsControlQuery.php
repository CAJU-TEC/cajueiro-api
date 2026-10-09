<?php

namespace App\Queries;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Query base do módulo de Controle de Protocolos.
 *
 * Toda filtragem acontece aqui, no banco, para que o custo de um novo filtro
 * seja apenas mais uma cláusula WHERE (e não mais dados trafegados/processados).
 * Usa query builder + joins (sem Eloquent) para evitar hidratar models,
 * $appends e eager loading que a tela não utiliza.
 */
class TicketsControlQuery
{
    public const STATUSES = [
        'backlog', 'analyze', 'development', 'test',
        'pending', 'validation', 'todo', 'done',
    ];

    public const LABELS = [
        'backlog' => 'aguardando',
        'todo' => 'a fazer',
        'analyze' => 'análise',
        'development' => 'desenvolvimento',
        'test' => 'teste',
        'pending' => 'pendente',
        'validation' => 'validação',
        'done' => 'finalizado',
    ];

    /** Dias a partir dos quais um protocolo aberto é considerado atrasado. */
    public const DIAS_ATRASO = 4;

    /**
     * Os joins de dev/QA só entram quando algo os usa (busca textual ou
     * listagem de protocolos), para o resumo e as métricas não pagarem por eles.
     */
    public static function base(Request $request, bool $comColaboradores = false): Builder
    {
        $query = DB::table('tickets')
            ->join('clients', 'clients.id', '=', 'tickets.client_id')
            ->join('corporates', 'corporates.id', '=', 'clients.corporate_id')
            ->whereNull('tickets.deleted_at')
            ->whereNull('clients.deleted_at')
            ->whereNull('corporates.deleted_at');

        if ($comColaboradores || $request->filled('search')) {
            $query->leftJoin('collaborators as dev', 'dev.id', '=', 'tickets.collaborator_id')
                ->leftJoin('collaborators as qa', 'qa.id', '=', 'tickets.tester_id');
        }

        static::applyFilters($query, $request);

        return $query;
    }

    private static function statuses(Request $request): array
    {
        $informados = array_filter(explode(',', (string) $request->input('status')));
        $validos = array_values(array_intersect($informados, self::STATUSES));

        return $validos ?: self::STATUSES;
    }

    private static function applyFilters(Builder $query, Request $request): void
    {
        $query->whereIn('tickets.status', static::statuses($request));

        if ($request->filled('corporate_id')) {
            $query->where('clients.corporate_id', $request->input('corporate_id'));
        }

        if ($request->filled('platform')) {
            $query->whereIn('tickets.platform', array_filter(explode(',', $request->input('platform'))));
        }

        if ($request->filled('colaborador_id')) {
            static::applyColaborador($query, $request);
        }

        if ($request->filled('periodo')) {
            $inicio = Carbon::create((int) $request->input('periodo'))->startOfYear();
            // Intervalo (sargable) em vez de whereYear(), que impede o uso de índice.
            $query->where('tickets.created_at', '>=', $inicio)
                ->where('tickets.created_at', '<=', (clone $inicio)->endOfYear());
        }

        if ($request->filled('search')) {
            $busca = trim($request->input('search'));
            $termo = '%' . addcslashes($busca, '%_\\') . '%';
            // A busca também casa com o nome do status exibido na tela.
            $statusPeloNome = array_keys(array_filter(
                self::LABELS,
                fn ($label) => mb_stripos($label, mb_strtolower($busca)) !== false
            ));

            $query->where(function (Builder $q) use ($termo, $statusPeloNome) {
                if ($statusPeloNome) {
                    $q->orWhereIn('tickets.status', $statusPeloNome);
                }
                $q->orWhere('tickets.subject', 'like', $termo)
                    ->orWhere('tickets.code', 'like', $termo)
                    ->orWhere('corporates.first_name', 'like', $termo)
                    ->orWhere('corporates.last_name', 'like', $termo)
                    ->orWhereRaw("CONCAT_WS(' ', dev.first_name, dev.last_name) like ?", [$termo])
                    ->orWhereRaw("CONCAT_WS(' ', qa.first_name, qa.last_name) like ?", [$termo]);
            });
        }
    }

    /**
     * Protocolos em que o colaborador atua como dev, QA e/ou criador
     * (papel = lista separada por vírgula; sem papel, vale qualquer um).
     * created_id referencia o usuário, então é resolvido via collaborators.user_id.
     */
    private static function applyColaborador(Builder $query, Request $request): void
    {
        $id = $request->input('colaborador_id');
        $papeis = array_intersect(
            explode(',', (string) $request->input('papel')),
            ['dev', 'qa', 'criador']
        ) ?: ['dev', 'qa', 'criador'];

        $query->where(function (Builder $q) use ($id, $papeis) {
            if (in_array('dev', $papeis)) {
                $q->orWhere('tickets.collaborator_id', $id);
            }
            if (in_array('qa', $papeis)) {
                $q->orWhere('tickets.tester_id', $id);
            }
            if (in_array('criador', $papeis)) {
                $q->orWhereIn('tickets.created_id', function (Builder $sub) use ($id) {
                    $sub->from('collaborators')->select('user_id')->where('id', $id);
                });
            }
        });
    }

    public static function limiteAtraso(): Carbon
    {
        return now()->subDays(self::DIAS_ATRASO);
    }
}
