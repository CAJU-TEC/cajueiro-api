<?php

namespace App\Actions\Dumps;

use App\Models\Dump;
use RuntimeException;

/**
 * Apaga o banco de destino e cria um vazio com o mesmo nome.
 */
class RecriarBancoDeDestinoAction
{
    public function __construct(private RodarComandoAction $rodarComando) {}

    public function __invoke(Dump $dump, array $variaveis): void
    {
        // MYSQL_PWD é a senha que o mysql usa, sem aparecer na linha de comando.
        // --no-defaults ignora o ~/.my.cnf da máquina, que senão sobrepõe MYSQL_PWD com outra senha.
        $comando = 'MYSQL_PWD="$SENHA_DESTINO" mysql --no-defaults -h "$HOST_DESTINO" -P "$PORTA_DESTINO" -u "$USUARIO_DESTINO" '
            . '-e "DROP DATABASE IF EXISTS $BANCO_DESTINO; CREATE DATABASE $BANCO_DESTINO"';

        try {
            ($this->rodarComando)($dump, $variaveis, $comando);
        } catch (RuntimeException $e) {
            throw new RuntimeException("Erro no DESTINO ({$variaveis['HOST_DESTINO']}) ao apagar/criar o banco {$variaveis['BANCO_DESTINO']}: " . $e->getMessage());
        }
    }
}
