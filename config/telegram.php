<?php

return [
    'token' => env('TELEGRAM_BOT_TOKEN'),
    'timeout' => 3,

    'chats' => [
        'devs' => [
            'chat_id'   => env('TELEGRAM_CHAT_DEVS'),
            'thread_id' => env('TELEGRAM_THREAD_DEVS'),
        ],
        'gestores' => [
            'chat_id'   => env('TELEGRAM_CHAT_GESTORES'),
            'thread_id' => env('TELEGRAM_THREAD_GESTORES'),
        ],
        'suporte' => [
            'chat_id'   => env('TELEGRAM_CHAT_SUPORTE'),
            'thread_id' => env('TELEGRAM_THREAD_SUPORTE'),
        ],
    ],

    /*
     * Closure recebe ($event, $model) e retorna string ou null.
     * Retornar null suprime a notificação para aquele evento.
     */
    'models' => [
        \App\Models\Ticket::class => [
            'events' => ['created', 'updated'],
            'chats' => ['devs'],
            'message' => function ($event, $model) {
                $subject = $model->subject ?? '—';

                if ($event === 'created') {
                    $model->refresh()->load('user');
                    $subject = $model->subject ?? '—';
                    $criador = $model->user?->name ?? '—';
                    $validacao = $model->validated === 'yes' ? 'Validado' : 'Não validar';

                    $lines = ["*Protocolo Criado 🆕 *", "#{$model->code} — {$subject}"];

                    if ($model->dufy === 'yes') {
                        $lines[] = "*PLANTÃO* 🟢";
                    }

                    $lines[] = "*VALIDAÇÃO:* {$validacao}";
                    $lines[] = "*CRIADOR:* {$criador}";

                    return implode("\n", $lines);
                }

                if ($event === 'updated') {
                    if (!$model->wasChanged('status')) {
                        return null;
                    }

                    $model->load(['collaborator', 'tester', 'user']);

                    $statusEmoji = match ($model->status) {
                        'backlog' => '🟢',
                        'test' => '🔴',
                        'development' => '🟠',
                        'pending' => '⚫️',
                        'validation' => '🟣',
                        'done' => '⚪️',
                        default => '⬜️',
                    };

                    $statusEnum = \App\Enums\Tickets\Status::tryFrom($model->status);
                    $statusDesc = $statusEnum?->description() ?? $model->status ?? '—';
                    $desenvolvedor = $model->collaborator?->full_name ?? '—';
                    $qa = $model->tester?->full_name ?? '—';
                    $criador = $model->user?->name ?? '—';

                    $plantao = $model->dufy === 'yes' ? '🟢 Sim' : '🔴 Não';

                    return implode("\n", [
                        "*Atualização 🔄*",
                        "*Protocolo:* #{$model->code} — {$subject}",
                        "*PLANTÃO:* {$plantao}",
                        "*STATUS:* {$statusEmoji} {$statusDesc}",
                        "*DESENVOLVEDOR:* {$desenvolvedor}",
                        "*QA:* {$qa}",
                        "*CRIADOR:* {$criador}",
                    ]);
                }

                return null;
            },
        ],
    ],
];
