<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class ImpactsPermissionsSeeder extends Seeder
{
    public function run()
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        //
        $permissionsByRole = [
            'impacts' => [
                'impacts.destroy',
                'impacts.index',
                'impacts.show',
                'impacts.store',
                'impacts.update',
                'impacts.list',
            ],
            'impacts.programadores' => [
                'impacts.index',
                'impacts.show',
                'impacts.store',
                'impacts.update',
            ],
        ];

        $insertPermissions = fn ($role) => collect($permissionsByRole[$role])
            ->map(fn ($name) => Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']))
            ->toArray();

        $permissionIdsByRole = [
            'impacts.*' => $insertPermissions('impacts'),
            'impacts.programadores' => $insertPermissions('impacts.programadores'),
        ];


        foreach ($permissionIdsByRole as $role => $permissions) {
            $role = Role::firstOrCreate(['name' => $role]);

            foreach ($permissions as $permission) {
                $role->givePermissionTo($permission['name']);
            }
        }
    }
}
