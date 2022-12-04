<?php

namespace App\Http\Controllers\Impacts;

use App\Http\Controllers\Controller;
use App\Models\Impact;

class ImpactIndexController extends Controller
{
    public function __construct(private Impact $impact)
    {
    }

    //
    public function __invoke()
    {
        return response()->json($this->impact->latest()->get(), 200);
    }
}
