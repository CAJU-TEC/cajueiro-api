<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;

class TelegramTestNotify extends Command
{
    protected $signature = 'telegram:test-notify
                            {--chat=devs : Alias do chat ou "all" para todos}
                            {--message=Teste de notificação Telegram : Mensagem a enviar}';

    protected $description = 'Envia uma mensagem de teste via Telegram';

    public function handle(TelegramService $telegram): int
    {
        $chatOption = $this->option('chat');
        $message    = $this->option('message');

        $chats = $chatOption === 'all'
            ? array_keys(config('telegram.chats', []))
            : [$chatOption];

        foreach ($chats as $alias) {
            $chatId = config("telegram.chats.{$alias}");

            if (!$chatId) {
                $this->warn("Chat '{$alias}' não configurado — pulando.");
                continue;
            }

            try {
                $telegram->sendToChatId($chatId, $message);
                $this->info("✓ Enviado para '{$alias}' ({$chatId})");
            } catch (\Throwable $e) {
                $this->error("✗ Falha ao enviar para '{$alias}': " . $e->getMessage());
            }
        }

        return self::SUCCESS;
    }
}
