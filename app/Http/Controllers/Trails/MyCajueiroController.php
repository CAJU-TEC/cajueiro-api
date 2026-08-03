<?php

namespace App\Http\Controllers\Trails;

use App\Http\Controllers\Controller;
use App\Services\Trails\MyCajueiroService;
use Illuminate\Support\Facades\Auth;

class MyCajueiroController extends Controller
{
    public function __construct(private MyCajueiroService $cajueiro) {}

    public function __invoke()
    {
        $collaborator = Auth::user()?->collaborator;

        if (!$collaborator) {
            return response()->json('Usuário autenticado não possui colaborador vinculado.', 404);
        }

        $collaborator->load(['team', 'jobplan']);

        return response()->json($this->cajueiro->payloadFor($collaborator), 200);
    }
}
