<?php

namespace App\Http\Controllers\Teams;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Team\TeamStoreRequest;
use App\Models\Team;

class TeamsStoreController extends Controller
{
    public function __construct(private Team $teams) {}

    public function __invoke(TeamStoreRequest $request)
    {
        $team = $this->teams->create($request->only([
            'name',
            'description',
            'color',
        ]));

        return response()->json($team, 201);
    }
}
