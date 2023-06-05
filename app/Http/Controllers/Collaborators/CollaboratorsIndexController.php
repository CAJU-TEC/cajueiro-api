<?php

namespace App\Http\Controllers\Collaborators;

use App\Http\Controllers\Controller;
use App\Models\Collaborator;
use Illuminate\Http\Request;

class CollaboratorsIndexController extends Controller
{
    public function __construct(private Collaborator $collaborators)
    {
    }

    //
    public function __invoke()
    {
        return response()->json($this->collaborators->with(['image', 'email', 'user', 'jobplan'])->latest()->get(), 200);
    }
}
