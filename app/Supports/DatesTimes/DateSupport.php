<?php

namespace App\Supports\DatesTimes;

use App\Supports\StringClear;
use Carbon\Carbon;

class DateSupport
{

    public function convertAmericaForBrazil(?string $date)
    {
        if ($date == '') return;
        return Carbon::parse($date)->format('d/m/Y');
    }

    public function convertBrazilForAmerica(?string $date)
    {
        if ($date == '') return;
        return Carbon::createFromFormat('d/m/Y', $date)->format('Y-m-d');
    }

    public function formatPasswordBrazil(?string $date)
    {
        if ($date == '') return;
        return (new StringClear())($date);
    }
}
