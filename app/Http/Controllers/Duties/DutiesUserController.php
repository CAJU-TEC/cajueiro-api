<?php

namespace App\Http\Controllers\Duties;

use App\Http\Controllers\Controller;
use App\Models\Duty;
use Illuminate\Http\JsonResponse;

class DutiesUserController extends Controller
{
    public function __construct(private Duty $duty) {}

    public function __invoke(string $userId): JsonResponse
    {
        // Busca o último duty que pertence ao usuário informado
        $dutyable = $this->duty
            ->with(['dutyable.image'])
            ->whereHas('dutyable', fn($q) => $q->where('user_id', $userId))
            ->latest()
            ->first();

        if (!$dutyable) {
            return response()->json([
                'message' => 'Nenhum duty encontrado para este usuário.',
            ], 404);
        }

        return response()->json($dutyable, 200);
    }
}
