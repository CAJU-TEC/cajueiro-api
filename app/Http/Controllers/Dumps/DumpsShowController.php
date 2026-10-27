<?php

namespace App\Http\Controllers\Dumps;

use App\Http\Controllers\Controller;
use App\Models\Dump;

class DumpsShowController extends Controller
{
    public function __construct(private Dump $dumps) {}

    public function __invoke($id)
    {
        return response()->json($this->dumps->with(['corporate:id,first_name,last_name', 'user:id,name'])->findOrFail($id), 200);
    }
}
