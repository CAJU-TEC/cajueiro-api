<?php

return [
    'token' => env('TELEGRAM_BOT_TOKEN'),
    'timeout' => 3,

    'chats' => [
        'devs' => [
            'chat_id' => env('TELEGRAM_CHAT_DEVS'),
            'thread_id' => env('TELEGRAM_THREAD_DEVS'),
        ],
        'gestores' => [
            'chat_id' => env('TELEGRAM_CHAT_GESTORES'),
            'thread_id' => env('TELEGRAM_THREAD_GESTORES'),
        ],
        'suporte' => [
            'chat_id' => env('TELEGRAM_CHAT_SUPORTE'),
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
                    $model->refresh()->load(['user.collaborator', 'collaborator', 'tester']);
                    $subject = $model->subject ?? '—';
                    $criador = $model->user?->collaborator?->first_name ?? $model->user?->name ?? '—';
                    $desenvolvedor = $model->collaborator?->first_name ?? '—';
                    $qa = $model->tester?->first_name ?? '—';

                    $lines = [
                        "*Criação 🆕*",
                        "{$criador} criou o *#{$model->code} {$subject}*",
                    ];

                    if ($model->dufy === 'yes') {
                        $lines[] = "🟢 PLANTÃO";
                    }

                    if ($model->validated === 'yes') {
                        $lines[] = "✅ Validação";
                    }

                    return implode("\n", $lines);
                }

                if ($event === 'updated') {
                    if (!$model->wasChanged('status')) {
                        return null;
                    }

                    $model->load(['collaborator', 'tester', 'user.collaborator']);

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
                    $desenvolvedor = $model->collaborator?->first_name ?? '—';
                    $qa = $model->tester?->first_name ?? '—';
                    $criador = $model->user?->collaborator?->first_name ?? $model->user?->name ?? '—';

                    $updater = \Illuminate\Support\Facades\Auth::user()?->load('collaborator');
                    $updaterName = $updater?->collaborator?->first_name ?? $updater?->name ?? '—';

                    $lines = [
                        "*Atualização 🔄*",
                        "{$updaterName} enviou *#{$model->code} {$subject}* para *{$statusDesc}* {$statusEmoji}",
                    ];

                    if ($model->dufy === 'yes') {
                        $lines[] = "🟢 PLANTÃO";
                    }

                    $lines[] = "*DESENVOLVEDOR:* {$desenvolvedor}";
                    $lines[] = "*QA:* {$qa}";
                    $lines[] = "*CRIADOR:* {$criador}";

                    return implode("\n", $lines);
                }

                return null;
            },
        ],
    ],
];
