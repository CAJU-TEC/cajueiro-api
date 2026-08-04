<?php

namespace App\Http\Controllers\Trails;

use App\Http\Controllers\Controller;
use App\Models\Collaborator;
use App\Models\Trail;
use App\Services\Trails\TrailProgressService;

class TrailProgressController extends Controller
{
    public function __construct(private TrailProgressService $progress) {}

    public function __invoke($trailId, $collaboratorId)
    {
        $trail = Trail::findOrFail($trailId);
        $collaborator = Collaborator::findOrFail($collaboratorId);

        return response()->json($this->progress->progressFor($trail, $collaborator), 200);
    }
}
