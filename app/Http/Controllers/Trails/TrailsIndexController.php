<?php

namespace App\Http\Controllers\Trails;

use App\Http\Controllers\Controller;
use App\Models\Trail;
use Illuminate\Http\Request;

class TrailsIndexController extends Controller
{
    public function __construct(private Trail $trails) {}

    public function __invoke(Request $request)
    {
        $trails = $this->trails->with('team')
            ->withCount(['stages', 'collaborators']);

        if ($request->filled('team_id')) {
            $trails->where('team_id', $request->input('team_id'));
        }

        if ($request->filled('active')) {
            $trails->where('active', $request->boolean('active'));
        }

        return response()->json($trails->latest()->get(), 200);
    }
}
