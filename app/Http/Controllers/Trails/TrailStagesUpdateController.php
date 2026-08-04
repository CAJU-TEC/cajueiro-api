<?php

namespace App\Http\Controllers\Trails;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Trail\TrailStageStoreRequest;
use App\Models\TrailStage;

class TrailStagesUpdateController extends Controller
{
    public function __construct(private TrailStage $stages) {}

    public function __invoke(TrailStageStoreRequest $request, $id)
    {
        $stage = $this->stages->findOrFail($id);

        $stage->update($request->only([
            'description',
            'note',
            'job_plan_id',
            'position',
            'required_count',
        ]));

        return response()->json($stage->load(['jobPlan', 'levels.materials', 'materials']), 200);
    }
}
