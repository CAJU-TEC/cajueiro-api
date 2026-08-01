<?php

namespace App\Services\Trails;

use App\Models\Collaborator;
use App\Models\Trail;
use App\Models\TrailLevel;
use App\Models\TrailStage;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Regras de progressão da trilha de aprendizado.
 *
 * R1 - Ordem:     etapa só fecha se todas as de posição menor já estiverem concluídas.
 * R2 - Quórum:    etapa fecha ao atingir `required_count` níveis concluídos.
 * R3 - Promoção:  ao fechar a etapa o colaborador recebe o plano-alvo.
 * R4 - Time:      colaborador, trilha e plano-alvo precisam ser do mesmo time.
 * R5 - Badges:    derivados das etapas concluídas (ver Collaborator::getBadgesAttribute).
 * R6 - Permissão: quem concluiu é sempre registrado em `completed_by`.
 * R7 - Desfazer:  só é possível desfazer a última etapa concluída.
 */
class TrailProgressService
{
    public const STATE_COMPLETED = 'completed';
    public const STATE_UNLOCKED = 'unlocked';
    public const STATE_LOCKED = 'locked';

    /**
     * Matricula o colaborador na trilha (R4).
     */
    public function enroll(Trail $trail, Collaborator $collaborator): void
    {
        $this->assertSameTeam($trail, $collaborator);

        $enrollment = DB::table('trail_collaborator')
            ->where('trail_id', $trail->id)
            ->where('collaborator_id', $collaborator->id)
            ->first();

        if ($enrollment && is_null($enrollment->deleted_at)) {
            return;
        }

        if ($enrollment) {
            DB::table('trail_collaborator')
                ->where('trail_id', $trail->id)
                ->where('collaborator_id', $collaborator->id)
                ->update([
                    'deleted_at' => null,
                    'started_at' => now(),
                    'finished_at' => null,
                    'updated_at' => now(),
                ]);

            return;
        }

        DB::table('trail_collaborator')->insert([
            'trail_id' => $trail->id,
            'collaborator_id' => $collaborator->id,
            'started_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function unenroll(Trail $trail, Collaborator $collaborator): void
    {
        DB::table('trail_collaborator')
            ->where('trail_id', $trail->id)
            ->where('collaborator_id', $collaborator->id)
            ->whereNull('deleted_at')
            ->update(['deleted_at' => now()]);
    }

    /**
     * Marca um nível como concluído e reavalia a etapa (R1 + R2).
     */
    public function completeLevel(TrailLevel $level, Collaborator $collaborator, string $userId, ?string $note = null): TrailStage
    {
        $stage = $level->stage()->with('trail')->firstOrFail();

        $this->assertPreviousStagesCompleted($stage, $collaborator);

        $record = DB::table('trail_level_collaborator')
            ->where('trail_level_id', $level->id)
            ->where('collaborator_id', $collaborator->id)
            ->first();

        if ($record && is_null($record->deleted_at) && !is_null($record->completed_at)) {
            return $this->syncStageCompletion($stage, $collaborator, $userId);
        }

        $payload = [
            'completed_by' => $userId,
            'completed_at' => now(),
            'note' => $note,
            'deleted_at' => null,
            'updated_at' => now(),
        ];

        if ($record) {
            DB::table('trail_level_collaborator')
                ->where('trail_level_id', $level->id)
                ->where('collaborator_id', $collaborator->id)
                ->update($payload);
        } else {
            DB::table('trail_level_collaborator')->insert($payload + [
                'trail_level_id' => $level->id,
                'collaborator_id' => $collaborator->id,
                'created_at' => now(),
            ]);
        }

        return $this->syncStageCompletion($stage, $collaborator, $userId);
    }

    /**
     * Desfaz a conclusão de um nível e reavalia a etapa.
     */
    public function undoLevel(TrailLevel $level, Collaborator $collaborator, string $userId): TrailStage
    {
        $stage = $level->stage()->with('trail')->firstOrFail();

        $this->assertNoLaterStageCompleted($stage, $collaborator);

        DB::table('trail_level_collaborator')
            ->where('trail_level_id', $level->id)
            ->where('collaborator_id', $collaborator->id)
            ->whereNull('deleted_at')
            ->update(['deleted_at' => now()]);

        if ($this->completedLevelsCount($stage, $collaborator) < $stage->required_count) {
            $this->undoStage($stage, $collaborator);
        }

        return $stage->fresh();
    }

    /**
     * Fecha a etapa se o quórum de níveis foi atingido (R2) e promove o colaborador (R3).
     */
    public function syncStageCompletion(TrailStage $stage, Collaborator $collaborator, string $userId): TrailStage
    {
        if ($this->completedLevelsCount($stage, $collaborator) < $stage->required_count) {
            return $stage;
        }

        return $this->advanceStage($stage, $collaborator, $userId);
    }

    /**
     * Conclui a etapa manualmente, validando ordem e time (R1, R3, R4, R6).
     */
    public function advanceStage(TrailStage $stage, Collaborator $collaborator, string $userId, ?string $note = null): TrailStage
    {
        $stage->loadMissing('trail');

        $this->assertSameTeam($stage->trail, $collaborator);
        $this->assertPreviousStagesCompleted($stage, $collaborator);

        if ($this->isStageCompleted($stage, $collaborator)) {
            return $stage;
        }

        DB::transaction(function () use ($stage, $collaborator, $userId, $note) {
            $existing = DB::table('trail_stage_collaborator')
                ->where('trail_stage_id', $stage->id)
                ->where('collaborator_id', $collaborator->id)
                ->first();

            $payload = [
                'job_plan_id' => $stage->job_plan_id,
                'completed_by' => $userId,
                'completed_at' => now(),
                'certificate_code' => $existing->certificate_code ?? $this->generateCertificateCode(),
                'note' => $note,
                'deleted_at' => null,
                'updated_at' => now(),
            ];

            if ($existing) {
                DB::table('trail_stage_collaborator')
                    ->where('trail_stage_id', $stage->id)
                    ->where('collaborator_id', $collaborator->id)
                    ->update($payload);
            } else {
                DB::table('trail_stage_collaborator')->insert($payload + [
                    'trail_stage_id' => $stage->id,
                    'collaborator_id' => $collaborator->id,
                    'created_at' => now(),
                ]);
            }

            // R3 - promoção de plano
            if ($stage->job_plan_id) {
                $collaborator->update(['jobplan_id' => $stage->job_plan_id]);
            }

            $this->syncTrailCompletion($stage->trail, $collaborator);
        });

        return $stage->fresh();
    }

    /**
     * Desfaz a conclusão da etapa e reverte o plano do colaborador (R7).
     */
    public function undoStage(TrailStage $stage, Collaborator $collaborator): TrailStage
    {
        $stage->loadMissing('trail');

        $this->assertNoLaterStageCompleted($stage, $collaborator);

        DB::transaction(function () use ($stage, $collaborator) {
            DB::table('trail_stage_collaborator')
                ->where('trail_stage_id', $stage->id)
                ->where('collaborator_id', $collaborator->id)
                ->whereNull('deleted_at')
                ->update(['deleted_at' => now()]);

            $previous = $this->lastCompletedStage($stage->trail, $collaborator);
            $collaborator->update(['jobplan_id' => $previous?->job_plan_id]);

            DB::table('trail_collaborator')
                ->where('trail_id', $stage->trail_id)
                ->where('collaborator_id', $collaborator->id)
                ->whereNull('deleted_at')
                ->update(['finished_at' => null, 'updated_at' => now()]);
        });

        return $stage->fresh();
    }

    /**
     * Estado da trilha para um colaborador — consumido pelo front.
     */
    public function progressFor(Trail $trail, Collaborator $collaborator): array
    {
        $trail->loadMissing(['team', 'stages.levels.materials', 'stages.materials', 'stages.jobPlan']);

        $completedStageIds = $this->completedStageIds($trail, $collaborator);
        $completedLevelIds = $this->completedLevelIds($trail, $collaborator);

        $previousCompleted = true;
        $stages = [];

        foreach ($trail->stages as $stage) {
            $isCompleted = in_array($stage->id, $completedStageIds, true);

            if ($isCompleted) {
                $state = self::STATE_COMPLETED;
            } elseif ($previousCompleted) {
                $state = self::STATE_UNLOCKED;
            } else {
                $state = self::STATE_LOCKED;
            }

            $levels = $stage->levels->map(fn ($level) => [
                'id' => $level->id,
                'description' => $level->description,
                'note' => $level->note,
                'type' => $level->type,
                'position' => $level->position,
                'materials' => $level->materials,
                'completed' => in_array($level->id, $completedLevelIds, true),
            ]);

            $stages[] = [
                'id' => $stage->id,
                'description' => $stage->description,
                'note' => $stage->note,
                'position' => $stage->position,
                'required_count' => $stage->required_count,
                'levels_count' => $stage->levels->count(),
                'completed_levels_count' => $levels->where('completed', true)->count(),
                'job_plan' => $stage->jobPlan,
                'materials' => $stage->materials,
                'levels' => $levels,
                'state' => $state,
                'completion' => $this->stageCompletion($stage, $collaborator),
            ];

            $previousCompleted = $previousCompleted && $isCompleted;
        }

        return [
            'trail' => $trail->only(['id', 'team_id', 'description', 'note', 'color', 'active']),
            'team' => $trail->team,
            'collaborator' => [
                'id' => $collaborator->id,
                'full_name' => $collaborator->full_name,
                'letter' => $collaborator->letter,
                'jobplan_id' => $collaborator->jobplan_id,
                'badges' => $collaborator->badges,
            ],
            'stages' => $stages,
            'stages_count' => count($stages),
            'completed_stages_count' => count($completedStageIds),
        ];
    }

    public function isStageCompleted(TrailStage $stage, Collaborator $collaborator): bool
    {
        return !is_null($this->stageCompletion($stage, $collaborator));
    }

    public function stageCompletion(TrailStage $stage, Collaborator $collaborator): ?object
    {
        return DB::table('trail_stage_collaborator')
            ->where('trail_stage_id', $stage->id)
            ->where('collaborator_id', $collaborator->id)
            ->whereNull('deleted_at')
            ->whereNotNull('completed_at')
            ->first();
    }

    private function completedLevelsCount(TrailStage $stage, Collaborator $collaborator): int
    {
        return DB::table('trail_level_collaborator')
            ->join('trail_levels', 'trail_levels.id', '=', 'trail_level_collaborator.trail_level_id')
            ->where('trail_levels.trail_stage_id', $stage->id)
            ->whereNull('trail_levels.deleted_at')
            ->where('trail_level_collaborator.collaborator_id', $collaborator->id)
            ->whereNull('trail_level_collaborator.deleted_at')
            ->whereNotNull('trail_level_collaborator.completed_at')
            ->count();
    }

    /**
     * R1 - todas as etapas anteriores precisam estar concluídas.
     */
    private function assertPreviousStagesCompleted(TrailStage $stage, Collaborator $collaborator): void
    {
        $pending = TrailStage::where('trail_id', $stage->trail_id)
            ->where('position', '<', $stage->position)
            ->whereNotExists(function ($query) use ($collaborator) {
                $query->select(DB::raw(1))
                    ->from('trail_stage_collaborator')
                    ->whereColumn('trail_stage_collaborator.trail_stage_id', 'trail_stages.id')
                    ->where('trail_stage_collaborator.collaborator_id', $collaborator->id)
                    ->whereNull('trail_stage_collaborator.deleted_at')
                    ->whereNotNull('trail_stage_collaborator.completed_at');
            })
            ->exists();

        if ($pending) {
            throw new DomainException('Conclua as etapas anteriores antes de avançar.');
        }
    }

    /**
     * R7 - não desfazer uma etapa com etapas posteriores concluídas.
     */
    private function assertNoLaterStageCompleted(TrailStage $stage, Collaborator $collaborator): void
    {
        $later = TrailStage::where('trail_id', $stage->trail_id)
            ->where('position', '>', $stage->position)
            ->whereExists(function ($query) use ($collaborator) {
                $query->select(DB::raw(1))
                    ->from('trail_stage_collaborator')
                    ->whereColumn('trail_stage_collaborator.trail_stage_id', 'trail_stages.id')
                    ->where('trail_stage_collaborator.collaborator_id', $collaborator->id)
                    ->whereNull('trail_stage_collaborator.deleted_at')
                    ->whereNotNull('trail_stage_collaborator.completed_at');
            })
            ->exists();

        if ($later) {
            throw new DomainException('Desfaça as etapas posteriores primeiro.');
        }
    }

    /**
     * R4 - colaborador precisa ser do mesmo time da trilha.
     */
    private function assertSameTeam(Trail $trail, Collaborator $collaborator): void
    {
        if ($trail->team_id && $collaborator->team_id !== $trail->team_id) {
            throw new DomainException('O colaborador não pertence ao time da trilha.');
        }
    }

    private function syncTrailCompletion(Trail $trail, Collaborator $collaborator): void
    {
        $total = TrailStage::where('trail_id', $trail->id)->count();
        $completed = count($this->completedStageIds($trail, $collaborator));

        DB::table('trail_collaborator')
            ->where('trail_id', $trail->id)
            ->where('collaborator_id', $collaborator->id)
            ->whereNull('deleted_at')
            ->update([
                'finished_at' => ($total > 0 && $completed >= $total) ? now() : null,
                'updated_at' => now(),
            ]);
    }

    private function lastCompletedStage(Trail $trail, Collaborator $collaborator): ?TrailStage
    {
        return TrailStage::where('trail_id', $trail->id)
            ->whereIn('id', $this->completedStageIds($trail, $collaborator))
            ->orderByDesc('position')
            ->first();
    }

    private function completedStageIds(Trail $trail, Collaborator $collaborator): array
    {
        return DB::table('trail_stage_collaborator')
            ->join('trail_stages', 'trail_stages.id', '=', 'trail_stage_collaborator.trail_stage_id')
            ->where('trail_stages.trail_id', $trail->id)
            ->whereNull('trail_stages.deleted_at')
            ->where('trail_stage_collaborator.collaborator_id', $collaborator->id)
            ->whereNull('trail_stage_collaborator.deleted_at')
            ->whereNotNull('trail_stage_collaborator.completed_at')
            ->pluck('trail_stages.id')
            ->toArray();
    }

    private function completedLevelIds(Trail $trail, Collaborator $collaborator): array
    {
        return DB::table('trail_level_collaborator')
            ->join('trail_levels', 'trail_levels.id', '=', 'trail_level_collaborator.trail_level_id')
            ->join('trail_stages', 'trail_stages.id', '=', 'trail_levels.trail_stage_id')
            ->where('trail_stages.trail_id', $trail->id)
            ->whereNull('trail_levels.deleted_at')
            ->where('trail_level_collaborator.collaborator_id', $collaborator->id)
            ->whereNull('trail_level_collaborator.deleted_at')
            ->whereNotNull('trail_level_collaborator.completed_at')
            ->pluck('trail_levels.id')
            ->toArray();
    }

    private function generateCertificateCode(): string
    {
        return strtoupper(Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4));
    }
}
