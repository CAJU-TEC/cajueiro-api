<?php

namespace App\Http\Controllers\CorporateGroups;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\CorporateGroup\CorporateGroupRequest;
use App\Models\CorporateGroup;

class CorporateGroupStoreController extends Controller
{
    public function __invoke(CorporateGroupRequest $request)
    {
        return response()->json(CorporateGroup::create($request->only('nome')), 201);
    }
}
