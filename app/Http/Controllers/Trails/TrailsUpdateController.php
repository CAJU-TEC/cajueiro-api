<?php

namespace App\Http\Controllers\Trails;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Trail\TrailStoreRequest;
use App\Models\Trail;

class TrailsUpdateController extends Controller
{
    public function __construct(private Trail $trails) {}

    public function __invoke(TrailStoreRequest $request, $id)
    {
        $trail = $this->trails->findOrFail($id);

        $trail->update($request->only([
            'team_id',
            'description',
            'note',
            'color',
            'active',
        ]));

        return response()->json($trail->load('team'), 200);
    }
}
