<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class GroupsPermissionsSeeder extends Seeder
{
    public function run()
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        //
        $permissionsByRole = [
            'groups' => [
                'groups.destroy',
                'groups.index',
                'groups.show',
                'groups.store',
                'groups.update',
            ],
            'groups.programadores' => [
                'groups.index',
                'groups.show',
                'groups.store',
                'groups.update',
            ],
        ];

        $insertPermissions = fn ($role) => collect($permissionsByRole[$role])
            ->map(fn ($name) => Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']))
            ->toArray();

        $permissionIdsByRole = [
            'groups.*' => $insertPermissions('groups'),
            'groups.programadores' => $insertPermissions('groups.programadores'),
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
