<?php

namespace App\Http\Controllers\Trails;

use App\Http\Controllers\Controller;
use App\Models\Trail;

class TrailsDestroyController extends Controller
{
    public function __construct(private Trail $trails) {}

    public function __invoke($id)
    {
        $this->trails->findOrFail($id)->delete();

        return response()->json([], 204);
    }
}
