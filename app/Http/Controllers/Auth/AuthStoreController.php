<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthStoreController extends Controller
{
    public function __construct(private User $user)
    {
    }
    //
    public function __invoke(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => 'required'
            ]);

            $user = $this->user->create($request->only([
                'name',
                'email',
                'password',
            ]));

            return response()->json([
                $user,
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                $th->getMessage()
            ], 500);
        }
    }
}
