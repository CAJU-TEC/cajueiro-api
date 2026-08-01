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

        $response = $this->postJson("/api/trails/levels/{$this->levelOf($this->stageOne, 0)->id}/complete", [
            'collaborator_id' => $this->collaborator->id,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('stages.0.state', 'unlocked');
        $response->assertJsonPath('stages.0.completed_levels_count', 1);

        $this->assertNull($this->collaborator->fresh()->jobplan_id);
    }

    public function test_reaching_required_levels_completes_stage_and_promotes_collaborator()
    {
        $this->actingAsUserWith(['trails.advance', 'trails.index']);

        $this->postJson("/api/trails/levels/{$this->levelOf($this->stageOne, 0)->id}/complete", [
            'collaborator_id' => $this->collaborator->id,
        ])->assertStatus(200);

        $response = $this->postJson("/api/trails/levels/{$this->levelOf($this->stageOne, 1)->id}/complete", [
            'collaborator_id' => $this->collaborator->id,
        ]);

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

        $response = $this->postJson("/api/trails/levels/{$this->levelOf($this->stageTwo, 0)->id}/complete", [
            'collaborator_id' => $this->collaborator->id,
        ]);

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

    private function advance(TrailStage $stage): void
    {
        $this->postJson("/api/trails/stages/{$stage->id}/advance", [
            'collaborator_id' => $this->collaborator->id,
        ])->assertStatus(200);
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
