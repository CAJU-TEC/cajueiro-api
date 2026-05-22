<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait HasTelegramNotifications
{
    protected static function bootHasTelegramNotifications(): void
    {
        foreach (['created', 'updated', 'deleted'] as $event) {
            static::$event(function ($model) use ($event) {
                $modelClass = static::class;
                Log::debug("TelegramTrait: evento disparado [{$event}] em [{$modelClass}]", ['id' => $model->getKey()]);

                try {
                    $config = config('telegram.models.' . $modelClass);

                    if (!$config) {
                        Log::debug("TelegramTrait: sem config para [{$modelClass}] — ignorando");
                        return;
                    }

                    if (!in_array($event, $config['events'])) {
                        Log::debug("TelegramTrait: evento [{$event}] não listado para [{$modelClass}] — ignorando");
                        return;
                    }

                    Log::debug("TelegramTrait: gerando mensagem para [{$modelClass}:{$event}]");
                    $message = ($config['message'])($event, $model);

                    if (!$message) {
                        Log::debug("TelegramTrait: closure retornou null para [{$modelClass}:{$event}] — notificação suprimida");
                        return;
                    }

                    Log::debug("TelegramTrait: enviando para chats", ['chats' => $config['chats']]);
                    app(\App\Services\TelegramService::class)
                        ->sendToMultiple($config['chats'], $message);

                } catch (\Throwable $e) {
                    Log::warning('HasTelegramNotifications: erro inesperado', [
                        'model' => $modelClass,
                        'event' => $event,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            });
        }
    }
}
