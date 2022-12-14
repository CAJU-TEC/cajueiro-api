<?php

namespace App\Http\Controllers\Corporates;

use App\Http\Controllers\Controller;
use App\Models\Corporate;

class CorporateIndexController extends Controller
{
    public function __construct(private Corporate $corporate)
    {
    }

    //
    public function __invoke()
    {
        return response()->json($this->corporate->with(['image', 'email'])->latest()->get(), 200);
    }
}
