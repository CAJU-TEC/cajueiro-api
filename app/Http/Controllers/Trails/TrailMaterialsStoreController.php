<?php

namespace App\Http\Controllers\Trails;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Trail\TrailMaterialStoreRequest;
use App\Models\TrailLevel;
use App\Models\TrailMaterial;
use App\Models\TrailStage;

class TrailMaterialsStoreController extends Controller
{
    public function __construct(private TrailMaterial $materials) {}

    public function __invoke(TrailMaterialStoreRequest $request)
    {
        $material = $this->materials->create([
            'description' => $request->input('description'),
            'url' => $request->input('url'),
            'type' => $request->input('type', 'other'),
            'materialable_id' => $request->input('materialable_id'),
            'materialable_type' => $request->input('materialable_type') === 'stage'
                ? TrailStage::class
                : TrailLevel::class,
        ]);

        return response()->json($material, 201);
    }
}
