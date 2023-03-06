<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class ClientsPermissionsSeeder extends Seeder
{
    public function run()
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        //
        $permissionsByRole = [
            'clients' => [
                'clients.destroy',
                'clients.index',
                'clients.show',
                'clients.store',
                'clients.update',
                'clients.list',
            ],
            'clients.programadores' => [
                'clients.index',
                'clients.show',
                'clients.store',
                'clients.update',
                'clients.list',
            ],
        ];

        $insertPermissions = fn ($role) => collect($permissionsByRole[$role])
            ->map(fn ($name) => Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']))
            ->toArray();

        $permissionIdsByRole = [
            'clients.*' => $insertPermissions('clients'),
            'clients.programadores' => $insertPermissions('clients.programadores'),
        ];

        foreach ($permissionIdsByRole as $role => $permissions) {
            $role = Role::firstOrCreate(['name' => $role]);

            foreach ($permissions as $permission) {
                $role->givePermissionTo($permission['name']);
            }
        }
    }
}
