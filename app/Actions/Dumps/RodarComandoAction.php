<?php

namespace App\Actions\Dumps;

use App\Models\Dump;
use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Roda um comando no bash e lança exceção se falhar. As mensagens de erro vão para dumps.log.
 * "pipefail": sem ele, se o mysqldump falhar, o mysql termina "bem" com entrada vazia
 * e a atualização pareceria ter dado certo.
 */
class RodarComandoAction
{
    public function __invoke(Dump $dump, array $variaveis, string $comando, ?callable $aCadaTick = null): void
    {
        $processo = $this->criarProcesso($variaveis, $comando, config('db_refresh.timeout'));

        $processo->start(function ($tipo, $texto) use ($dump) {
            if ($tipo === Process::ERR) {
                $dump->log = substr($dump->log . $texto, -20000);
            }
        });

        // $aCadaTick($processo) roda a cada 5 s enquanto o comando está rodando (usado para medir o andamento).
        $ultimoTick = 0;
        while ($processo->isRunning()) {
            $processo->checkTimeout();

            if (time() - $ultimoTick >= 5) {
                $ultimoTick = time();

                // Cancelada pela tela: derruba o processo e sai sem erro (o status já está como falha).
                // Via SSH, a queda da conexão encerra o script remoto (ver CopiarDadosAction).
                if ($dump->fresh()?->status === Dump::FALHA) {
                    $processo->stop(5);
                    return;
                }

                if ($aCadaTick) {
                    $aCadaTick($processo);
                }
            }

            usleep(500000);
        }
        $dump->save();

        if (!$processo->isSuccessful()) {
            throw new RuntimeException(trim($processo->getErrorOutput()) ?: 'Processo terminou com erro.');
        }
    }

    /**
     * O comando vira um script que entra no bash pela entrada padrão (stdin), com as variáveis
     * em "export" no começo: assim as senhas não aparecem na lista de processos (ps).
     * Com DB_REFRESH_SSH_HOST definido, o bash roda naquela máquina via SSH (perto dos bancos,
     * os dados não passam por aqui); sem ele, roda nesta máquina.
     */
    public function criarProcesso(array $variaveis, string $comando, int $timeout): Process
    {
        $script = '';
        foreach ($variaveis as $nome => $valor) {
            $script .= "export $nome=" . escapeshellarg((string) $valor) . "\n";
        }
        $script .= "set -o pipefail\n$comando\n";

        $ssh = config('db_refresh.ssh');
        $bash = $ssh['host']
            ? [
                'ssh',
                ...($ssh['key'] ? ['-i', $ssh['key']] : []),
                '-o', 'BatchMode=yes',          // nunca pergunta senha: sem chave, falha na hora
                '-o', 'ServerAliveInterval=30', // percebe se a conexão cair
                "{$ssh['user']}@{$ssh['host']}",
                'bash -s',
            ]
            : ['bash', '-s'];

        return new Process($bash, null, null, $script, $timeout);
    }
}
