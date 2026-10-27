<?php

namespace App\Actions\Dumps;

use App\Models\Dump;

/**
 * Copia o banco de PRODUÇÃO de um município para o banco de DEV ou HMG dele.
 * Passos: validar o destino, apagar e recriar o banco de destino, copiar os dados.
 */
class AtualizarBancoAction
{
    public function __construct(
        private ValidarDestinoAction $validarDestino,
        private RecriarBancoDeDestinoAction $recriarBancoDeDestino,
        private CopiarDadosAction $copiarDados,
    ) {}

    public function __invoke(Dump $dump): void
    {
        $organizacao = $dump->corporate;
        $destino = $dump->destino; // 'dev' ou 'hmg'

        ($this->validarDestino)($organizacao, $destino);

        // Tudo que varia vai por variável de ambiente; os comandos são fixos.
        $variaveis = [
            'HOST_ORIGEM' => $organizacao->host_prod,
            'PORTA_ORIGEM' => config('db_refresh.prod.port'),
            'USUARIO_ORIGEM' => config('db_refresh.prod.user'),
            'SENHA_ORIGEM' => config('db_refresh.prod.password'),
            'BANCO_ORIGEM' => $organizacao->banco_prod,

            'HOST_DESTINO' => $organizacao->{"host_$destino"},
            'PORTA_DESTINO' => config("db_refresh.$destino.port"),
            'USUARIO_DESTINO' => config("db_refresh.$destino.user"),
            'SENHA_DESTINO' => config("db_refresh.$destino.password"),
            'BANCO_DESTINO' => $organizacao->{"banco_$destino"},
        ];

        $dump->update(['progresso' => 'apagando banco de destino']);
        ($this->recriarBancoDeDestino)($dump, $variaveis);

        $dump->update(['progresso' => 'copiando dados']);
        ($this->copiarDados)($dump, $variaveis);
    }
}
