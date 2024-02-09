<?php

namespace App\Supports\DatesTimes;

use Illuminate\Support\Facades\Log;
use App\Supports\StringClear;
use Carbon\Carbon;

class DateSupport
{

    public function convertAmericaForBrazil(?string $date)
    {
        if ($date === '' || $date === null) {
            return null;
        }
        try{
            return Carbon::parse($date)->format('d/m/Y');
        } catch (\Exception $e) {
            return Log::error('Error: cannot convert America to Brazil', ['date' => $date, 'error' => $e->getMessage()]);
        }
    }

    public function convertBrazilForAmerica(?string $date)
    {
        if ($date === '' || $date === null) {
            return null;
        }
        try{
            return Carbon::createFromFormat('d/m/Y', $date)->format('Y-m-d');
        } catch (\Exception $e) {
            return Log::error('Error: cannot convert Brazil to America', ['date' => $date, 'error' => $e->getMessage()]);
        }
    }

    public function formatPasswordBrazil(?string $date)
    {
        if ($date === '' || $date === null) {
            return null;
        }
        try{
            return (new StringClear())($date);
        } catch (\Exception $e) {
            return Log::error('Error: cannot format password', ['date' => $date, 'error' => $e->getMessage()]);
        }
    }
}
