<?php

namespace App\Http\Controllers\Schedules;

use App\Http\Controllers\Controller;
use App\Models\Schedule;

class SchedulesShowController extends Controller
{
    public function __construct(private Schedule $schedules) {}

    public function __invoke($id)
    {
        return response()->json(
            $this->schedules
                ->with(['collaborators:id,first_name,last_name'])
                ->findOrFail($id),
            200
        );
    }
}
