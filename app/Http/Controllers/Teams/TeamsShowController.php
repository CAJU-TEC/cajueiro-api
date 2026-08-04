<?php

namespace App\Http\Controllers\Teams;

use App\Http\Controllers\Controller;
use App\Models\Team;

class TeamsShowController extends Controller
{
    public function __construct(private Team $teams) {}

    public function __invoke($id)
    {
        return response()->json(
            $this->teams
                ->with([
                    'collaborators:id,team_id,jobplan_id,first_name,last_name',
                    'jobPlans',
                    'trails',
                ])
                ->findOrFail($id),
            200
        );
    }
}
