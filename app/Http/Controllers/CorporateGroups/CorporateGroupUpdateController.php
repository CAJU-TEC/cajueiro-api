<?php

namespace App\Http\Controllers\CorporateGroups;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\CorporateGroup\CorporateGroupRequest;
use App\Models\CorporateGroup;

class CorporateGroupUpdateController extends Controller
{
    public function __invoke(CorporateGroupRequest $request, $id)
    {
        $grupo = CorporateGroup::findOrFail($id);
        $grupo->update($request->only('nome'));

        return response()->json($grupo, 200);
    }
}
