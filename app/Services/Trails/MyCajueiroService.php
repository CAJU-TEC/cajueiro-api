<?php

namespace App\Services\Trails;

use App\Models\Collaborator;
use Illuminate\Support\Facades\DB;

/**
 * Monta o "Meu Cajueiro": as conquistas do colaborador, na ordem em que caíram.
 *
 * O desenho e a exportação da imagem vivem no front (CajueiroPoster.vue): o
 * pôster que aparece na tela é o mesmo que vira PNG, então não há dois
 * desenhos para manter em sincronia.
 */
class MyCajueiroService
{
    public function payloadFor(Collaborator $collaborator): array
    {
        $badges = $this->badgesOf($collaborator);
        $totalStages = $this->totalStagesOf($collaborator);
        $harvested = $badges->count();

        $fruits = $badges->values()->map(function ($badge) {
            return [
                'color' => $badge->badge_color ?: '#F9A825',
                'icon' => $badge->badge_icon,
                'job_plan' => $badge->job_plan,
                'trail_stage' => $badge->trail_stage,
                'completed_at' => $badge->completed_at,
            ];
        })->all();

        return [
            'collaborator' => [
                'id' => $collaborator->id,
                'full_name' => $collaborator->full_name,
                'first_name' => $collaborator->first_name,
                'letter' => $collaborator->letter,
            ],
            'team' => $collaborator->team?->only(['id', 'name', 'color']),
            'job_plan' => $collaborator->jobplan?->only(['id', 'description', 'badge_icon', 'badge_color']),
            'fruits' => $fruits,
            'harvested' => $harvested,
            'total_stages' => $totalStages,
        ];
    }

    /**
     * Etapas conquistadas, com o badge do plano-alvo. Mesma consulta do
     * TrailBadgesController, ordenada cronologicamente.
     */
    public function badgesOf(Collaborator $collaborator)
    {
        return DB::table('trail_stage_collaborator')
            ->join('job_plans', 'job_plans.id', '=', 'trail_stage_collaborator.job_plan_id')
            ->join('trail_stages', 'trail_stages.id', '=', 'trail_stage_collaborator.trail_stage_id')
            ->where('trail_stage_collaborator.collaborator_id', $collaborator->id)
            ->whereNull('trail_stage_collaborator.deleted_at')
            ->whereNotNull('trail_stage_collaborator.completed_at')
            ->whereNull('trail_stages.deleted_at')
            ->orderBy('trail_stage_collaborator.completed_at')
            ->orderBy('trail_stages.position')
            ->get([
                'job_plans.description as job_plan',
                'job_plans.badge_icon',
                'job_plans.badge_color',
                'trail_stages.description as trail_stage',
                'trail_stage_collaborator.completed_at',
            ]);
    }

    /**
     * Total de etapas das trilhas em que o colaborador está matriculado.
     */
    private function totalStagesOf(Collaborator $collaborator): int
    {
        return DB::table('trail_stages')
            ->join('trail_collaborator', 'trail_collaborator.trail_id', '=', 'trail_stages.trail_id')
            ->where('trail_collaborator.collaborator_id', $collaborator->id)
            ->whereNull('trail_collaborator.deleted_at')
            ->whereNull('trail_stages.deleted_at')
            ->count();
    }
}
