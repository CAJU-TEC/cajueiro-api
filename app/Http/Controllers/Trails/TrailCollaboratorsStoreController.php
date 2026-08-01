<?php

namespace App\Http\Controllers\Trails;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Trail\TrailAdvanceRequest;
use App\Models\Collaborator;
use App\Models\Trail;
use App\Services\Trails\TrailProgressService;
use DomainException;

class TrailCollaboratorsStoreController extends Controller
{
    public function __construct(private TrailProgressService $progress) {}

    public function __invoke(TrailAdvanceRequest $request, $trailId)
    {
        $trail = Trail::findOrFail($trailId);
        $collaborator = Collaborator::findOrFail($request->input('collaborator_id'));

        try {
            $this->progress->enroll($trail, $collaborator);
        } catch (DomainException $e) {
            return response()->json($e->getMessage(), 422);
        }

        return response()->json($trail->load('collaborators'), 201);
    }
}
