<?php

namespace App\Supports\Documents;

class DocumentsSupport
{

    public function processDocument($method, $value)
    {
        return $method->format($value);
    }

    public function processReturnDocument($method, $value)
    {
        return $method->formatDBToView($value);
    }
}
