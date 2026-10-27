<?php

namespace App\Actions\Dumps;

/**
 * Tamanho de cada tabela do banco de PRODUÇÃO ([tabela => bytes]), para calcular o andamento da cópia.
 * É estatística do MySQL (espaço em disco), não o tamanho do dump; devolve null se não conseguir medir.
 * Roda no mesmo lugar da cópia (local ou via SSH), que é de onde o PROD aceita conexão.
 */
class MedirTamanhoBancoAction
{
    public function __construct(private RodarComandoAction $rodarComando) {}

    public function __invoke(array $variaveis): ?array
    {
        // As tabelas que vão sem dados não entram na conta.
        $semDados = collect(config('db_refresh.somente_estrutura'))->map(fn ($tabela) => "'$tabela'")->implode(',');
        $consulta = "SELECT table_name, data_length FROM information_schema.tables WHERE table_schema='\$BANCO_ORIGEM' AND table_type='BASE TABLE'"
            . ($semDados ? " AND table_name NOT IN ($semDados)" : '');

        // No MySQL 8 os tamanhos ficam em cache por 24h; a 1ª tentativa zera o cache da sessão.
        // A 2ª, sem isso, é para servidores que não conhecem essa variável.
        foreach (['SET SESSION information_schema_stats_expiry=0; ', ''] as $antes) {
            $processo = $this->rodarComando->criarProcesso(
                $variaveis,
                'MYSQL_PWD="$SENHA_ORIGEM" mysql --no-defaults -h "$HOST_ORIGEM" -P "$PORTA_ORIGEM" -u "$USUARIO_ORIGEM" -N -B -e "' . $antes . $consulta . '"',
                30
            );
            $processo->run();

            if ($processo->isSuccessful()) {
                $tamanhos = [];
                foreach (array_filter(explode("\n", trim($processo->getOutput()))) as $linha) {
                    [$tabela, $bytes] = explode("\t", $linha);
                    $tamanhos[$tabela] = (int) $bytes;
                }

                return $tamanhos;
            }
        }

        return null;
    }
}
