<?php

namespace App\Http\Controllers\Trails;

use App\Http\Controllers\Controller;
use App\Models\Collaborator;
use App\Models\Trail;
use App\Services\Trails\TrailProgressService;

class TrailCollaboratorsDestroyController extends Controller
{
    public function __construct(private TrailProgressService $progress) {}

    public function __invoke($trailId, $collaboratorId)
    {
        $trail = Trail::findOrFail($trailId);
        $collaborator = Collaborator::findOrFail($collaboratorId);

        $this->progress->unenroll($trail, $collaborator);

        return response()->json([], 204);
    }
}
