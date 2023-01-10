<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthRefreshTokenController extends Controller
{
    public function __invoke(Request $request)
    {
        //
        $user = $request->user();
        dd(auth());
        $user->tokens->delete();
        return response()->json([
            'token' => $user->createToken($user->name)->plainTextToken
        ]);
    }
}
