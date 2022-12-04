<?php

namespace App\Supports;

use DomainException;

class StringClear
{
    public function __invoke($value)
    {
        return str_replace(['.', '-', '/', ',', ' ', ';'], '', $value);
    }
}
