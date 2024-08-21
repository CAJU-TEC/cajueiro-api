<?php

namespace App\Http\Controllers\CheckLists;

use App\Http\Controllers\Controller;
use App\Models\CheckList;

class CheckListsIndexController extends Controller
{
    //
    public function __construct(private CheckList $checklist) {}

    //
    public function __invoke()
    {
        return response()->json(
            $this->checklist
                ->with([
                    'collaborators',
                    'tickets'
                ])
                ->withCount([
                    'collaborators',
                    'tickets'
                ])
                ->latest()
                ->get(),
            200
        );
    }
}
