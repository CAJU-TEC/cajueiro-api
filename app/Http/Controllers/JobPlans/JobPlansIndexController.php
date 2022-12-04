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
    public function __invoke()
    {
        return response()->json($this->jobPlans->latest()->get(), 200);
    }
}
