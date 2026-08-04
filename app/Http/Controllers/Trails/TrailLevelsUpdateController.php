<?php

namespace App\Http\Controllers\Trails;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Trail\TrailLevelStoreRequest;
use App\Models\TrailLevel;

class TrailLevelsUpdateController extends Controller
{
    public function __construct(private TrailLevel $levels) {}

    public function __invoke(TrailLevelStoreRequest $request, $id)
    {
        $level = $this->levels->findOrFail($id);

        $level->update($request->only([
            'description',
            'note',
            'type',
            'skill',
            'position',
        ]));

        return response()->json($level->load('materials'), 200);
    }
}
