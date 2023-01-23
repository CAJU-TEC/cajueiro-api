<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class CorporatesPermissionsSeeder extends Seeder
{
    public function run()
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        //
        $permissionsByRole = [
            'corporates' => [
                'corporates.destroy',
                'corporates.index',
                'corporates.show',
                'corporates.store',
                'corporates.update',
            ],
            'corporates.programadores' => [
                'corporates.index',
                'corporates.show',
                'corporates.store',
                'corporates.update',
            ],
        ];

        $insertPermissions = fn ($role) => collect($permissionsByRole[$role])
            ->map(fn ($name) => Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']))
            ->toArray();

        $permissionIdsByRole = [
            'corporates.*' => $insertPermissions('corporates'),
            'corporates.programadores' => $insertPermissions('corporates.programadores'),
        ];

        foreach ($permissionIdsByRole as $role => $permissions) {
            $role = Role::firstOrCreate(['name' => $role]);

            foreach ($permissions as $permission) {
                $role->givePermissionTo($permission['name']);
            }
        }
    }
}
