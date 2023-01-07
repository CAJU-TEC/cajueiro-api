<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class CollaboratorPermissionsSeeder extends Seeder
{
    public function run()
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        //
        $permissionsByRole = [
            'collaborators' => [
                'collaborators.destroy',
                'collaborators.index',
                'collaborators.show',
                'collaborators.store',
                'collaborators.update',
            ],
            'collaborators.programadores' => [
                'collaborators.index',
                'collaborators.show',
                'collaborators.store',
                'collaborators.update',
            ],
        ];

        $insertPermissions = fn ($role) => collect($permissionsByRole[$role])
            ->map(fn ($name) => Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']))
            ->toArray();

        $permissionIdsByRole = [
            'collaborators.*' => $insertPermissions('collaborators'),
            'collaborators.programadores' => $insertPermissions('collaborators.programadores'),
        ];

        $user = \App\Models\User::firstOrCreate([
            'name' => 'Atendente',
            'email' => 'atendente@atendente.com',
        ], [
            'name' => 'Atendente',
            'email' => 'atendente@atendente.com',
            'password' => 'password' //password
        ]);

        foreach ($permissionIdsByRole as $role => $permissions) {
            $role = Role::firstOrCreate(['name' => $role]);
            $user->assignRole($role);

            foreach ($permissions as $permission) {
                $role->givePermissionTo($permission['name']);
            }
        }
    }
}
