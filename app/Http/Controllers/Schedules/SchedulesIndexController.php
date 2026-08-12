<?php

namespace App\Http\Controllers\Schedules;

use App\Http\Controllers\Controller;
use App\Models\Schedule;

class SchedulesIndexController extends Controller
{
    public function __construct(private Schedule $schedules) {}

    public function __invoke()
    {
        return response()->json(
            $this->schedules
                ->withCount(['collaborators'])
                ->orderByDesc('date')
                ->orderByDesc('created_at')
                ->get(),
            200
        );
    }
}
