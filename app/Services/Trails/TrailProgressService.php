<?php

namespace App\Services\Trails;

use App\Models\Collaborator;
use App\Models\Trail;
use App\Models\TrailLevel;
use App\Models\TrailStage;
use DomainException;
use Illuminate\Support\Carbon;
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
 * R7 - Desfazer:  só é possível desfazer a última etapa concluída, e o cargo
 *                 volta a ser o que era antes dela (`previous_job_plan_id`).
 * R8 - Prazo:     cada nível tem início e fim por matrícula; sem datas fica
 *                 "não iniciado" e estourar o fim só marca atrasado.
 * R9 - Avaliação: ao concluir o nível o líder dá nota de 0 a 100 e uma
 *                 resposta. Abaixo do `cut_score` o nível fica reprovado, mas
 *                 continua contando para o quórum: a nota se reflete na
 *                 porcentagem de avaliação da etapa, não na de conclusão.
 * R10 - Envio:    o colaborador envia o nível (podendo anexar certificado) e
 *                 ele fica aguardando avaliação. Enviar não conclui.
 */
class TrailProgressService
{
    public const STATE_COMPLETED = 'completed';
    public const STATE_UNLOCKED = 'unlocked';
    public const STATE_LOCKED = 'locked';

    // Onde o nível está no fluxo de envio e avaliação (R9, R10).
    public const LEVEL_PENDING = 'pending';
    public const LEVEL_SUBMITTED = 'submitted';
    public const LEVEL_COMPLETED = 'completed';

