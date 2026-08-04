<?php

namespace App\Http\Controllers\Trails;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Trail\TrailAdvanceRequest;
use App\Models\Collaborator;
use App\Models\TrailStage;
use App\Services\Trails\TrailProgressService;
use DomainException;

class TrailStageUndoController extends Controller
{
    public function __construct(private TrailProgressService $progress) {}

    public function __invoke(TrailAdvanceRequest $request, $stageId)
    {
        $stage = TrailStage::with('trail')->findOrFail($stageId);
        $collaborator = Collaborator::findOrFail($request->input('collaborator_id'));

        try {
            $this->progress->undoStage($stage, $collaborator);
        } catch (DomainException $e) {
            return response()->json($e->getMessage(), 422);
        }

        return response()->json(
            $this->progress->progressFor($stage->trail, $collaborator->fresh()),
            200
        );
    }
}
