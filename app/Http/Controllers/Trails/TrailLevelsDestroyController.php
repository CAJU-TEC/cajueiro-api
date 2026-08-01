<?php

namespace App\Http\Controllers\Trails;

use App\Http\Controllers\Controller;
use App\Models\TrailLevel;

class TrailLevelsDestroyController extends Controller
{
    public function __construct(private TrailLevel $levels) {}

    public function __invoke($id)
    {
        $this->levels->findOrFail($id)->delete();

        return response()->json([], 204);
    }
}
