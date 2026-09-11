<?php

namespace App\Http\Controllers\Schedules;

use App\Http\Controllers\Controller;
use App\Models\Schedule;

class SchedulesTodayController extends Controller
{
    public function __construct(private Schedule $schedules) {}

    public function __invoke()
    {
        return response()->json(
            $this->schedules
                ->with(['collaborators:id,first_name,last_name'])
                ->whereDate('date', now())
                ->get(),
            200
        );
    }
}