    // Estado do prazo do nível (R8).
    public const PERIOD_NOT_STARTED = 'not_started';
    public const PERIOD_SCHEDULED = 'scheduled';
    public const PERIOD_RUNNING = 'running';
    public const PERIOD_LATE = 'late';
    public const PERIOD_DONE = 'done';

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
    public function completeLevel(
        TrailLevel $level,
        Collaborator $collaborator,
        string $userId,
        ?string $note = null,
        ?int $score = null
    ): TrailStage {
        $stage = $level->stage()->with('trail')->firstOrFail();

        $this->assertPreviousStagesCompleted($stage, $collaborator);

        if (!is_null($score) && ($score < 0 || $score > 100)) {
            throw new DomainException('A nota precisa estar entre 0 e 100.');
        }

        $record = DB::table('trail_level_collaborator')
            ->where('trail_level_id', $level->id)
            ->where('collaborator_id', $collaborator->id)
            ->first();

        // Reavaliar um nível já concluído é caso legítimo: o líder errou a nota
        // ou o colaborador reenviou. Sem isso o método saía cedo e a nota nova
        // era ignorada em silêncio.
        $reavaliando = $record && is_null($record->deleted_at) && !is_null($record->completed_at);

        if ($reavaliando && is_null($score) && is_null($note)) {
            return $this->syncStageCompletion($stage, $collaborator, $userId);
        }

        $payload = [
            'completed_by' => $userId,
            'completed_at' => $reavaliando ? $record->completed_at : now(),
            'note' => $note,
            'score' => $score,
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
     * Define (ou limpa) o prazo do nível para um colaborador.
     *
     * Passar as duas datas nulas devolve o nível para "não iniciado" — é assim
     * que o sublíder desfaz um prazo colocado errado.
     *
     * Prazo estourado não bloqueia nada: só marca o nível como atrasado.
     */
    public function setLevelPeriod(
        TrailLevel $level,
        Collaborator $collaborator,
        ?string $startsAt,
        ?string $endsAt
    ): TrailStage {
        $stage = $level->stage()->with('trail')->firstOrFail();

        $this->assertSameTeam($stage->trail, $collaborator);
        $this->assertEnrolled($stage->trail, $collaborator);

        if ((bool) $startsAt !== (bool) $endsAt) {
            throw new DomainException('Informe as duas datas do período, ou nenhuma.');
        }

        // Normalizado para Y-m-d na entrada: o estado do prazo é decidido
        // comparando string de data, e o cliente pode mandar ISO completo. O
        // MySQL truncaria numa coluna date, mas o SQLite dos testes guardaria
        // o horário e a comparação passaria a mentir.
        $startsAt = $startsAt ? Carbon::parse($startsAt)->toDateString() : null;
        $endsAt = $endsAt ? Carbon::parse($endsAt)->toDateString() : null;

        if ($startsAt && $endsAt && $endsAt < $startsAt) {
            throw new DomainException('A data de fim não pode ser anterior à de início.');
        }

        $existing = $this->levelRecord($level, $collaborator);
        $payload = [
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'updated_at' => now(),
        ];

        if ($existing) {
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

        return $stage->fresh();
    }

    /**
     * O colaborador envia o nível para avaliação (R10).
     *
     * O envio não conclui nada: marca `submitted_at` e o nível fica aguardando
     * a nota do líder. É o que permite ao colaborador anexar o certificado do
     * curso sem ter poder de fechar o próprio nível.
     */
    public function submitLevel(
        TrailLevel $level,
        Collaborator $collaborator,
        string $userId,
        ?string $certificateUri = null
    ): TrailStage {
        $stage = $level->stage()->with('trail')->firstOrFail();

        $this->assertEnrolled($stage->trail, $collaborator);
        $this->assertPreviousStagesCompleted($stage, $collaborator);

        $record = $this->levelRecord($level, $collaborator);

        if ($record && $record->completed_at) {
            throw new DomainException('Este nível já foi concluído.');
        }

        $payload = [
            'submitted_at' => now(),
            'submitted_by' => $userId,
            'updated_at' => now(),
        ];

        // Sem certificado novo o anterior é preservado: reenviar por ter
        // esquecido de escrever algo não pode apagar o arquivo já anexado.
        if ($certificateUri) {
            $payload['certificate_uri'] = $certificateUri;
        }

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

        return $stage->fresh();
    }

    /**
     * Desfaz a conclusão de um nível e reavalia a etapa.
     */
    public function undoLevel(TrailLevel $level, Collaborator $collaborator, string $userId): TrailStage
    {
        $stage = $level->stage()->with('trail')->firstOrFail();

        $this->assertNoLaterStageCompleted($stage, $collaborator);

        // Limpa a conclusão mas mantém a linha: ela carrega o prazo do nível
        // (starts_at/ends_at), que não tem nada a ver com ter concluído ou não.
        // Apagar a linha, como era antes, jogava o prazo fora junto.
        DB::table('trail_level_collaborator')
            ->where('trail_level_id', $level->id)
            ->where('collaborator_id', $collaborator->id)
            ->whereNull('deleted_at')
            ->update([
                'completed_at' => null,
                'completed_by' => null,
                'note' => null,
                'score' => null,
                'updated_at' => now(),
            ]);

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
        $this->assertQuorumReached($stage, $collaborator);

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
                // Cargo de antes da promoção, para o desfazer ter o que restaurar.
                // Só faz sentido quando a etapa promove: etapa sem plano não
                // mexe no cargo, então não tem nada a devolver.
                'previous_job_plan_id' => $stage->job_plan_id ? $collaborator->jobplan_id : null,
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
            // Lido antes do soft delete: é ele que sabe qual cargo esta etapa
            // concedeu e qual o colaborador tinha antes.
            $completion = $this->stageCompletion($stage, $collaborator);

            DB::table('trail_stage_collaborator')
                ->where('trail_stage_id', $stage->id)
                ->where('collaborator_id', $collaborator->id)
                ->whereNull('deleted_at')
                ->update(['deleted_at' => now()]);

            $this->revertJobPlan($collaborator, $completion);

            DB::table('trail_collaborator')
                ->where('trail_id', $stage->trail_id)
                ->where('collaborator_id', $collaborator->id)
                ->whereNull('deleted_at')
                ->update(['finished_at' => null, 'updated_at' => now()]);
        });

        return $stage->fresh();
    }

    /**
     * Devolve ao colaborador o cargo que ele tinha antes desta etapa (R7).
     *
     * Duas guardas, e as duas existem por bug de produção:
     *
     * - Etapa que não concede cargo não pode mexer no cargo. O avanço só
     *   promove quando `job_plan_id` está preenchido; o desfazer tem que ser
     *   simétrico, senão desfazer uma etapa qualquer zerava o cargo.
     * - Se o cargo atual não é mais o que esta etapa concedeu, alguém mudou
     *   depois: outra trilha promoveu, ou o RH editou o colaborador na mão.
     *   Restaurar aqui apagaria essa mudança mais recente.
     */
    private function revertJobPlan(Collaborator $collaborator, ?object $completion): void
    {
        if (!$completion || !$completion->job_plan_id) {
            return;
        }

        if ((string) $collaborator->jobplan_id !== (string) $completion->job_plan_id) {
            return;
        }

        // Pode ser null de verdade: quem não tinha cargo antes volta a não ter.
        $collaborator->update(['jobplan_id' => $completion->previous_job_plan_id]);
    }

    /**
     * Estado da trilha para um colaborador — consumido pelo front.
     */
    public function progressFor(Trail $trail, Collaborator $collaborator): array
    {
        $trail->loadMissing(['team', 'stages.levels.materials', 'stages.materials', 'stages.jobPlan']);

        $completedStageIds = $this->completedStageIds($trail, $collaborator);
        $completedLevelIds = $this->completedLevelIds($trail, $collaborator);
        // Uma consulta para os prazos de todos os níveis da trilha, em vez de
        // uma por nível dentro do laço.
        $records = $this->levelRecords($trail, $collaborator);

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

            $levels = $stage->levels->map(function ($level) use ($completedLevelIds, $records) {
                $completed = in_array($level->id, $completedLevelIds, true);
                $record = $records[$level->id] ?? null;

                $score = $record->score ?? null;

                return [
                    'id' => $level->id,
                    'description' => $level->description,
                    'note' => $level->note,
                    'type' => $level->type,
                    'skill' => $level->skill,
                    'cut_score' => $level->cut_score,
                    'position' => $level->position,
                    'materials' => $level->materials,
                    'completed' => $completed,
                    'starts_at' => $record->starts_at ?? null,
                    'ends_at' => $record->ends_at ?? null,
                    'period_state' => $this->periodState($record, $completed),
                    // envio do colaborador e avaliação do líder
                    'submitted_at' => $record->submitted_at ?? null,
                    'certificate_uri' => $record->certificate_uri ?? null,
                    'score' => $score,
                    'evaluation_note' => $record->note ?? null,
                    'reproved' => !is_null($score) && $score < $level->cut_score,
                    'level_state' => $this->levelState($record, $completed),
                ];
            });

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
                'submitted_levels_count' => $levels->where('level_state', self::LEVEL_SUBMITTED)->count(),
            ] + $this->stagePercents($stage, $levels);

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

    /**
     * Onde o nível está no fluxo: ninguém tocou, o colaborador enviou e
     * espera avaliação, ou o líder concluiu.
     */
    private function levelState(?object $record, bool $completed): string
    {
        if ($completed) {
            return self::LEVEL_COMPLETED;
        }

        return $record && $record->submitted_at ? self::LEVEL_SUBMITTED : self::LEVEL_PENDING;
    }

    /**
     * As duas porcentagens da etapa.
     *
     * `completion` é quanto do quórum foi cumprido — a barra de progresso de
     * sempre. `evaluation` é a média das notas, e considera só os níveis já
     * avaliados: com nível sem nota entrando como zero, a média despencaria e
     * diria que o colaborador foi mal, quando na verdade ninguém corrigiu.
     */
    private function stagePercents(TrailStage $stage, $levels): array
    {
        $required = min($stage->required_count, $levels->count());
        $done = $levels->where('completed', true)->count();
        $scored = $levels->whereNotNull('score');

        return [
            'completion_percent' => $required > 0 ? (int) round(min($done / $required, 1) * 100) : null,
            'evaluation_percent' => $scored->count() > 0 ? (int) round($scored->avg('score')) : null,
            'evaluated_levels_count' => $scored->count(),
        ];
    }

    /**
     * Estado do prazo do nível, na ordem em que importa mostrar.
     *
     * Sem datas o nível é "não iniciado": o prazo só começa a contar quando o
     * sublíder escolhe início e fim. Estourar o fim apenas marca atrasado, não
     * bloqueia a conclusão.
     */
    private function periodState(?object $record, bool $completed): string
    {
        if ($completed) {
            return self::PERIOD_DONE;
        }

        if (!$record || !$record->starts_at || !$record->ends_at) {
            return self::PERIOD_NOT_STARTED;
        }

        $today = now()->toDateString();

        if ($record->ends_at < $today) {
            return self::PERIOD_LATE;
        }

        // Prazo agendado para o futuro não pode aparecer como "em andamento".
        if ($record->starts_at > $today) {
            return self::PERIOD_SCHEDULED;
        }

        return self::PERIOD_RUNNING;
    }

    /**
     * Prazos de todos os níveis da trilha, indexados por nível.
     */
    private function levelRecords(Trail $trail, Collaborator $collaborator): array
    {
        return DB::table('trail_level_collaborator')
            ->join('trail_levels', 'trail_levels.id', '=', 'trail_level_collaborator.trail_level_id')
            ->join('trail_stages', 'trail_stages.id', '=', 'trail_levels.trail_stage_id')
            ->where('trail_stages.trail_id', $trail->id)
            ->where('trail_level_collaborator.collaborator_id', $collaborator->id)
            ->whereNull('trail_level_collaborator.deleted_at')
            ->get([
                'trail_levels.id as level_id',
                'trail_level_collaborator.starts_at',
                'trail_level_collaborator.ends_at',
                'trail_level_collaborator.submitted_at',
                'trail_level_collaborator.certificate_uri',
                'trail_level_collaborator.score',
                'trail_level_collaborator.note',
            ])
            ->keyBy('level_id')
            ->all();
    }

    /**
     * A linha do pivô nível × colaborador, que pode existir só com o prazo,
     * sem conclusão nenhuma.
     */
    private function levelRecord(TrailLevel $level, Collaborator $collaborator): ?object
    {
        return DB::table('trail_level_collaborator')
            ->where('trail_level_id', $level->id)
            ->where('collaborator_id', $collaborator->id)
            ->whereNull('deleted_at')
            ->first();
    }

    /**
     * Prazo só faz sentido para quem está matriculado — é o que amarra o
     * período à matrícula, e não ao nível solto.
     */
    private function assertEnrolled(Trail $trail, Collaborator $collaborator): void
    {
        $enrolled = DB::table('trail_collaborator')
            ->where('trail_id', $trail->id)
            ->where('collaborator_id', $collaborator->id)
            ->whereNull('deleted_at')
            ->exists();

        if (!$enrolled) {
            throw new DomainException('O colaborador não está matriculado nesta trilha.');
        }
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
     * R2 - a etapa só fecha com o quórum de níveis atingido.
     *
     * Faltava validar isso no avanço manual: `syncStageCompletion` só chama
     * `advanceStage` quando o quórum bate, mas a rota de avançar chamava
     * direto, então o líder fechava a etapa com todos os níveis pendentes.
     *
     * Etapa sem nível cadastrado continua liberada: aí não existe quórum a
     * cobrar, e é assim que as etapas de tarefa única são fechadas hoje.
     *
     * O quórum é limitado ao número de níveis existentes. Sem isso, uma etapa
     * que exige 3 e tem 2 níveis cadastrados nunca fecharia.
     */
    private function assertQuorumReached(TrailStage $stage, Collaborator $collaborator): void
    {
        $levels = $stage->levels()->count();

        if ($levels === 0) {
            return;
        }

        $done = $this->completedLevelsCount($stage, $collaborator);
        $required = min($stage->required_count, $levels);

        if ($done < $required) {
            throw new DomainException(
                "Conclua {$required} nível(is) desta etapa antes de finalizá-la ({$done} de {$required})."
            );
        }
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
