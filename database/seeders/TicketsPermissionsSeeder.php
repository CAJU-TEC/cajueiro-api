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
            'reports' => [
                'reports.support',
                'reports.development',
                'reports.qa',
            ],
        ];

        $insertPermissions = fn($role) => collect($permissionsByRole[$role])
            ->map(fn($name) => Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']))
            ->toArray();

        $permissionIdsByRole = [
            'tickets.*' => $insertPermissions('tickets'),
            'tickets.programadores' => $insertPermissions('tickets.programadores'),
            'reports.*' => $insertPermissions('reports'),
        ];


        foreach ($permissionIdsByRole as $role => $permissions) {
            $role = Role::firstOrCreate(['name' => $role]);

            foreach ($permissions as $permission) {
                $role->givePermissionTo($permission['name']);
            }
        }
    }
}
