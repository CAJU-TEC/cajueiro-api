<?php

// Só o que difere do padrão do pacote owen-it/laravel-auditing (o resto vem de vendor/owen-it/laravel-auditing/config/audit.php).
// Atenção: a mescla é feita por chave de 1º nível, então "user" e "resolvers" precisam estar completos aqui.
return [
    'implementation' => App\Models\Audit::class,

    'user' => [
        'morph_prefix' => 'user',
        'guards' => [
            'sanctum',
            'web',
        ],
        'resolver' => OwenIt\Auditing\Resolvers\UserResolver::class,
    ],

    // sem IP, navegador e URL (dados pessoais que não pedimos)
    'resolvers' => [],

    // não grava auditoria sem valores, exceto estes eventos (que não têm valores de propósito)
    'empty_values' => false,
    'allowed_empty_values' => [
        'retrieved',
        'created',
        'deleted',
        'restored',
    ],

    // artisan/tinker não auditam por padrão; ligar com AUDITING_CONSOLE=true (ex.: em testes)
    'console' => env('AUDITING_CONSOLE', false),
];
