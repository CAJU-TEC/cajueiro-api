<?php

namespace App\Http\Controllers\Teams;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Team\TeamStoreRequest;
use App\Models\Team;

class TeamsUpdateController extends Controller
{
    public function __construct(private Team $teams) {}

    public function __invoke(TeamStoreRequest $request, $id)
    {
        $team = $this->teams->findOrFail($id);

        $team->update($request->only([
            'name',
            'description',
            'color',
        ]));

        return response()->json($team, 200);
    }
}
