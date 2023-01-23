<?php

namespace App\Http\Controllers\Collaborators;

use App\Http\Controllers\Controller;
use App\Models\Collaborator;
use Illuminate\Http\Request;

class CollaboratorsDestroyController extends Controller
{
    //
    public function __construct(private Collaborator $collaborator)
    {
    }

    public function __invoke($id)
    {
        $collaborator = $this->collaborator->with(['image', 'email'])->find($id);
        $collaborator->delete();

        return response()->json([], 204);
    }
}
