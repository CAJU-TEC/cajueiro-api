<?php

namespace App\Http\Controllers\CheckLists;

use App\Http\Controllers\Controller;
use App\Models\CheckList;
use DomainException;
use Illuminate\Http\Request;

class CheckListsUpdateController extends Controller
{
    //
    public function __invoke(Request $request, $id)
    {
        try {
            $checkList = CheckList::find($id);
            $checkList->update($request->only([
                'description',
                'status',
                'started',
                'delivered',
            ]));

            return response()->json($checkList, 200);
        } catch (DomainException $e) {
            return response()->json($e->getMessage(), 422);
        }
    }
}
