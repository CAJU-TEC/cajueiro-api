<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AuthShowController extends Controller
{
    //
    public function __construct(private User $user)
    {
    }

    public function __invoke(Request $request, $id)
    {
        $client = $this->user
            ->with(['permissions', 'roles'])
            ->findOrFail($id);
        return response()->json($client, 200);
    }
}
