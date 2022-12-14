<?php

namespace App\Http\Controllers\Corporates;

use App\Http\Controllers\Controller;
use App\Models\Corporate;

class CorporateShowController extends Controller
{
    //
    public function __construct(private Corporate $corporate)
    {
    }

    public function __invoke($id)
    {
        $corporate = $this->corporate->with(['email'])->findOrFail($id);
        return response()->json($corporate, 200);
    }
}
