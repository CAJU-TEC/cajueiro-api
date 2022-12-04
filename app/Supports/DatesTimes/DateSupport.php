<?php

namespace App\Supports\DatesTimes;

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
}
