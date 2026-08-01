<?php

namespace App\Http\Controllers\Trails;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Trail\TrailStageStoreRequest;
use App\Models\Trail;
use App\Models\TrailStage;

class TrailStagesStoreController extends Controller
{
    public function __construct(private TrailStage $stages) {}

    public function __invoke(TrailStageStoreRequest $request, $trailId)
    {
        $trail = Trail::findOrFail($trailId);

        $stage = $this->stages->create([
            'trail_id' => $trail->id,
            'description' => $request->input('description'),
            'note' => $request->input('note'),
            'job_plan_id' => $request->input('job_plan_id'),
            'position' => $request->input('position', $this->nextPosition($trail)),
            'required_count' => $request->input('required_count', 1),
        ]);

        return response()->json($stage->load(['jobPlan', 'levels', 'materials']), 201);
    }

    private function nextPosition(Trail $trail): int
    {
        return (int) TrailStage::where('trail_id', $trail->id)->max('position') + 1;
    }
}
