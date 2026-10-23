<?php

namespace App\Http\Controllers\CorporateGroups;

use App\Http\Controllers\Controller;
use App\Models\CorporateGroup;

class CorporateGroupIndexController extends Controller
{
    public function __invoke()
    {
        return response()->json(CorporateGroup::withCount('corporates')->orderBy('nome')->get(), 200);
    }
}
