<?php

namespace App\Http\Controllers\Collaborators;

use App\Http\Controllers\Controller;
use App\Models\Collaborator;

class CollaboratorsShowController extends Controller
{
    //
    public function __construct(private Collaborator $collaborator)
    {
    }

    public function __invoke($id)
    {
        $collaborator = $this->collaborator->findOrFail($id);
        return response()->json($collaborator, 200);
    }
}
