<?php

namespace App\Http\Controllers\Teams;

use App\Http\Controllers\Controller;
use App\Models\Team;

class TeamsIndexController extends Controller
{
    public function __construct(private Team $teams) {}

    public function __invoke()
    {
        return response()->json(
            $this->teams
                ->withCount([
                    'collaborators',
                    'jobPlans',
                    'trails',
                ])
                ->latest()
                ->get(),
            200
        );
    }
}
