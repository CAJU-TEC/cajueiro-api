<?php

namespace App\Http\Controllers\JobPlans;

use App\Http\Controllers\Controller;
use App\Models\JobPlans;
use Illuminate\Http\Request;

class JobPlansDestroyController extends Controller
{
    //
    public function __construct(private JobPlans $jobPlans)
    {
    }

    public function __invoke($id)
    {
        $jobPlans = $this->jobPlans->find($id);
        $jobPlans->delete();

        return response()->json([], 204);
    }
}
