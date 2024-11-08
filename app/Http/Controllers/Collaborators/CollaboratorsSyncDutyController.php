<?php

namespace App\Http\Controllers\Collaborators;

use App\Http\Controllers\Controller;
use App\Models\Collaborator;
use Illuminate\Http\Request;

class CollaboratorsSyncDutyController extends Controller
{
    public function __construct(private Collaborator $collaborator) {}

    public function __invoke(Request $request)
    {
        try {
            $collaborator = $this->collaborator->find($request->collaborator['id']);
            $collaborator->duty()->create([]);
            return response()->json($collaborator, 201);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }
}
