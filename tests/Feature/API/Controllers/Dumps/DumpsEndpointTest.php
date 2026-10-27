<?php

namespace Tests\Feature\API\Controllers\Dumps;

use App\Jobs\RefreshDatabaseJob;
use App\Models\Corporate;
use App\Models\Dump;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DumpsEndpointTest extends TestCase
{
    private Corporate $municipio;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $role = Role::firstOrCreate(['name' => 'corporates.test']);
        foreach (['index', 'show', 'store', 'update'] as $acao) {
            Permission::firstOrCreate(['name' => "corporates.$acao", 'guard_name' => 'web']);
            $role->givePermissionTo("corporates.$acao");
        }

        $this->user = User::factory()->create();
        $this->user->assignRole($role);
        Sanctum::actingAs($this->user);

        foreach (['prod', 'dev', 'hmg'] as $ambiente) {
            config(["db_refresh.$ambiente.user" => 'u']);
        }

        $this->municipio = Corporate::create([
            'first_name' => 'Município', 'last_name' => 'A', 'ativo' => true,
            'host_prod' => 'prod.local', 'banco_prod' => 'a_prod',
            'host_dev' => 'dev.local', 'banco_dev' => 'a_dev',
            'host_hmg' => 'hmg.local', 'banco_hmg' => 'a_hmg',
        ]);
    }

    public function test_dispara_a_atualizacao_na_fila()
    {
        Queue::fake();

        $this->postJson('/api/dumps', ['corporate_id' => $this->municipio->id, 'destino' => 'dev'])
            ->assertCreated()
            ->assertJsonPath('status', 'pendente');

        Queue::assertPushed(RefreshDatabaseJob::class);
        $this->assertDatabaseHas('dumps', ['corporate_id' => $this->municipio->id, 'user_id' => $this->user->id, 'destino' => 'dev']);
    }

    public function test_recusa_destino_prod_ou_invalido()
    {
        Queue::fake();

        $this->postJson('/api/dumps', ['corporate_id' => $this->municipio->id, 'destino' => 'prod'])
            ->assertStatus(422)->assertJsonValidationErrors('destino');

        Queue::assertNothingPushed();
    }

    public function test_recusa_segunda_atualizacao_do_mesmo_destino_mas_aceita_outro()
    {
        Queue::fake();
        $dados = ['corporate_id' => $this->municipio->id, 'destino' => 'dev'];

        $this->postJson('/api/dumps', $dados)->assertCreated();
        $this->postJson('/api/dumps', $dados)->assertStatus(409);
        $this->postJson('/api/dumps', ['destino' => 'hmg'] + $dados)->assertCreated();
    }

    public function test_recusa_quando_o_host_de_destino_e_o_de_producao()
    {
        Queue::fake();
        $this->municipio->update(['host_dev' => 'prod.local']);

        $this->postJson('/api/dumps', ['corporate_id' => $this->municipio->id, 'destino' => 'dev'])->assertStatus(422);

        Queue::assertNothingPushed();
    }

    public function test_recusa_organizacao_inativa_ou_sem_banco()
    {
        Queue::fake();

        $this->municipio->update(['ativo' => false]);
        $this->postJson('/api/dumps', ['corporate_id' => $this->municipio->id, 'destino' => 'dev'])->assertStatus(422);

        $this->municipio->update(['ativo' => true, 'banco_dev' => null, 'host_prod' => '']);
        $this->postJson('/api/dumps', ['corporate_id' => $this->municipio->id, 'destino' => 'dev'])
            ->assertStatus(422)->assertJsonPath('message', 'Cadastro incompleto de Município A: preencha host de PROD, banco de DEV no cadastro da organização.');

        Queue::assertNothingPushed();
    }

    public function test_lista_todas_as_organizacoes_ativas_mesmo_com_cadastro_incompleto()
    {
        Corporate::create(['first_name' => 'Inativa', 'last_name' => 'X', 'banco_prod' => 'x_prod']); // ativo = false por padrão
        Corporate::create(['first_name' => 'Sem', 'last_name' => 'Banco', 'ativo' => true]);

        $this->getJson('/api/dumps/organizacoes')
            ->assertOk()->assertJsonCount(2)->assertJsonPath('0.nome', 'Município A')->assertJsonPath('1.nome', 'Sem Banco');
    }

    public function test_consulta_status_e_historico_por_municipio()
    {
        $dump = Dump::create([
            'corporate_id' => $this->municipio->id, 'user_id' => $this->user->id, 'destino' => 'hmg',
            'status' => Dump::FALHA, 'erro' => 'Access denied', 'log' => 'texto longo',
        ]);

        $this->getJson("/api/dumps/{$dump->id}")
            ->assertOk()->assertJsonPath('status', 'falha')->assertJsonPath('erro', 'Access denied');

        $this->getJson("/api/dumps?corporate_id={$this->municipio->id}")
            ->assertOk()->assertJsonCount(1)->assertJsonPath('0.corporate.nome', 'Município A')
            ->assertJsonMissingPath('0.log');

        $this->getJson('/api/dumps?corporate_id=outro')->assertOk()->assertJsonCount(0);
    }

    public function test_cancela_atualizacao_presa_e_libera_o_destino()
    {
        Queue::fake();
        $id = $this->postJson('/api/dumps', ['corporate_id' => $this->municipio->id, 'destino' => 'dev'])->json('id');

        $this->getJson('/api/dumps/organizacoes')->assertJsonPath('0.andamento.dev.id', $id)->assertJsonPath('0.andamento.hmg', null);

        $this->postJson("/api/dumps/$id/cancel")->assertOk()->assertJsonPath('status', 'falha');
        $this->postJson("/api/dumps/$id/cancel")->assertStatus(409);

        $this->getJson('/api/dumps/organizacoes')->assertJsonPath('0.andamento.dev', null)->assertJsonPath('0.ultimo.dev.status', 'falha');
        $this->postJson('/api/dumps', ['corporate_id' => $this->municipio->id, 'destino' => 'dev'])->assertCreated();
    }

    public function test_job_cancelado_antes_de_rodar_nao_executa()
    {
        $dump = Dump::create([
            'corporate_id' => $this->municipio->id, 'user_id' => $this->user->id, 'destino' => 'dev',
            'status' => Dump::FALHA, 'erro' => 'Cancelada pelo usuário.',
        ]);

        (new RefreshDatabaseJob($dump))->handle(app(\App\Actions\Dumps\AtualizarBancoAction::class));

        $this->assertSame('falha', $dump->fresh()->status);
    }

    public function test_exige_as_permissoes_de_organizacoes()
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/dumps/organizacoes')->assertForbidden();
        $this->postJson('/api/dumps', ['corporate_id' => $this->municipio->id, 'destino' => 'dev'])->assertForbidden();
    }
}
