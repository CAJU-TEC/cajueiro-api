<?php

namespace App\Http\Controllers\Schedules;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Schedule\ScheduleStoreRequest;
use App\Models\Schedule;

class SchedulesStoreController extends Controller
{
    public function __construct(private Schedule $schedules) {}

    public function __invoke(ScheduleStoreRequest $request)
    {
        $schedule = $this->schedules->create($request->only([
            'title',
            'date',
            'start_time',
            'lunch_start_time',
            'lunch_duration_minutes',
            'end_time',
        ]));

        $schedule->collaborators()->sync(
            collect($request->collaborator_ids)->mapWithKeys(fn ($collaboratorId, $position) => [$collaboratorId => ['position' => $position]])
        );

        return response()->json($schedule->load('collaborators'), 201);
    }
}
