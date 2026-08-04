<?php

namespace App\Http\Controllers\JobPlans;

use App\Http\Controllers\Controller;
use App\Models\JobPlans;
use DomainException;
use Illuminate\Http\Request;

class JobPlansUpdateController extends Controller
{
    //
    public function __invoke(Request $request, $id)
    {
        try {
            $jobPlans = JobPlans::find($id);
            $jobPlans->update($request->only([
                'team_id',
                'description',
                'color',
                'value',
                'time',
                'note',
                'badge_icon',
                'badge_color',
                'position',
            ]));

            return response()->json($jobPlans, 200);
        } catch (DomainException $e) {
            return response()->json($e->getMessage(), 422);
        }
    }
}
