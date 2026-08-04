<?php

namespace App\Http\Controllers\JobPlans;

use App\Http\Controllers\Controller;
use App\Models\JobPlans;
use Illuminate\Http\Request;

class JobPlansIndexController extends Controller
{
    //
    public function __construct(private JobPlans $jobPlans)
    {
    }

    //
    public function __invoke(Request $request)
    {
        $jobPlans = $this->jobPlans->with('team');

        if ($request->filled('team_id')) {
            $jobPlans->where('team_id', $request->input('team_id'));
        }

        return response()->json($jobPlans->latest()->get(), 200);
    }
}
