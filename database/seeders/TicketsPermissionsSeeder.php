<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class TicketsPermissionsSeeder extends Seeder
{
    public function run()
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        //
        $permissionsByRole = [
            'tickets' => [
                'tickets.destroy',
                'tickets.index',
                'tickets.show',
                'tickets.create',
                'tickets.store',
                'tickets.list',
                // 'tickets.edit',
                // 'tickets.update',
            ],
            'tickets.programadores' => [
                'tickets.index',
                'tickets.show',
                'tickets.create',
                'tickets.store',
                // 'tickets.edit',
                // 'tickets.update',
            ],
        ];

        $insertPermissions = fn ($role) => collect($permissionsByRole[$role])
            ->map(fn ($name) => Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']))
            ->toArray();

        $permissionIdsByRole = [
            'tickets.*' => $insertPermissions('tickets'),
            'tickets.programadores' => $insertPermissions('tickets.programadores'),
        ];

        $user = \App\Models\User::firstOrCreate([
            'name' => 'Atendente',
            'email' => 'atendente@atendente.com',
        ], [
            'name' => 'Atendente',
            'email' => 'atendente@atendente.com',
            'password' => 'password' //password
        ]);
        $user->givePermissionTo('tickets.index');


        foreach ($permissionIdsByRole as $role => $permissions) {
            $role = Role::firstOrCreate(['name' => $role]);

            foreach ($permissions as $permission) {
                $role->givePermissionTo($permission['name']);
            }
        }
    }
}
