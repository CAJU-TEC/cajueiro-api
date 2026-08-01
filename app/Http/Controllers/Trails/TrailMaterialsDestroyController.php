<?php

namespace App\Http\Controllers\Trails;

use App\Http\Controllers\Controller;
use App\Models\TrailMaterial;

class TrailMaterialsDestroyController extends Controller
{
    public function __construct(private TrailMaterial $materials) {}

    public function __invoke($id)
    {
        $this->materials->findOrFail($id)->delete();

        return response()->json([], 204);
    }
}
