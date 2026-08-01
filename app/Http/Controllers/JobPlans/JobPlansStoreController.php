<?php

namespace App\Http\Controllers\JobPlans;

use App\Http\Controllers\Controller;
use App\Models\JobPlans;
use Illuminate\Http\Request;

class JobPlansStoreController extends Controller
{
    //
    public function __construct(private JobPlans $jobPlans)
    {
    }

    public function __invoke(Request $request)
    {
        $jobPlans = $this->jobPlans->create($request->only([
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

        return response()->json($jobPlans, 201);
    }
}
