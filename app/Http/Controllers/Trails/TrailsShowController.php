<?php

namespace App\Http\Controllers\Trails;

use App\Http\Controllers\Controller;
use App\Models\Trail;

class TrailsShowController extends Controller
{
    public function __construct(private Trail $trails) {}

    public function __invoke($id)
    {
        return response()->json(
            $this->trails
                ->with([
                    'team',
                    'stages.jobPlan',
                    'stages.materials',
                    'stages.levels.materials',
                    'collaborators',
                ])
                ->findOrFail($id),
            200
        );
    }
}
