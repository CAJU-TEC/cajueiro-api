<?php

/*
| Atualização de bancos DEV/HMG a partir de PROD.
| Host e nome do banco vêm do cadastro (tabela apps); usuário e senha ficam
| aqui, uma credencial por ambiente. PROD deve ser usuário somente leitura.
*/

return [
    'queue' => 'db-refresh',
    'timeout' => 10800,

    // Tabelas copiadas só com a estrutura (chegam vazias no destino): logs grandes que DEV/HMG não precisam.
    'somente_estrutura' => ['audits', 'notas_logs'],

    // Máquina perto dos bancos que executa os comandos (ex.: piaui04). Vazio = roda nesta máquina.
    'ssh' => [
        'host' => env('DB_REFRESH_SSH_HOST'),
        'user' => env('DB_REFRESH_SSH_USER', 'dumper'),
        'key' => env('DB_REFRESH_SSH_KEY'),
    ],

    'prod' => [
        'user' => env('DB_REFRESH_PROD_USER'),
        'password' => env('DB_REFRESH_PROD_PASSWORD'),
        'port' => env('DB_REFRESH_PROD_PORT', 3306),
    ],

    'dev' => [
        'user' => env('DB_REFRESH_DEV_USER'),
        'password' => env('DB_REFRESH_DEV_PASSWORD'),
        'port' => env('DB_REFRESH_DEV_PORT', 3306),
    ],

    'hmg' => [
        'user' => env('DB_REFRESH_HMG_USER'),
        'password' => env('DB_REFRESH_HMG_PASSWORD'),
        'port' => env('DB_REFRESH_HMG_PORT', 3306),
    ],
];
