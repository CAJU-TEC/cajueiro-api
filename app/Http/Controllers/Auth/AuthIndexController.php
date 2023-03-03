<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AuthIndexController extends Controller
{
    //
    public function __construct(private User $users)
    {
    }

    //
    public function __invoke()
    {
        return response()->json($this->users->with(['collaborator', 'roles', 'tickets'])->latest()->get(), 200);
    }
}
