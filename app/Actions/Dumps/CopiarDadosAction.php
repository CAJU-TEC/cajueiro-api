<?php

namespace App\Actions\Dumps;

use App\Models\Dump;
use Symfony\Component\Process\Process;
use RuntimeException;

/**
 * Copia o banco de PRODUÇÃO para o destino:
 *   mysqldump lê de PROD e escreve o SQL; o pipe (|) entrega esse SQL ao mysql,
 *   que o executa no destino. Nada é gravado em disco.
 * --single-transaction lê o banco sem travar a produção.
 * São dois mysqldump em sequência: 1º a estrutura de todas as tabelas, views e procedures/functions (--routines),
 * 2º os dados, menos os das tabelas em db_refresh.somente_estrutura (logs), que chegam ao destino vazias.
 * O sed tira o DEFINER (usuário de PROD) das procedures e views: no destino elas ficam com o usuário de DEV/HMG,
 * que não precisa de privilégio de administrador para criá-las. As procedures só vêm se o usuário de PROD
 * tiver SHOW_ROUTINE; sem ela o mysqldump as omite sem erro.
 */
class CopiarDadosAction
{
    public function __construct(private RodarComandoAction $rodarComando, private MedirTamanhoBancoAction $medirTamanho) {}

    public function __invoke(Dump $dump, array $variaveis): void
    {
        $mysqldump = 'MYSQL_PWD="$SENHA_ORIGEM" mysqldump --no-defaults --no-tablespaces --single-transaction '
            . '-h "$HOST_ORIGEM" -P "$PORTA_ORIGEM" -u "$USUARIO_ORIGEM"';
        $semDados = collect(config('db_refresh.somente_estrutura'))
            ->map(fn ($tabela) => "--ignore-table=\"\$BANCO_ORIGEM.$tabela\"")
            ->implode(' ');

        // Andamento, tudo pela saída padrão (que chega aqui mesmo via SSH):
        // - a 1ª linha imprime a cada 5 s quantos bytes o mysql já recebeu do pipe (lido de /proc);
        // - o tee repassa ao grep a linha "-- Dumping data for table `x`" que o mysqldump escreve ao começar cada tabela.
        // --skip-triggers na estrutura: as triggers vêm no 2º dump, depois dos dados, para não dispararem na carga.
        // exec 3>&1 guarda a saída do script: dentro do tee a saída padrão já é o pipe para o mysql,
        // então o grep escreve no 3 para os marcadores chegarem aqui, e não no banco.
        // Cancelamento: se a conexão SSH cair (worker parou o processo), o echo falha e o "pkill -P $$" derruba
        // o script inteiro (mysqldump, mysql...); sem isso a cópia seguiria sozinha no servidor.
        $comando = 'exec 3>&1' . "\n"
            . '( trap "" PIPE; while sleep 5; do p=$(pgrep -n -P $$ -x mysql) && b=$(awk \'/^rchar/{print $2}\' /proc/$p/io) || b=; echo "$b" || pkill -P $$; done ) &' . "\n"
            . "{ $mysqldump --no-data --skip-triggers --routines \"\$BANCO_ORIGEM\" | sed -E 's/DEFINER=`[^`]+`@`[^`]+`//' "
            . "&& $mysqldump --no-create-info $semDados \"\$BANCO_ORIGEM\"; } "
            . "| tee >(grep -a --line-buffered '^-- Dumping data for table ' >&3) "
            . '| MYSQL_PWD="$SENHA_DESTINO" mysql --no-defaults -h "$HOST_DESTINO" -P "$PORTA_DESTINO" -u "$USUARIO_DESTINO" "$BANCO_DESTINO"' . "\n"
            . 'status=$?; kill $! 2>/dev/null; exit $status';

        // Percentual = tamanho (em PROD) das tabelas já copiadas + parte da atual / tamanho total. Bytes do dump não servem
        // para isso: o dump é texto SQL e sai de 0,7x a 2x o tamanho em disco, então nunca batia com o total.
        $tamanhos = array_diff_key(
            ($this->medirTamanho)($variaveis) ?? [],
            array_flip(config('db_refresh.somente_estrutura'))
        );
        $total = array_sum($tamanhos);
        $dump->update(['bytes_estimados' => $total ?: null]);

        $resto = '';      // linha que chegou pela metade na leitura anterior
        $atual = null;    // tabela sendo copiada agora
        $copiado = 0;     // soma dos tamanhos das tabelas já terminadas
        $bytes = 0;       // último rchar lido do mysql
        $inicio = 0;      // rchar quando a tabela atual começou

        $medirAndamento = function (Process $processo) use ($dump, $tamanhos, $total, &$resto, &$atual, &$copiado, &$bytes, &$inicio) {
            $linhas = explode("\n", $resto . $processo->getIncrementalOutput());
            $resto = array_pop($linhas);
            $dados = [];

            foreach ($linhas as $linha) {
                if (preg_match('/^-- Dumping data for table `(.+)`/', $linha, $m)) {
                    $copiado += $atual !== null ? ($tamanhos[$atual] ?? 0) : 0; // começou outra: a anterior terminou
                    $atual = $m[1];
                    $inicio = $bytes;
                } elseif (ctype_digit(trim($linha))) {
                    $bytes = $dados['bytes_copiados'] = (int) trim($linha);
                }
            }

            if ($total) {
                // Parte da tabela atual: bytes recebidos desde que ela começou, limitados ao tamanho dela
                // (o dump não bate exato com o disco, então dentro da tabela é aproximado).
                $parcial = $atual !== null ? min($tamanhos[$atual] ?? 0, $bytes - $inicio) : 0;
                // nunca diminui e para em 99: os 100% só quando o processo termina
                $dados['percentual'] = max((int) $dump->percentual, min(99, (int) floor(($copiado + $parcial) * 100 / $total)));
            }

            if ($dados) {
                $dump->update($dados);
            }
        };

        try {
            ($this->rodarComando)($dump, $variaveis, $comando, $medirAndamento);
        } catch (RuntimeException $e) {
            // Erros do mysqldump começam com "mysqldump:"; os do mysql, com "ERROR".
            $lado = str_contains($e->getMessage(), 'mysqldump:')
                ? "na ORIGEM/PROD ({$variaveis['HOST_ORIGEM']}) ao ler {$variaveis['BANCO_ORIGEM']}"
                : "no DESTINO ({$variaveis['HOST_DESTINO']}) ao gravar {$variaveis['BANCO_DESTINO']}";

            throw new RuntimeException("Erro $lado: " . $e->getMessage());
        }
    }
}
