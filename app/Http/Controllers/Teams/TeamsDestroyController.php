<?php

namespace App\Http\Controllers\Teams;

use App\Http\Controllers\Controller;
use App\Models\Team;

class TeamsDestroyController extends Controller
{
    public function __construct(private Team $teams) {}

    public function __invoke($id)
    {
        $team = $this->teams->withCount(['collaborators', 'trails'])->findOrFail($id);

        if ($team->collaborators_count > 0 || $team->trails_count > 0) {
            return response()->json('Time possui trilhas ou colaboradores vinculados.', 422);
        }

        $team->delete();

        return response()->json([], 204);
    }
}
