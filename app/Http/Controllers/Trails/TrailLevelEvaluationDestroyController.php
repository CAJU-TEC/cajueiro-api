<?php

namespace App\Http\Controllers\Trails;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Trail\TrailAdvanceRequest;
use App\Models\Collaborator;
use App\Models\TrailLevel;
use App\Services\Trails\TrailProgressService;
use DomainException;

class TrailLevelEvaluationDestroyController extends Controller
{
    public function __construct(private TrailProgressService $progress) {}

    public function __invoke(TrailAdvanceRequest $request, $levelId)
    {
        $level = TrailLevel::findOrFail($levelId);
        $collaborator = Collaborator::findOrFail($request->input('collaborator_id'));

        try {
            $stage = $this->progress->clearLevelEvaluation($level, $collaborator);
        } catch (DomainException $e) {
            return response()->json($e->getMessage(), 422);
        }

        return response()->json(
            $this->progress->progressFor($stage->trail, $collaborator->fresh()),
            200
        );
    }
}
