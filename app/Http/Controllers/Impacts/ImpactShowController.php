<?php

namespace App\Http\Controllers\Impacts;

use App\Http\Controllers\Controller;
use App\Models\Impact;

class ImpactShowController extends Controller
{
    //
    public function __construct(private Impact $impact)
    {
    }

    public function __invoke($id)
    {
        $impact = $this->impact->findOrFail($id);
        return response()->json($impact, 200);
    }
}
