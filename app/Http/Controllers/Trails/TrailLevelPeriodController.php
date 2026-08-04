<?php

namespace App\Http\Controllers\Trails;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Trail\TrailLevelPeriodRequest;
use App\Models\Collaborator;
use App\Models\TrailLevel;
use App\Services\Trails\TrailProgressService;
use DomainException;

class TrailLevelPeriodController extends Controller
{
    public function __construct(private TrailProgressService $progress) {}

    public function __invoke(TrailLevelPeriodRequest $request, $levelId)
    {
        $level = TrailLevel::findOrFail($levelId);
        $collaborator = Collaborator::findOrFail($request->input('collaborator_id'));

        try {
            $stage = $this->progress->setLevelPeriod(
                $level,
                $collaborator,
                $request->input('starts_at'),
                $request->input('ends_at')
            );
        } catch (DomainException $e) {
            return response()->json($e->getMessage(), 422);
        }

        return response()->json(
            $this->progress->progressFor($stage->trail, $collaborator),
            200
        );
    }
}
