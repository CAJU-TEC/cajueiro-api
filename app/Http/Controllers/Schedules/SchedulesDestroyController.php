<?php

namespace App\Http\Controllers\Schedules;

use App\Http\Controllers\Controller;
use App\Models\Schedule;

class SchedulesDestroyController extends Controller
{
    public function __construct(private Schedule $schedules) {}

    public function __invoke($id)
    {
        $schedule = $this->schedules->findOrFail($id);
        $schedule->delete();

        return response()->json([], 204);
    }
}
