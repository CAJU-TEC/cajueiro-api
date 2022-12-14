<?php

namespace App\Http\Controllers\Corporates;

use App\Http\Controllers\Controller;
use App\Models\Corporate;
use Illuminate\Http\Request;

class CorporateDestroyController extends Controller
{
    //
    public function __construct(private Corporate $corporate)
    {
    }

    public function __invoke($id)
    {
        $corporate = $this->corporate->find($id);
        $corporate->delete();

        return response()->json([], 204);
    }
}
