<?php

namespace App\Http\Controllers\Trails;

use App\Http\Controllers\Controller;
use App\Models\TrailStage;

class TrailStagesDestroyController extends Controller
{
    public function __construct(private TrailStage $stages) {}

    public function __invoke($id)
    {
        $this->stages->findOrFail($id)->delete();

        return response()->json([], 204);
    }
}
