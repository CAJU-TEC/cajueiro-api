<?php

namespace App\Contracts;

interface DocumentMethodInterface
{
    public function format(string $value);
    public function formatDBToView(int $value);
}
