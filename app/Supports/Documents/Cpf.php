<?php

namespace App\Supports\Documents;

use App\Contracts\DocumentMethodInterface;
use App\Supports\StringClear;
use Exception;

class Cpf implements DocumentMethodInterface
{
    public function format($value)
    {
        if (!$value) return;

        $cpf = preg_replace('/[^0-9]/', "", (new StringClear)($value));

        if (strlen($cpf) !== 11 || preg_match('/([0-9])\1{10}/', $cpf)) {
            throw new Exception('CPF inválido');
        }

        $number_quantity_to_loop = [9, 10];

        foreach ($number_quantity_to_loop as $item) {

            $sum = 0;
            $number_to_multiplicate = $item + 1;

            for ($index = 0; $index < $item; $index++) {

                $sum += $cpf[$index] * ($number_to_multiplicate--);
            }

            $result = (($sum * 10) % 11);

            if ($cpf[$item] != $result) {
                throw new Exception('CPF inválido');
            }
        }

        return $cpf;
    }

    public function formatDBToView($value)
    {
        return preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "\$1.\$2.\$3-\$4", $value);
    }
}
