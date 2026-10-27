<?php

namespace App\Actions\Dumps;

use App\Models\Corporate;
use InvalidArgumentException;
use RuntimeException;

/**
 * Recusa o que possa apagar produção ou executar algo inesperado.
 */
class ValidarDestinoAction
{
    public function __invoke(Corporate $organizacao, string $destino): void
    {
        if (!in_array($destino, ['dev', 'hmg'], true)) {
            throw new InvalidArgumentException("Destino inválido: $destino");
        }

        if (!$organizacao->ativo) {
            throw new InvalidArgumentException('Organização inativa.');
        }

        $faltando = [];
        foreach (['prod', $destino] as $ambiente) {
            foreach (['host', 'banco'] as $campo) {
                if (!trim((string) $organizacao->{"{$campo}_$ambiente"})) {
                    $faltando[] = "$campo de " . strtoupper($ambiente);
                }
            }
        }
        if ($faltando) {
            throw new InvalidArgumentException("Cadastro incompleto de {$organizacao->nome}: preencha " . implode(', ', $faltando) . ' no cadastro da organização.');
        }

        // Nomes de banco entram no comando, então só aceitam letras, números e "_".
        foreach ([$organizacao->banco_prod, $organizacao->{"banco_$destino"}] as $banco) {
            if (!preg_match('/^[A-Za-z0-9_]+$/', (string) $banco)) {
                throw new InvalidArgumentException("Nome de banco inválido: $banco");
            }
        }

        // Se o destino estiver no servidor de produção, o DROP apagaria produção.
        if (strcasecmp(trim($organizacao->{"host_$destino"}), trim($organizacao->host_prod)) === 0) {
            throw new InvalidArgumentException('O host de destino é o mesmo de produção.');
        }

        foreach (['prod', $destino] as $ambiente) {
            if (!config("db_refresh.$ambiente.user")) {
                throw new RuntimeException("Credenciais de $ambiente não configuradas (DB_REFRESH_*).");
            }
        }
    }
}
