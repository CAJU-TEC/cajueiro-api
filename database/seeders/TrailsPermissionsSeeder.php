<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class TrailsPermissionsSeeder extends Seeder
{
    public function run()
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        //
        // Três perfis, três papéis:
        //
        //   trails.*            Líder (gestor da empresa) - tudo, inclusive excluir
        //   trails.lider        Sublíder - cria e gerencia trilhas, não exclui
        //   trails.colaborador  Liderado - só a própria trilha
        //
        // Avaliar o nível não tem permissão própria: a nota é dada no ato de
        // concluir, então quem tem trails.advance avalia. Anexar certificado
        // também não: é escrita na própria matrícula, e a checagem é no
        // servidor ("é o dono, ou tem trails.advance") — permissão global
        // deixaria um liderado anexar na trilha de outro.
        $permissionsByRole = [
            // gestão completa da trilha
            'trails' => [
                'trails.destroy',
                'trails.index',
                'trails.show',
                'trails.store',
                'trails.update',
                'trails.advance',
                'trails.mine',
                'trails.report',
            ],
            // sublíder: cria, edita e avança seus liderados, mas não exclui trilhas
            'trails.lider' => [
                'trails.index',
                'trails.show',
                'trails.store',
                'trails.update',
                'trails.advance',
                'trails.mine',
                'trails.report',
            ],
            // liderado: só enxerga a própria trilha
            'trails.colaborador' => [
                'trails.mine',
            ],
        ];

        $insertPermissions = fn ($role) => collect($permissionsByRole[$role])
            ->map(fn ($name) => Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']))
            ->toArray();

        $permissionIdsByRole = [
            'trails.*' => $insertPermissions('trails'),
            'trails.lider' => $insertPermissions('trails.lider'),
            'trails.colaborador' => $insertPermissions('trails.colaborador'),
        ];

        foreach ($permissionIdsByRole as $role => $permissions) {
            $role = Role::firstOrCreate(['name' => $role]);

            foreach ($permissions as $permission) {
                $role->givePermissionTo($permission['name']);
            }
        }
    }
}
