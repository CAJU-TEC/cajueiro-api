<?php

namespace App\Enums\Tickets;

enum Status: string
{
    case OPEN = 'backlog';
    case PROGRESS = 'todo';
    case COMPLETED = 'analyze';
    case DEVELOPMENT = 'development';
    case TEST = 'test';
    case PENDING = 'pending';
    case DONE = 'done';
    case VALIDATION = 'validation';

    public function description(): string
    {
        return match ($this) {
            self::OPEN => mb_strtoupper('aberto'),
            self::PROGRESS => mb_strtoupper('executando'),
            self::COMPLETED => mb_strtoupper('finalizado'),
            self::DEVELOPMENT => mb_strtoupper('desenvolvimento'),
            self::TEST => mb_strtoupper('teste'),
            self::PENDING => mb_strtoupper('pendente'),
            self::DONE => mb_strtoupper('concluído'),
            self::VALIDATION => mb_strtoupper('validação'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::OPEN => 'green',          // verde para 'aberto'
            self::PROGRESS => 'yellow',     // amarelo para 'executando'
            self::COMPLETED => 'gray',      // cinza para 'finalizado'
            self::DEVELOPMENT => 'blue',    // azul para 'em desenvolvimento'
            self::TEST => 'purple',         // roxo para 'em teste'
            self::PENDING => 'orange',      // laranja para 'pendente'
            self::DONE => 'darkgreen',      // verde para 'concluído'
            self::VALIDATION => 'teal',     // verde água para 'em validação'
        };
    }
}
