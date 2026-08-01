<?php

namespace App\Http\Controllers\Trails;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Badges de todos os colaboradores em uma única consulta, agrupados por
 * collaborator_id. O front carrega uma vez e resolve o avatar de qualquer tela
 * sem gerar N+1 nas listagens.
 */
class TrailBadgesController extends Controller
{
    public function __invoke(Request $request)
    {
        $badges = DB::table('trail_stage_collaborator')
            ->join('job_plans', 'job_plans.id', '=', 'trail_stage_collaborator.job_plan_id')
            ->join('trail_stages', 'trail_stages.id', '=', 'trail_stage_collaborator.trail_stage_id')
            ->whereNull('trail_stage_collaborator.deleted_at')
            ->whereNotNull('trail_stage_collaborator.completed_at')
            ->whereNull('trail_stages.deleted_at')
            ->whereNotNull('job_plans.badge_icon')
            ->when($request->filled('collaborator_id'), function ($query) use ($request) {
                $query->where('trail_stage_collaborator.collaborator_id', $request->input('collaborator_id'));
            })
            // Cronológica: o último da lista é sempre a conquista mais recente,
            // inclusive quando o colaborador segue mais de uma trilha.
            ->orderBy('trail_stage_collaborator.completed_at')
            ->orderBy('trail_stages.position')
            ->get([
                'trail_stage_collaborator.collaborator_id',
                'trail_stage_collaborator.completed_at',
                'trail_stage_collaborator.certificate_code',
                'job_plans.id as job_plan_id',
                'job_plans.description as job_plan',
                'job_plans.badge_icon',
                'job_plans.badge_color',
                'trail_stages.id as trail_stage_id',
                'trail_stages.description as trail_stage',
            ])
            ->groupBy('collaborator_id');

        return response()->json($badges, 200);
    }
}
