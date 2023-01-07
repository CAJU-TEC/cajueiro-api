<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class JobPlansPermissionsSeeder extends Seeder
{
    public function run()
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        //
        $permissionsByRole = [
            'jobplans' => [
                'jobplans.destroy',
                'jobplans.index',
                'jobplans.show',
                'jobplans.store',
                'jobplans.update',
            ],
            'jobplans.programadores' => [
                'jobplans.index',
                'jobplans.show',
                'jobplans.store',
                'jobplans.update',
            ],
        ];

        $insertPermissions = fn ($role) => collect($permissionsByRole[$role])
            ->map(fn ($name) => Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']))
            ->toArray();

        $permissionIdsByRole = [
            'jobplans.*' => $insertPermissions('jobplans'),
            'jobplans.programadores' => $insertPermissions('jobplans.programadores'),
        ];


        foreach ($permissionIdsByRole as $role => $permissions) {
            $role = Role::firstOrCreate(['name' => $role]);

            foreach ($permissions as $permission) {
                $role->givePermissionTo($permission['name']);
            }
        }
    }
}
