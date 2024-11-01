<?php

namespace App\Casts;

use App\Enums\Tickets\Status;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;

class TicketStatus implements CastsAttributes
{
    public function get($model, $key, $value, $attributes)
    {
        $status = status::tryFrom($value);

        return $status ? [
            'description' => $status->description(),
            'color' => $status->color()
        ] : null;
    }

    public function set($model, $key, $value, $attributes)
    {
        if (!status::tryFrom($value)) {
            throw new InvalidArgumentException("O status '{$value}' não é válido.");
        }

        return $value;
    }
}
