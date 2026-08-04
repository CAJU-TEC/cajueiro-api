<?php

namespace App\Http\Controllers\Trails;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Trail\TrailMaterialStoreRequest;
use App\Models\TrailMaterial;

class TrailMaterialsUpdateController extends Controller
{
    public function __construct(private TrailMaterial $materials) {}

    public function __invoke(TrailMaterialStoreRequest $request, $id)
    {
        $material = $this->materials->findOrFail($id);

        $material->update($request->only([
            'description',
            'url',
            'type',
        ]));

        return response()->json($material, 200);
    }
}
