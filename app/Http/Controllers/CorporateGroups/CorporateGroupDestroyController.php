<?php

namespace App\Http\Controllers\CorporateGroups;

use App\Http\Controllers\Controller;
use App\Models\Corporate;
use App\Models\CorporateGroup;

class CorporateGroupDestroyController extends Controller
{
    public function __invoke($id)
    {
        $grupo = CorporateGroup::findOrFail($id);

        // as organizações do grupo não são apagadas, só ficam sem grupo
        Corporate::where('corporate_group_id', $grupo->id)->update(['corporate_group_id' => null]);
        $grupo->delete();

        return response()->json([], 204);
    }
}
