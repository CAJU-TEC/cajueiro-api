<?php

namespace App\Http\Controllers\CheckLists;

use App\Http\Controllers\Controller;
use App\Models\CheckList;
use Illuminate\Http\Request;

class CheckListsDestroyController extends Controller
{
    //
    public function __construct(private CheckList $checklist) {}

    public function __invoke($id)
    {
        $checklist = $this->checklist->find($id);
        $checklist->delete();

        return response()->json([], 204);
    }
}
