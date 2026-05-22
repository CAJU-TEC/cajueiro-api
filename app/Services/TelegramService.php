<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    public function sendToChat(string $chatAlias, string $message): void
    {
        $config = config("telegram.chats.{$chatAlias}");

        if (!$config) {
            Log::warning('TelegramService: alias de chat não encontrado', ['alias' => $chatAlias]);
            return;
        }

        $chatId   = is_array($config) ? $config['chat_id']   : $config;
        $threadId = is_array($config) ? ($config['thread_id'] ?? null) : null;

        $this->sendToChatId($chatId, $message, $threadId);
    }

    public function sendToChatId(string|int $chatId, string $message, string|int|null $threadId = null): void
    {
        $token = config('telegram.token');

        Log::debug('TelegramService: enviando mensagem', ['chat_id' => $chatId, 'thread_id' => $threadId]);

        $payload = [
            'chat_id'    => $chatId,
            'text'       => $message,
            'parse_mode' => 'Markdown',
        ];

        if ($threadId) {
            $payload['message_thread_id'] = $threadId;
        }

        try {
            $response = Http::timeout(config('telegram.timeout', 3))
                ->withOptions(['curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]])
                ->post("https://api.telegram.org/bot{$token}/sendMessage", $payload);

            Log::debug('TelegramService: resposta da API', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('TelegramService: falha ao enviar mensagem', [
                'chat_id' => $chatId,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    public function sendToMultiple(array $chatAliases, string $message): void
    {
        foreach ($chatAliases as $alias) {
            try {
                $this->sendToChat($alias, $message);
            } catch (\Throwable $e) {
                Log::warning('TelegramService: falha ao enviar para alias', [
                    'alias' => $alias,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
