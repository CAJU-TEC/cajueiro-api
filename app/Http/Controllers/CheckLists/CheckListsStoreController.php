<?php

namespace App\Http\Controllers\CheckLists;

use App\Http\Controllers\Controller;
use App\Models\CheckList;
use Illuminate\Http\Request;

class CheckListsStoreController extends Controller
{
    //
    public function __construct(private CheckList $checkLists) {}

    public function __invoke(Request $request)
    {
        $checkLists = $this->checkLists->create($request->only([
            'description',
            'status',
            'started',
            'delivered',
        ]));

        return response()->json($checkLists, 201);
    }
}
