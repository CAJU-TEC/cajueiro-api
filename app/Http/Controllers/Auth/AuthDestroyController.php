<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use DomainException;
use Illuminate\Http\Request;

class AuthDestroyController extends Controller
{
    private User $user;

    public function __invoke(Request $request, $id)
    {
        try {
            $this->user = User::find($id);
            if (!$this->user) {
                return response()->json(['message' => 'Usuário não encontrado'], 404);
            }
            $this->user->delete();
            return response()->json(['message' => 'Usuário deletado com sucesso'], 200);
        } catch (DomainException $e) {
            return response()->json($e->getMessage(), 422);
        }
    }
}
