<?php

namespace App\Enums\CheckLists;

enum status: string
{
    case OPEN = 'open';
    case PROGRESS = 'progress';
    case COMPLETED = 'completed';

    public function description(): string
    {
        return match ($this) {
            self::OPEN => 'aberto',
            self::PROGRESS => 'executando',
            self::COMPLETED => 'finalizado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::OPEN => 'green',       // verde para 'aberto'
            self::PROGRESS => 'yellow',   // amarelo para 'executando'
            self::COMPLETED => 'gray',  // vermelho para 'finalizado'
        };
    }
}
