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

        // $collaboratorsWithTimestamps = $this->convertArrayWithTimestamps($request->input('collaborators'));
        // $ticketsWithTimestamps = $this->convertArrayWithTimestamps($request->input('tickets'));

        // $checkLists->collaborators()->sync($collaboratorsWithTimestamps);
        // $checkLists->tickets()->sync($ticketsWithTimestamps);

        return response()->json($checkLists, 201);
    }

    private function convertArrayWithTimestamps($array)
    {
        return collect($array)->mapWithKeys(function ($data) {
            return [
                $data => [
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];
        })->toArray();
    }
}
