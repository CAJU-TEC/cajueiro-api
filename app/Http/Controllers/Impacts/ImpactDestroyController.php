<?php

namespace App\Http\Controllers\Impacts;

use App\Http\Controllers\Controller;
use App\Models\Impact;

class ImpactDestroyController extends Controller
{
    //
    public function __construct(private Impact $impact)
    {
    }

    public function __invoke($id)
    {
        $impact = $this->impact->find($id);
        $impact->delete();

        return response()->json([], 204);
    }
}
