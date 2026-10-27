<?php

namespace App\Jobs;

use App\Models\Dump;
use App\Actions\Dumps\AtualizarBancoAction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class RefreshDatabaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    public $failOnTimeout = true;
    public $timeout;

    public function __construct(public Dump $dump)
    {
        $this->timeout = config('db_refresh.timeout');
        $this->onConnection(config('db_refresh.queue'));
    }

    // Sem ShouldBeUnique: o lock dele ficava preso se o worker morresse e o dispatch seguinte era descartado
    // em silêncio. Um por vez por organização+destino já é garantido pelo 409 do DumpsStoreController.

    public function handle(AtualizarBancoAction $atualizarBanco): void
    {
        // Cancelada pela tela antes de o worker pegar: não roda.
        if ($this->dump->fresh()?->status !== Dump::PENDENTE) {
            return;
        }

        $this->dump->update([
            'status' => Dump::EXECUTANDO,
            'started_at' => now(),
            'progresso' => 'iniciando',
            'percentual' => null,
            'bytes_copiados' => null,
            'bytes_estimados' => null,
        ]);

        $atualizarBanco($this->dump);

        // Cancelada durante a cópia: mantém o status de falha.
        if ($this->dump->fresh()->status === Dump::FALHA) {
            return;
        }

        $this->dump->update([
            'status' => Dump::CONCLUIDO,
            'progresso' => null,
            'percentual' => 100,
            'finished_at' => now(),
        ]);
    }

    public function failed(Throwable $e): void
    {
        Log::error('Atualização de banco falhou', [
            'dump' => $this->dump->id,
            'organizacao' => $this->dump->corporate?->nome,
            'destino' => $this->dump->destino,
            'etapa' => $this->dump->progresso,
            'erro' => $e->getMessage(),
        ]);

        $this->dump->update([
            'status' => Dump::FALHA,
            'progresso' => null,
            'finished_at' => now(),
            'erro' => Str::limit($e->getMessage(), 1000),
        ]);
    }
}
