<?php

namespace App\Http\Controllers\Trails;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Trail\TrailLevelStoreRequest;
use App\Models\TrailLevel;
use App\Models\TrailStage;

class TrailLevelsStoreController extends Controller
{
    public function __construct(private TrailLevel $levels) {}

    public function __invoke(TrailLevelStoreRequest $request, $stageId)
    {
        $stage = TrailStage::findOrFail($stageId);

        $level = $this->levels->create([
            'trail_stage_id' => $stage->id,
            'description' => $request->input('description'),
            'note' => $request->input('note'),
            'type' => $request->input('type', 'task'),
            // Padrao hard: nivel tecnico e o caso comum, soft e a excecao.
            'skill' => $request->input('skill', 'hard'),
            'position' => $request->input('position', $this->nextPosition($stage)),
        ]);

        return response()->json($level->load('materials'), 201);
    }

    private function nextPosition(TrailStage $stage): int
    {
        return (int) TrailLevel::where('trail_stage_id', $stage->id)->max('position') + 1;
    }
}
