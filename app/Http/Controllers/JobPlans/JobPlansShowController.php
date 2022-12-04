<?php

namespace App\Http\Controllers\JobPlans;

use App\Http\Controllers\Controller;
use App\Models\JobPlans;
use Illuminate\Http\Request;

class JobPlansShowController extends Controller
{
    //
    public function __construct(private JobPlans $jobPlans)
    {
    }

    public function __invoke($id)
    {
        $jobPlans = $this->jobPlans->findOrFail($id);
        return response()->json($jobPlans, 200);
    }
}
