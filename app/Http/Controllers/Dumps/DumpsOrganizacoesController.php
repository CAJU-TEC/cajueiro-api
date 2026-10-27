<?php

namespace App\Http\Controllers\Dumps;

use App\Http\Controllers\Controller;
use App\Models\Corporate;
use App\Models\Dump;

/**
 * Organizações que aparecem na tela de atualização de DEV/HMG: todas as ativas. Cadastro incompleto é recusado ao disparar.
 */
class DumpsOrganizacoesController extends Controller
{
    public function __construct(private Corporate $organizacoes) {}

    public function __invoke()
    {
        $organizacoes = $this->organizacoes
            ->where('ativo', true)
            ->with(['group:id,nome', 'dumps' => fn ($query) => $query->orderByDesc('finished_at')->orderByDesc('created_at')])
            ->orderBy('first_name')
            ->get(['id', 'initials', 'first_name', 'last_name', 'host_prod', 'banco_prod', 'host_dev', 'banco_dev', 'host_hmg', 'banco_hmg', 'corporate_group_id']);

        // Mesma janela do 409 do DumpsStoreController: registro preso e antigo não conta como em andamento.
        $limite = now()->subSeconds(config('db_refresh.timeout') + 600);
        $terminados = [Dump::CONCLUIDO, Dump::FALHA];

        // "ultimo": a última atualização terminada de cada destino.
        // "andamento": a atualização pendente/executando de cada destino, se houver.
        $organizacoes->each(function ($organizacao) use ($limite, $terminados) {
            $destinos = collect(['dev', 'hmg']);

            $organizacao->ultimo = $destinos->mapWithKeys(function ($destino) use ($organizacao, $terminados) {
                $dump = $organizacao->dumps->first(fn ($d) => $d->destino === $destino && in_array($d->status, $terminados, true));

                return [$destino => $dump ? [
                    'status' => $dump->status,
                    'finished_at' => $dump->finished_at->format('d/m/Y H:i'),
                    'erro' => $dump->erro,
                ] : null];
            });

            $organizacao->andamento = $destinos->mapWithKeys(function ($destino) use ($organizacao, $limite, $terminados) {
                $dump = $organizacao->dumps->first(fn ($d) => $d->destino === $destino
                    && !in_array($d->status, $terminados, true)
                    && $d->created_at >= $limite);

                return [$destino => $dump ? $dump->only(['id', 'status', 'progresso', 'percentual', 'bytes_copiados', 'bytes_estimados', 'iniciado_em']) : null];
            });

            $organizacao->unsetRelation('dumps');
        });

        return response()->json($organizacoes, 200);
    }
}
