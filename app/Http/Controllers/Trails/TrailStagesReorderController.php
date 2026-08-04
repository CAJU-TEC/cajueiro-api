<?php

namespace App\Http\Controllers\Trails;

use App\Http\Controllers\Controller;
use App\Models\Trail;
use App\Models\TrailStage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrailStagesReorderController extends Controller
{
    public function __invoke(Request $request, $trailId)
    {
        $request->validate([
            'stages' => 'required|array|min:1',
            'stages.*' => 'required|uuid',
        ]);

        $trail = Trail::findOrFail($trailId);

        DB::transaction(function () use ($request, $trail) {
            foreach ($request->input('stages') as $position => $stageId) {
                TrailStage::where('trail_id', $trail->id)
                    ->where('id', $stageId)
                    ->update(['position' => $position + 1]);
            }
        });

        return response()->json($trail->load('stages.jobPlan'), 200);
    }
}
