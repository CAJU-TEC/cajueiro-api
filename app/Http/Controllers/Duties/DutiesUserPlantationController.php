<?php

namespace App\Http\Controllers\Duties;

use App\Http\Controllers\Controller;
use App\Models\Duty;
use Illuminate\Http\JsonResponse;

class DutiesUserPlantationController extends Controller
{
    public function __construct(private Duty $duty) {}

    public function __invoke()
    {

        $dutyable = $this->duty
            ->with(['dutyable.image'])
            ->latest()
            ->first();

        return response()->json($dutyable, 200);
    }
}
