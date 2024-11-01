<?php

namespace App\Http\Controllers\Duties;

use App\Http\Controllers\Controller;
use App\Models\Duty;
use Illuminate\Http\Request;

class DutiesIndexController extends Controller
{
    public function __construct(private Duty $duty) {}

    //
    public function __invoke()
    {
        return response()->json($this->duty->with(['dutyable.image'])->latest()->get(), 200);
    }
}
