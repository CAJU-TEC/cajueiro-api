<?php

namespace App\Http\Controllers\Impacts;

use App\Http\Controllers\Controller;
use App\Models\Impact;
use DomainException;
use Illuminate\Http\Request;

class ImpactUpdateController extends Controller
{
    public function __invoke(Request $request, $id)
    {
        try {
            $impact = Impact::find($id);
            $impact->update($request->only([
                'description',
                'color',
                'points',
                'classification',
                'example',
            ]));

            return response()->json($impact, 200);
        } catch (DomainException $e) {
            return response()->json($e->getMessage(), 422);
        }
    }
}
