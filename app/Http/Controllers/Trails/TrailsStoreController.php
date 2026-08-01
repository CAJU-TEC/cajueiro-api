<?php

namespace App\Http\Controllers\Trails;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Trail\TrailStoreRequest;
use App\Models\Trail;

class TrailsStoreController extends Controller
{
    public function __construct(private Trail $trails) {}

    public function __invoke(TrailStoreRequest $request)
    {
        $trail = $this->trails->create($request->only([
            'team_id',
            'description',
            'note',
            'color',
            'active',
        ]));

        return response()->json($trail->load('team'), 201);
    }
}
