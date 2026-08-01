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
                'trails.list',
            ],
            // líder: cria, edita e avança seus liderados, mas não exclui trilhas
            'trails.lider' => [
                'trails.index',
                'trails.show',
                'trails.store',
                'trails.update',
                'trails.advance',
                'trails.mine',
            ],
            // colaborador: só enxerga a própria trilha
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
