<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dump extends Model
{
    use HasUuids;

    public const PENDENTE = 'pendente';
    public const EXECUTANDO = 'executando';
    public const CONCLUIDO = 'concluido';
    public const FALHA = 'falha';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'corporate_id',
        'user_id',
        'destino',
        'status',
        'progresso',
        'percentual',
        'bytes_copiados',
        'bytes_estimados',
        'started_at',
        'finished_at',
        'log',
        'erro',
    ];

    protected $casts = [
        'bytes_copiados' => 'integer',
        'bytes_estimados' => 'integer',
        'started_at' => 'datetime:d/m/Y H:i',
        'finished_at' => 'datetime:d/m/Y H:i',
    ];

    protected $appends = ['iniciado_em'];

    // Início em ISO 8601, para a tela calcular o tempo decorrido (started_at sai formatado para exibição).
    public function getIniciadoEmAttribute(): ?string
    {
        return $this->started_at?->toIso8601String();
    }

    public function corporate(): BelongsTo
    {
        return $this->belongsTo(Corporate::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
