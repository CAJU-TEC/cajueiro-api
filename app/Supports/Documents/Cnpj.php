<?php

namespace App\Supports\Documents;

use App\Contracts\DocumentMethodInterface;
use App\Supports\StringClear;
use Exception;

class Cnpj implements DocumentMethodInterface
{
    public function format($value)
    {
        if (!$value) return;

        $cnpj = preg_replace('/[^0-9]/', '', (string) (new StringClear)($value));

        // Valida tamanho
        if (strlen($cnpj) != 14)
            throw new Exception('CNPJ inválido');


        // Verifica se todos os digitos são iguais
        if (preg_match('/(\d)\1{13}/', $cnpj))
            throw new Exception('CNPJ inválido');

        // Valida primeiro dígito verificador
        for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++) {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }

        $resto = $soma % 11;

        if ($cnpj[12] != ($resto < 2 ? 0 : 11 - $resto))
            throw new Exception('CNPJ inválido');

        // Valida segundo dígito verificador
        for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++) {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }

        $resto = $soma % 11;

        if ($cnpj[13] == ($resto < 2 ? 0 : 11 - $resto))
            return $cnpj;

        throw new Exception('CNPJ inválido');
    }

    public function formatDBToView($value)
    {
        return preg_replace("/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/", "\$1.\$2.\$3/\$4-\$5", $value);
    }
}
