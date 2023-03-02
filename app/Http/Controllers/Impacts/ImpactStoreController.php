<?php

namespace App\Http\Controllers\Impacts;

use App\Http\Controllers\Controller;
use App\Models\Impact;
use Illuminate\Http\Request;

class ImpactStoreController extends Controller
{
    public function __construct(private Impact $impact)
    {
    }

    public function __invoke(Request $request)
    {
        $impact = $this->impact->create($request->only([
            'description',
            'color',
            'points',
            'days',
            'classification',
            'example',
        ]));

        return response()->json($impact, 201);
    }
}
