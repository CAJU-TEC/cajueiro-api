<?php

namespace Tests\Feature\API\Controllers\Trails;

use App\Models\Collaborator;
use App\Models\JobPlans;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Team;
use App\Models\Trail;
use App\Models\TrailLevel;
use App\Models\TrailStage;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class TrailProgressEndpointTest extends TestCase
{
    private Team $team;
    private Trail $trail;
    private Collaborator $collaborator;
    private TrailStage $stageOne;
    private TrailStage $stageTwo;
    private JobPlans $planTrainee;
    private JobPlans $planJunior;

    protected function setUp(): void
    {
        parent::setUp();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->team = $this->persist(Team::create(['name' => 'Dev']));

        $this->planTrainee = $this->persist(JobPlans::create([
            'team_id' => $this->team->id,
            'description' => 'caju estágio',
            'badge_icon' => 'military_tech',
            'badge_color' => '#FFB300',
            'position' => 1,
        ]));

        $this->planJunior = $this->persist(JobPlans::create([
            'team_id' => $this->team->id,
            'description' => 'caju junior',
            'badge_icon' => 'workspace_premium',
            'badge_color' => '#43A047',
            'position' => 2,
        ]));

        $this->trail = $this->persist(Trail::create([
            'team_id' => $this->team->id,
            'description' => 'Trilha Dev',
        ]));

        // Etapa 1 exige 2 de 3 níveis; etapa 2 exige 1 de 1.
        $this->stageOne = $this->persist(TrailStage::create([
            'trail_id' => $this->trail->id,
            'job_plan_id' => $this->planTrainee->id,
            'description' => 'Fundamentos',
            'position' => 1,
            'required_count' => 2,
        ]));

        foreach (['Fazer um protocolo', 'Documentar um bug', 'Curso de Git'] as $index => $description) {
            TrailLevel::create([
                'trail_stage_id' => $this->stageOne->id,
                'description' => $description,
                'position' => $index + 1,
            ]);
        }

        $this->stageTwo = $this->persist(TrailStage::create([
            'trail_id' => $this->trail->id,
            'job_plan_id' => $this->planJunior->id,
            'description' => 'Automação',
            'position' => 2,
            'required_count' => 1,
        ]));

        TrailLevel::create([
            'trail_stage_id' => $this->stageTwo->id,
            'description' => 'Fazer uma automação',
            'position' => 1,
        ]);

        $this->collaborator = $this->persist(Collaborator::create([
            'team_id' => $this->team->id,
            'first_name' => 'Ana',
            'last_name' => 'Souza',
        ]));

        DB::table('trail_collaborator')->insert([
            'trail_id' => $this->trail->id,
            'collaborator_id' => $this->collaborator->id,
            'started_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Init::boot() grava a chave como objeto Str::uuid(); relê o registro para
     * trabalhar sempre com o id em string, como fazem os controllers (findOrFail).
     */
    private function persist(Model $model): Model
    {
        return $model->newQuery()->findOrFail((string) $model->getKey());
    }

    public function test_completing_less_than_required_levels_keeps_stage_open()
    {
        $this->actingAsUserWith(['trails.advance', 'trails.index']);

        $response = $this->complete($this->levelOf($this->stageOne, 0));

        $response->assertStatus(200);
        $response->assertJsonPath('stages.0.state', 'unlocked');
        $response->assertJsonPath('stages.0.completed_levels_count', 1);

        $this->assertNull($this->collaborator->fresh()->jobplan_id);
    }

    public function test_reaching_required_levels_completes_stage_and_promotes_collaborator()
    {
        $this->actingAsUserWith(['trails.advance', 'trails.index']);

        $this->complete($this->levelOf($this->stageOne, 0))->assertStatus(200);

        $response = $this->complete($this->levelOf($this->stageOne, 1));

        $response->assertStatus(200);
        $response->assertJsonPath('stages.0.state', 'completed');
        $response->assertJsonPath('stages.1.state', 'unlocked');

        $collaborator = $this->collaborator->fresh();
        $this->assertSame($this->planTrainee->id, $collaborator->jobplan_id);

        // R5 - badge derivado da etapa concluída
        $badges = $collaborator->badges;
        $this->assertCount(1, $badges);
        $this->assertSame('military_tech', $badges->first()->badge_icon);
        $this->assertNotNull($badges->first()->certificate_code);
    }

    public function test_cannot_skip_stages()
    {
        $this->actingAsUserWith(['trails.advance', 'trails.index']);

        $response = $this->complete($this->levelOf($this->stageTwo, 0));

        $response->assertStatus(422);
        $this->assertSame('Conclua as etapas anteriores antes de avançar.', $response->json());
        $this->assertNull($this->collaborator->fresh()->jobplan_id);
    }

    public function test_collaborator_from_another_team_cannot_be_enrolled()
    {
        $this->actingAsUserWith(['trails.update', 'trails.index']);

        $otherTeam = Team::create(['name' => 'Suporte']);
        $bruno = Collaborator::create([
            'team_id' => $otherTeam->id,
            'first_name' => 'Bruno',
            'last_name' => 'Lima',
        ]);

        $response = $this->postJson("/api/trails/{$this->trail->id}/collaborators", [
            'collaborator_id' => $bruno->id,
        ]);

        $response->assertStatus(422);
        $this->assertSame('O colaborador não pertence ao time da trilha.', $response->json());
    }

    public function test_user_without_advance_permission_is_forbidden()
    {
        $this->actingAsUserWith(['trails.index']);

        $response = $this->postJson("/api/trails/levels/{$this->levelOf($this->stageOne, 0)->id}/complete", [
            'collaborator_id' => $this->collaborator->id,
        ]);

        $response->assertStatus(403);

        $this->assertDatabaseCount('trail_level_collaborator', 0);
    }

    /**
     * R2 no avanço manual: sem o quórum de níveis a etapa não fecha, nem pela
     * rota. Era o que permitia ao líder concluir uma etapa intocada.
     */
    public function test_cannot_advance_stage_with_levels_pending()
    {
        $this->actingAsUserWith(['trails.advance', 'trails.index']);

        // stageOne exige 2 níveis e nenhum foi concluído.
        $response = $this->postJson("/api/trails/stages/{$this->stageOne->id}/advance", [
            'collaborator_id' => $this->collaborator->id,
        ]);

        $response->assertStatus(422);
        $this->assertSame(
            'Conclua 2 nível(is) desta etapa antes de finalizá-la (0 de 2).',
            $response->json()
        );
        $this->assertNull($this->collaborator->fresh()->jobplan_id);
    }

    public function test_stage_without_levels_can_still_be_advanced_manually()
    {
        $this->actingAsUserWith(['trails.advance', 'trails.index']);

        $this->advance($this->stageOne);
        $this->advance($this->stageTwo);

        // Etapa de tarefa única: não há nível, então não há quórum a cobrar.
        $solo = $this->persist(TrailStage::create([
            'trail_id' => $this->trail->id,
            'description' => 'Implementar um deploy novo',
            'position' => 3,
            'required_count' => 1,
        ]));

        $response = $this->postJson("/api/trails/stages/{$solo->id}/advance", [
            'collaborator_id' => $this->collaborator->id,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('stages.2.state', 'completed');
    }

    public function test_cannot_undo_stage_with_a_later_stage_completed()
    {
        $this->actingAsUserWith(['trails.advance', 'trails.index']);

        $this->advance($this->stageOne);
        $this->advance($this->stageTwo);

        $response = $this->deleteJson("/api/trails/stages/{$this->stageOne->id}/advance", [
            'collaborator_id' => $this->collaborator->id,
        ]);

        $response->assertStatus(422);
        $this->assertSame('Desfaça as etapas posteriores primeiro.', $response->json());
    }

    public function test_undoing_the_last_stage_reverts_the_job_plan()
    {
        $this->actingAsUserWith(['trails.advance', 'trails.index']);

        $this->advance($this->stageOne);
        $this->advance($this->stageTwo);

        $this->assertSame($this->planJunior->id, $this->collaborator->fresh()->jobplan_id);

        $response = $this->deleteJson("/api/trails/stages/{$this->stageTwo->id}/advance", [
            'collaborator_id' => $this->collaborator->id,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('stages.1.state', 'unlocked');
        $this->assertSame($this->planTrainee->id, $this->collaborator->fresh()->jobplan_id);
        $this->assertCount(1, $this->collaborator->fresh()->badges);
    }

    /**
     * O cargo pode vir da contratação, não só da trilha. Desfazer tem que
     * devolver esse cargo, não zerar.
     */
    public function test_undoing_the_only_stage_restores_the_plan_from_before_the_trail()
    {
        $this->actingAsUserWith(['trails.advance', 'trails.index']);

        $hired = $this->persist(JobPlans::create([
            'team_id' => $this->team->id,
            'description' => 'caju contratação',
            'position' => 0,
        ]));
        $this->collaborator->update(['jobplan_id' => $hired->id]);

        $this->advance($this->stageOne);
        $this->assertSame($this->planTrainee->id, $this->collaborator->fresh()->jobplan_id);

        $response = $this->deleteJson("/api/trails/stages/{$this->stageOne->id}/advance", [
            'collaborator_id' => $this->collaborator->id,
        ]);

        $response->assertStatus(200);
        $this->assertSame($hired->id, $this->collaborator->fresh()->jobplan_id);
    }

    public function test_undoing_a_stage_without_job_plan_keeps_the_current_plan()
    {
        $this->actingAsUserWith(['trails.advance', 'trails.index']);

        $stageThree = $this->persist(TrailStage::create([
            'trail_id' => $this->trail->id,
            'description' => 'Mentoria',
            'position' => 3,
            'required_count' => 1,
        ]));
        TrailLevel::create([
            'trail_stage_id' => $stageThree->id,
            'description' => 'Acompanhar um par',
            'position' => 1,
        ]);

        $this->advance($this->stageOne);
        $this->advance($this->stageTwo);
        $this->advance($stageThree);

        $this->assertSame($this->planJunior->id, $this->collaborator->fresh()->jobplan_id);

        $this->deleteJson("/api/trails/stages/{$stageThree->id}/advance", [
            'collaborator_id' => $this->collaborator->id,
        ])->assertStatus(200);

        // A etapa não promoveu ninguém, então não tem cargo a devolver.
        $this->assertSame($this->planJunior->id, $this->collaborator->fresh()->jobplan_id);
    }

    public function test_undoing_a_stage_does_not_overwrite_a_plan_changed_afterwards()
    {
        $this->actingAsUserWith(['trails.advance', 'trails.index']);

        $this->advance($this->stageOne);

        // Simula promoção por outra trilha (ou edição manual do RH) depois do avanço.
        $this->collaborator->update(['jobplan_id' => $this->planJunior->id]);

        $this->deleteJson("/api/trails/stages/{$this->stageOne->id}/advance", [
            'collaborator_id' => $this->collaborator->id,
        ])->assertStatus(200);

        $this->assertSame($this->planJunior->id, $this->collaborator->fresh()->jobplan_id);
    }

    public function test_leader_sets_the_level_period()
    {
        $this->actingAsUserWith(['trails.update', 'trails.index']);

        $response = $this->putJson("/api/trails/levels/{$this->levelOf($this->stageOne, 0)->id}/period", [
            'collaborator_id' => $this->collaborator->id,
            'starts_at' => now()->subDay()->toDateString(),
            'ends_at' => now()->addDays(6)->toDateString(),
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('stages.0.levels.0.period_state', 'running');
        $response->assertJsonPath('stages.0.levels.0.ends_at', now()->addDays(6)->toDateString());

        // Prazo não é conclusão: a linha do pivô existe, o nível não.
        $response->assertJsonPath('stages.0.levels.0.completed', false);
        $response->assertJsonPath('stages.0.completed_levels_count', 0);
        $response->assertJsonPath('stages.0.state', 'unlocked');
    }

    public function test_level_without_period_is_not_started_and_overdue_is_late()
    {
        $this->actingAsUserWith(['trails.update', 'trails.index']);

        $level = $this->levelOf($this->stageOne, 0);

        $this->putJson("/api/trails/levels/{$level->id}/period", [
            'collaborator_id' => $this->collaborator->id,
        ])->assertStatus(200)->assertJsonPath('stages.0.levels.0.period_state', 'not_started');

        $this->putJson("/api/trails/levels/{$level->id}/period", [
            'collaborator_id' => $this->collaborator->id,
            'starts_at' => now()->subDays(10)->toDateString(),
            'ends_at' => now()->subDay()->toDateString(),
        ])->assertStatus(200)->assertJsonPath('stages.0.levels.0.period_state', 'late');
    }

    /**
     * O prazo mora no mesmo pivô da conclusão, e o desfazer apagava a linha.
     */
    public function test_undoing_a_level_keeps_its_period()
    {
        $this->actingAsUserWith(['trails.update', 'trails.advance', 'trails.index']);

        $level = $this->levelOf($this->stageOne, 0);
        $ends = now()->addDays(3)->toDateString();

        $this->putJson("/api/trails/levels/{$level->id}/period", [
            'collaborator_id' => $this->collaborator->id,
            'starts_at' => now()->toDateString(),
            'ends_at' => $ends,
        ])->assertStatus(200);

        $this->complete($level)
            ->assertStatus(200)
            ->assertJsonPath('stages.0.levels.0.period_state', 'done');

        $response = $this->deleteJson("/api/trails/levels/{$level->id}/complete", [
            'collaborator_id' => $this->collaborator->id,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('stages.0.levels.0.completed', false);
        $response->assertJsonPath('stages.0.levels.0.ends_at', $ends);
        $response->assertJsonPath('stages.0.levels.0.period_state', 'running');
    }

    public function test_level_period_rejects_a_single_date_or_inverted_range()
    {
        $this->actingAsUserWith(['trails.update', 'trails.index']);

        $level = $this->levelOf($this->stageOne, 0);

        $umaData = $this->putJson("/api/trails/levels/{$level->id}/period", [
            'collaborator_id' => $this->collaborator->id,
            'starts_at' => now()->toDateString(),
        ]);
        $umaData->assertStatus(422);
        $this->assertSame('Informe as duas datas do período, ou nenhuma.', $umaData->json());

        $invertido = $this->putJson("/api/trails/levels/{$level->id}/period", [
            'collaborator_id' => $this->collaborator->id,
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->subDay()->toDateString(),
        ]);
        $invertido->assertStatus(422);
        $this->assertSame('A data de fim não pode ser anterior à de início.', $invertido->json());
    }

    public function test_level_is_created_as_hard_skill_by_default_and_accepts_soft()
    {
        $this->actingAsUserWith(['trails.update', 'trails.index']);

        $this->postJson("/api/trails/stages/{$this->stageTwo->id}/levels", [
            'description' => 'Escrever documentacao tecnica',
        ])->assertStatus(201);

        $this->postJson("/api/trails/stages/{$this->stageTwo->id}/levels", [
            'description' => 'Apresentar em publico',
            'skill' => 'soft',
        ])->assertStatus(201);

        $skills = TrailLevel::where('trail_stage_id', $this->stageTwo->id)
            ->orderBy('created_at')
            ->pluck('skill', 'description');

        // Sem informar, o nivel nasce hard: os que ja existiam sao tecnicos.
        $this->assertSame('hard', $skills['Escrever documentacao tecnica']);
        $this->assertSame('soft', $skills['Apresentar em publico']);
    }

    public function test_level_skill_must_be_soft_or_hard()
    {
        $this->actingAsUserWith(['trails.update', 'trails.index']);

        $this->postJson("/api/trails/stages/{$this->stageTwo->id}/levels", [
            'description' => 'Nivel invalido',
            'skill' => 'medium',
        ])->assertStatus(422);
    }

    public function test_level_accepts_soft_skill_themes_as_type()
    {
        $this->actingAsUserWith(['trails.update', 'trails.index']);

        // Em soft skill o type e o tema da competencia, porque esses niveis sao
        // comportamento do dia a dia. O enum antigo do banco so tinha os tipos
        // tecnicos, e o campo virou varchar por causa disso.
        $temas = ['communication', 'empathy', 'emotional_intelligence', 'collaboration', 'proactivity', 'organization', 'leadership'];

        foreach ($temas as $type) {
            $this->postJson("/api/trails/stages/{$this->stageTwo->id}/levels", [
                'description' => "Nivel {$type}",
                'skill' => 'soft',
                'type' => $type,
            ])->assertStatus(201)->assertJsonPath('type', $type);
        }

        $this->postJson("/api/trails/stages/{$this->stageTwo->id}/levels", [
            'description' => 'Tipo inexistente',
            'type' => 'coffee_break',
        ])->assertStatus(422);
    }

    public function test_leader_completes_level_with_a_score_and_an_answer()
    {
        $this->actingAsUserWith(['trails.advance', 'trails.index']);

        $level = $this->levelOf($this->stageOne, 0);

        $response = $this->postJson("/api/trails/levels/{$level->id}/complete", [
            'collaborator_id' => $this->collaborator->id,
            'score' => 85,
            'note' => 'Explicou com clareza para o time.',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('stages.0.levels.0.score', 85);
        $response->assertJsonPath('stages.0.levels.0.evaluation_note', 'Explicou com clareza para o time.');
        $response->assertJsonPath('stages.0.levels.0.reproved', false);
        $response->assertJsonPath('stages.0.levels.0.level_state', 'completed');
        $response->assertJsonPath('stages.0.evaluation_percent', 85);
    }

    public function test_completing_a_level_requires_a_score_and_an_answer()
    {
        $this->actingAsUserWith(['trails.advance', 'trails.index']);

        $response = $this->postJson("/api/trails/levels/{$this->levelOf($this->stageOne, 0)->id}/complete", [
            'collaborator_id' => $this->collaborator->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['score', 'note']);
    }

    /**
     * R9: nivel enviado e nao avaliado nao conta para o quorum, entao a etapa
     * podia fechar pelos outros e deixar o envio sem resposta nenhuma.
     */
    public function test_stage_does_not_close_while_a_level_awaits_evaluation()
    {
        $user = $this->actingAsUserWith(['trails.advance', 'trails.mine', 'trails.index']);
        $this->collaborator->update(['user_id' => $user->id]);

        // O terceiro nivel fica na fila do lider...
        $this->postJson("/api/trails/levels/{$this->levelOf($this->stageOne, 2)->id}/submit", [
            'collaborator_id' => $this->collaborator->id,
        ])->assertStatus(200);

        // ... e os outros dois batem o quorum de 2.
        $this->complete($this->levelOf($this->stageOne, 0))->assertStatus(200);
        $response = $this->complete($this->levelOf($this->stageOne, 1));

        $response->assertStatus(200);
        $response->assertJsonPath('stages.0.completion_percent', 100);
        $response->assertJsonPath('stages.0.state', 'unlocked');
        $this->assertNull($this->collaborator->fresh()->jobplan_id);

        $manual = $this->postJson("/api/trails/stages/{$this->stageOne->id}/advance", [
            'collaborator_id' => $this->collaborator->id,
        ]);

        $manual->assertStatus(422);
        $this->assertSame(
            'Avalie o(s) 1 nível(is) aguardando avaliação antes de finalizar a etapa.',
            $manual->json()
        );

        // Avaliado o que faltava, a etapa fecha e promove.
        $this->complete($this->levelOf($this->stageOne, 2))
            ->assertStatus(200)
            ->assertJsonPath('stages.0.state', 'completed');
        $this->assertSame($this->planTrainee->id, $this->collaborator->fresh()->jobplan_id);
    }

    /**
     * R9: nota abaixo do corte reprova o nivel mas nao trava a etapa. O
     * quorum conta niveis concluidos, com nota boa ou ruim.
     */
    public function test_score_below_the_cut_reproves_the_level_without_blocking_the_stage()
    {
        $this->actingAsUserWith(['trails.advance', 'trails.index']);

        $this->complete($this->levelOf($this->stageOne, 0), ['score' => 40])->assertStatus(200);

        $response = $this->complete($this->levelOf($this->stageOne, 1), ['score' => 90]);

        $response->assertStatus(200);
        // 40 esta abaixo do corte padrao de 70
        $response->assertJsonPath('stages.0.levels.0.reproved', true);
        $response->assertJsonPath('stages.0.levels.1.reproved', false);
        // ... e a etapa fechou de qualquer forma, com o quorum de 2
        $response->assertJsonPath('stages.0.state', 'completed');
        $response->assertJsonPath('stages.0.completion_percent', 100);
        $response->assertJsonPath('stages.0.evaluation_percent', 65);
        $this->assertSame($this->planTrainee->id, $this->collaborator->fresh()->jobplan_id);
    }

    public function test_cut_score_is_configurable_per_level()
    {
        $this->actingAsUserWith(['trails.update', 'trails.advance', 'trails.index']);

        $exigente = TrailLevel::where('trail_stage_id', $this->stageOne->id)->orderBy('position')->first();
        $exigente->update(['cut_score' => 90]);

        $response = $this->complete($exigente, ['score' => 80]);

        $response->assertStatus(200);
        $response->assertJsonPath('stages.0.levels.0.cut_score', 90);
        // 80 passaria no corte padrao de 70, mas nao neste nivel
        $response->assertJsonPath('stages.0.levels.0.reproved', true);
    }

    public function test_leader_corrects_the_score_of_an_already_evaluated_level()
    {
        $this->actingAsUserWith(['trails.advance', 'trails.index']);

        $level = $this->levelOf($this->stageOne, 0);

        $this->postJson("/api/trails/levels/{$level->id}/complete", [
            'collaborator_id' => $this->collaborator->id,
            'score' => 40,
            'note' => 'Faltou praticar.',
        ])->assertStatus(200);

        $response = $this->postJson("/api/trails/levels/{$level->id}/complete", [
            'collaborator_id' => $this->collaborator->id,
            'score' => 90,
            'note' => 'Refez e ficou bom.',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('stages.0.levels.0.score', 90);
        $response->assertJsonPath('stages.0.levels.0.evaluation_note', 'Refez e ficou bom.');
        $response->assertJsonPath('stages.0.levels.0.reproved', false);
        $response->assertJsonPath('stages.0.levels.0.completed', true);
    }

    public function test_leader_removes_the_evaluation_keeping_the_level_completed()
    {
        $this->actingAsUserWith(['trails.advance', 'trails.index']);

        $level = $this->levelOf($this->stageOne, 0);

        $this->postJson("/api/trails/levels/{$level->id}/complete", [
            'collaborator_id' => $this->collaborator->id,
            'score' => 85,
            'note' => 'Bom trabalho.',
        ])->assertStatus(200);

        $response = $this->deleteJson("/api/trails/levels/{$level->id}/evaluation", [
            'collaborator_id' => $this->collaborator->id,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('stages.0.levels.0.score', null);
        $response->assertJsonPath('stages.0.levels.0.evaluation_note', null);
        $response->assertJsonPath('stages.0.levels.0.reproved', false);
        // Sai a avaliacao, nao a conclusao: o nivel continua contando no quorum.
        $response->assertJsonPath('stages.0.levels.0.completed', true);
        $response->assertJsonPath('stages.0.levels.0.level_state', 'completed');
        $response->assertJsonPath('stages.0.completed_levels_count', 1);
        $response->assertJsonPath('stages.0.evaluation_percent', null);
    }

    public function test_removing_the_evaluation_keeps_the_period_and_the_submission()
    {
        $this->actingAsUserWith(['trails.advance', 'trails.update', 'trails.mine', 'trails.index']);

        $level = $this->levelOf($this->stageOne, 0);

        $this->putJson("/api/trails/levels/{$level->id}/period", [
            'collaborator_id' => $this->collaborator->id,
            'starts_at' => '2026-03-01',
            'ends_at' => '2026-03-31',
        ])->assertStatus(200);

        $this->postJson("/api/trails/levels/{$level->id}/submit", [
            'collaborator_id' => $this->collaborator->id,
        ])->assertStatus(200);

        $this->complete($level, ['score' => 85])->assertStatus(200);

        $response = $this->deleteJson("/api/trails/levels/{$level->id}/evaluation", [
            'collaborator_id' => $this->collaborator->id,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('stages.0.levels.0.starts_at', '2026-03-01');
        $response->assertJsonPath('stages.0.levels.0.ends_at', '2026-03-31');
        $this->assertNotNull($response->json('stages.0.levels.0.submitted_at'));
    }

    public function test_user_without_advance_permission_cannot_remove_the_evaluation()
    {
        $this->actingAsUserWith(['trails.index']);

        $response = $this->deleteJson(
            "/api/trails/levels/{$this->levelOf($this->stageOne, 0)->id}/evaluation",
            ['collaborator_id' => $this->collaborator->id]
        );

        $response->assertStatus(403);
    }

    public function test_collaborator_submits_a_level_with_a_certificate()
    {
        $user = $this->actingAsUserWith(['trails.mine']);
        $this->collaborator->update(['user_id' => $user->id]);

        $level = $this->levelOf($this->stageOne, 0);
        $pdf = 'data:application/pdf;base64,' . base64_encode('%PDF-1.4 certificado');

        $response = $this->postJson("/api/trails/levels/{$level->id}/submit", [
            'collaborator_id' => $this->collaborator->id,
            'certificate' => $pdf,
        ]);

        $response->assertStatus(200);
        // Enviar nao conclui: fica aguardando a avaliacao do lider.
        $response->assertJsonPath('stages.0.levels.0.level_state', 'submitted');
        $response->assertJsonPath('stages.0.levels.0.completed', false);
        $response->assertJsonPath('stages.0.submitted_levels_count', 1);

        $arquivo = $response->json('stages.0.levels.0.certificate_uri');
        $this->assertStringEndsWith('.pdf', $arquivo);
        $this->assertFileExists(storage_path("app/public/certificates/{$arquivo}"));
        @unlink(storage_path("app/public/certificates/{$arquivo}"));
    }

    public function test_resubmitting_replaces_the_certificate_file()
    {
        $user = $this->actingAsUserWith(['trails.mine', 'trails.index']);
        $this->collaborator->update(['user_id' => $user->id]);

        $level = $this->levelOf($this->stageOne, 0);

        $primeiro = $this->submitCertificate($level, 'application/pdf', '%PDF-1.4 primeiro');
        $segundo = $this->submitCertificate($level, 'image/png', 'segundo');

        $this->assertNotSame($primeiro, $segundo);
        $this->assertStringEndsWith('.png', $segundo);
        $this->assertFileExists(storage_path("app/public/certificates/{$segundo}"));
        // O anterior não serve mais e ficaria órfão no disco para sempre.
        $this->assertFileDoesNotExist(storage_path("app/public/certificates/{$primeiro}"));

        @unlink(storage_path("app/public/certificates/{$segundo}"));
    }

    public function test_resubmitting_without_a_new_file_keeps_the_previous_one()
    {
        $user = $this->actingAsUserWith(['trails.mine', 'trails.index']);
        $this->collaborator->update(['user_id' => $user->id]);

        $level = $this->levelOf($this->stageOne, 0);

        $arquivo = $this->submitCertificate($level, 'application/pdf', '%PDF-1.4 certificado');

        $response = $this->postJson("/api/trails/levels/{$level->id}/submit", [
            'collaborator_id' => $this->collaborator->id,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('stages.0.levels.0.certificate_uri', $arquivo);
        $this->assertFileExists(storage_path("app/public/certificates/{$arquivo}"));

        @unlink(storage_path("app/public/certificates/{$arquivo}"));
    }

    public function test_certificate_in_an_unsupported_format_is_refused()
    {
        $user = $this->actingAsUserWith(['trails.mine', 'trails.index']);
        $this->collaborator->update(['user_id' => $user->id]);

        $level = $this->levelOf($this->stageOne, 0);
        $arquivo = $this->submitCertificate($level, 'application/pdf', '%PDF-1.4 certificado');

        $response = $this->postJson("/api/trails/levels/{$level->id}/submit", [
            'collaborator_id' => $this->collaborator->id,
            'certificate' => 'data:application/zip;base64,' . base64_encode('PK'),
        ]);

        // Sem isso o formato recusado passava em silêncio, com o arquivo antigo
        // no lugar e resposta 200: parecia que o reenvio não substituiu nada.
        $response->assertStatus(422);
        $this->assertSame(
            'Envie o certificado em PDF ou imagem (JPG, PNG ou WebP).',
            $response->json()
        );
        $this->assertFileExists(storage_path("app/public/certificates/{$arquivo}"));

        @unlink(storage_path("app/public/certificates/{$arquivo}"));
    }

    /**
     * O desfazer antigo apagava a linha do pivô, entao ha base com duas linhas
     * do mesmo (nivel, colaborador): uma apagada e uma viva. Escrita sem filtro
     * de deleted_at pegava as duas e revivia a antiga.
     */
    public function test_submitting_does_not_touch_an_old_soft_deleted_row()
    {
        $user = $this->actingAsUserWith(['trails.mine', 'trails.index']);
        $this->collaborator->update(['user_id' => $user->id]);

        $level = $this->levelOf($this->stageOne, 0);

        DB::table('trail_level_collaborator')->insert([
            'trail_level_id' => $level->id,
            'collaborator_id' => $this->collaborator->id,
            'certificate_uri' => 'antigo.pdf',
            'deleted_at' => now()->subDay(),
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        $arquivo = $this->submitCertificate($level, 'application/pdf', '%PDF-1.4 novo');

        $apagada = DB::table('trail_level_collaborator')
            ->where('trail_level_id', $level->id)
            ->whereNotNull('deleted_at')
            ->first();

        $this->assertNotNull($apagada, 'a linha apagada não pode ser revivida');
        $this->assertSame('antigo.pdf', $apagada->certificate_uri);
        $this->assertNull($apagada->submitted_at);

        @unlink(storage_path("app/public/certificates/{$arquivo}"));
    }

    public function test_certificate_attached_to_the_level_is_served_by_the_api()
    {
        $user = $this->actingAsUserWith(['trails.mine', 'trails.index']);
        $this->collaborator->update(['user_id' => $user->id]);

        $level = $this->levelOf($this->stageOne, 0);
        $pdf = 'data:application/pdf;base64,' . base64_encode('%PDF-1.4 certificado');

        $arquivo = $this->postJson("/api/trails/levels/{$level->id}/submit", [
            'collaborator_id' => $this->collaborator->id,
            'certificate' => $pdf,
        ])->json('stages.0.levels.0.certificate_uri');

        $response = $this->getJson(
            "/api/trails/levels/{$level->id}/certificate/{$this->collaborator->id}"
        );

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
        @unlink(storage_path("app/public/certificates/{$arquivo}"));
    }

    public function test_level_without_attached_certificate_returns_not_found()
    {
        $this->actingAsUserWith(['trails.index']);

        $response = $this->getJson(
            "/api/trails/levels/{$this->levelOf($this->stageOne, 0)->id}/certificate/{$this->collaborator->id}"
        );

        $response->assertStatus(404);
        $this->assertSame('Nenhum certificado anexado neste nível.', $response->json());
    }

    public function test_collaborator_cannot_open_the_certificate_of_someone_else()
    {
        $user = $this->actingAsUserWith(['trails.mine']);

        $outro = $this->persist(Collaborator::create([
            'team_id' => $this->team->id,
            'first_name' => 'Bruno',
            'last_name' => 'Lima',
        ]));
        $outro->update(['user_id' => $user->id]);

        $response = $this->getJson(
            "/api/trails/levels/{$this->levelOf($this->stageOne, 0)->id}/certificate/{$this->collaborator->id}"
        );

        $response->assertStatus(403);
        $this->assertSame('Você só pode abrir certificados da sua própria trilha.', $response->json());
    }

    public function test_collaborator_cannot_submit_a_level_for_someone_else()
    {
        $user = $this->actingAsUserWith(['trails.mine']);

        $outro = $this->persist(Collaborator::create([
            'team_id' => $this->team->id,
            'first_name' => 'Bruno',
            'last_name' => 'Lima',
        ]));
        $this->collaborator->update(['user_id' => $user->id]);

        $response = $this->postJson("/api/trails/levels/{$this->levelOf($this->stageOne, 0)->id}/submit", [
            'collaborator_id' => $outro->id,
        ]);

        $response->assertStatus(403);
        $this->assertSame('Você só pode enviar níveis da sua própria trilha.', $response->json());
    }

    public function test_undoing_a_level_keeps_the_submission_and_clears_the_score()
    {
        $this->actingAsUserWith(['trails.advance', 'trails.mine', 'trails.index']);

        $level = $this->levelOf($this->stageOne, 0);

        $this->postJson("/api/trails/levels/{$level->id}/submit", [
            'collaborator_id' => $this->collaborator->id,
        ])->assertStatus(200);

        $this->complete($level, ['score' => 75])->assertStatus(200);

        $response = $this->deleteJson("/api/trails/levels/{$level->id}/complete", [
            'collaborator_id' => $this->collaborator->id,
        ]);

        $response->assertStatus(200);
        // Desfazer desfaz a avaliacao, nao o envio: volta para a fila do lider.
        $response->assertJsonPath('stages.0.levels.0.level_state', 'submitted');
        $response->assertJsonPath('stages.0.levels.0.score', null);
    }

    public function test_badges_endpoint_groups_by_collaborator()
    {
        $this->actingAsUserWith(['trails.advance', 'trails.index']);

        $this->advance($this->stageOne);

        $response = $this->getJson('/api/trails/badges');

        $response->assertStatus(200);
        $response->assertJsonPath("{$this->collaborator->id}.0.badge_icon", 'military_tech');
        $response->assertJsonPath("{$this->collaborator->id}.0.job_plan", 'caju estágio');
        $this->assertCount(1, $response->json()[$this->collaborator->id]);
    }

    public function test_badges_are_ordered_chronologically_across_trails()
    {
        $this->actingAsUserWith(['trails.advance', 'trails.index', 'trails.update']);

        // Segunda trilha do mesmo time, cuja etapa 1 é conquistada por último.
        $planSpecialist = $this->persist(JobPlans::create([
            'team_id' => $this->team->id,
            'description' => 'caju especialista',
            'badge_icon' => 'stars',
            'badge_color' => '#8E24AA',
            'position' => 3,
        ]));

        $extraTrail = $this->persist(Trail::create([
            'team_id' => $this->team->id,
            'description' => 'Trilha Dev Extra',
        ]));

        $extraStage = $this->persist(TrailStage::create([
            'trail_id' => $extraTrail->id,
            'job_plan_id' => $planSpecialist->id,
            'description' => 'Mentoria',
            'position' => 1,
            'required_count' => 1,
        ]));

        DB::table('trail_collaborator')->insert([
            'trail_id' => $extraTrail->id,
            'collaborator_id' => $this->collaborator->id,
            'started_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->advance($this->stageOne);
        $this->travel(1)->minutes();
        $this->advance($this->stageTwo);
        $this->travel(1)->minutes();
        $this->advance($extraStage);
        $this->travelBack();

        $badges = $this->getJson('/api/trails/badges')->json()[$this->collaborator->id];

        // A etapa da trilha extra tem position 1, então ordenar por posição a
        // colocaria no meio da lista em vez do fim.
        $this->assertSame(
            ['caju estágio', 'caju junior', 'caju especialista'],
            array_column($badges, 'job_plan')
        );
    }

    public function test_badges_endpoint_ignores_plans_without_badge()
    {
        $this->actingAsUserWith(['trails.advance', 'trails.index']);

        $this->planTrainee->update(['badge_icon' => null]);
        $this->advance($this->stageOne);

        $response = $this->getJson('/api/trails/badges');

        $response->assertStatus(200);
        $this->assertSame([], $response->json());
        // A promoção acontece mesmo sem badge configurado.
        $this->assertSame($this->planTrainee->id, $this->collaborator->fresh()->jobplan_id);
    }

    public function test_certificate_is_blocked_for_an_incomplete_stage()
    {
        $this->actingAsUserWith(['trails.advance', 'trails.index']);

        $response = $this->getJson("/api/trails/stages/{$this->stageOne->id}/certificate/{$this->collaborator->id}");

        $response->assertStatus(422);
        $this->assertSame('Etapa não concluída.', $response->json());
    }

    public function test_finishing_every_stage_marks_the_trail_as_finished()
    {
        $this->actingAsUserWith(['trails.advance', 'trails.index']);

        $this->advance($this->stageOne);
        $this->advance($this->stageTwo);

        $enrollment = DB::table('trail_collaborator')
            ->where('trail_id', $this->trail->id)
            ->where('collaborator_id', $this->collaborator->id)
            ->first();

        $this->assertNotNull($enrollment->finished_at);
        $this->assertCount(2, $this->collaborator->fresh()->badges);
    }

    /**
     * Conclui os níveis exigidos e então fecha a etapa.
     *
     * Antes o helper batia direto na rota de avanço com todos os níveis
     * pendentes — o que passava porque o quórum não era validado ali (R2).
     * Agora esse atalho dá 422, e tem teste próprio para isso.
     */
    private function advance(TrailStage $stage): void
    {
        $levels = $stage->levels()->orderBy('position')->get();
        $required = min($stage->required_count, $levels->count());

        for ($i = 0; $i < $required; $i++) {
            $this->complete($levels[$i])->assertStatus(200);
        }

        // Atingido o quórum a etapa já fecha sozinha; a chamada explícita é
        // idempotente e cobre também a etapa sem nível cadastrado.
        $this->postJson("/api/trails/stages/{$stage->id}/advance", [
            'collaborator_id' => $this->collaborator->id,
        ])->assertStatus(200);
    }

    /**
     * Conclui o nível pela rota do líder.
     *
     * Concluir é avaliar (R9), então nota e resposta vão sempre. A maioria dos
     * cenários não se importa com quais: o padrão passa do corte de 70.
     */
    private function complete(TrailLevel $level, array $payload = [])
    {
        return $this->postJson("/api/trails/levels/{$level->id}/complete", $payload + [
            'collaborator_id' => $this->collaborator->id,
            'score' => 80,
            'note' => 'Avaliado no teste.',
        ]);
    }

    /**
     * Envia o nível com um certificado e devolve o nome do arquivo gravado.
     */
    private function submitCertificate(TrailLevel $level, string $mime, string $content): string
    {
        return $this->postJson("/api/trails/levels/{$level->id}/submit", [
            'collaborator_id' => $this->collaborator->id,
            'certificate' => "data:{$mime};base64," . base64_encode($content),
        ])->assertStatus(200)->json('stages.0.levels.0.certificate_uri');
    }

    private function levelOf(TrailStage $stage, int $index): TrailLevel
    {
        return $stage->levels()->get()->get($index);
    }

    private function actingAsUserWith(array $permissions): User
    {
        $role = Role::firstOrCreate(['name' => 'trails.test']);

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
            $role->givePermissionTo($permission);
        }

        $user = User::create([
            'name' => 'Líder',
            'email' => 'lider@cajutec.com.br',
            'password' => 'secret',
        ]);
        $user->assignRole($role);

        Sanctum::actingAs($user);

        return $user;
    }
}
