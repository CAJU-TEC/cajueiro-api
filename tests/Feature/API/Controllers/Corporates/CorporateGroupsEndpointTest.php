<?php

namespace Tests\Feature\API\Controllers\Corporates;

use App\Models\Corporate;
use App\Models\CorporateGroup;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class CorporateGroupsEndpointTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $role = Role::firstOrCreate(['name' => 'corporates.test']);
        foreach (['index', 'store', 'update', 'destroy'] as $acao) {
            Permission::firstOrCreate(['name' => "corporates.$acao", 'guard_name' => 'web']);
            $role->givePermissionTo("corporates.$acao");
        }

        $user = User::factory()->create();
        $user->assignRole($role);
        Sanctum::actingAs($user);
    }

    public function test_crud_e_organizacao_no_grupo()
    {
        $id = $this->postJson('/api/corporate-groups', ['nome' => 'Piauí'])->assertCreated()->json('id');
        $this->postJson('/api/corporate-groups', ['nome' => 'Piauí'])->assertStatus(422)->assertJsonValidationErrors('nome');

        $this->putJson("/api/corporate-groups/$id", ['nome' => 'PI'])->assertOk()->assertJsonPath('nome', 'PI');

        $organizacao = Corporate::create(['first_name' => 'Cocal', 'last_name' => 'PI', 'address' => 'x', 'corporate_group_id' => $id]);
        $this->getJson('/api/corporate-groups')->assertOk()->assertJsonPath('0.corporates_count', 1);
        $this->putJson("/api/corporates/{$organizacao->id}", ['first_name' => 'Cocal', 'last_name' => 'PI', 'address' => 'x', 'corporate_group_id' => 'inexistente'])
            ->assertStatus(422)->assertJsonValidationErrors('corporate_group_id');

        // apagar o grupo não apaga a organização, só a deixa sem grupo
        $this->deleteJson("/api/corporate-groups/$id")->assertNoContent();
        $this->assertNull($organizacao->fresh()->corporate_group_id);
        $this->assertNotNull($organizacao->fresh());
        $this->assertSame(0, CorporateGroup::count());
    }
}
