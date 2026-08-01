<?php

namespace App\Http\Controllers\Trails;

use App\Http\Controllers\Controller;
use App\Services\Trails\TrailProgressService;
use Illuminate\Support\Facades\Auth;

class TrailMineController extends Controller
{
    public function __construct(private TrailProgressService $progress) {}

    public function __invoke()
    {
        $collaborator = Auth::user()?->collaborator;

        if (!$collaborator) {
            return response()->json('Usuário autenticado não possui colaborador vinculado.', 404);
        }

        $trails = $collaborator->trails()->get();

        return response()->json(
            $trails->map(fn ($trail) => $this->progress->progressFor($trail, $collaborator)),
            200
        );
    }
}
