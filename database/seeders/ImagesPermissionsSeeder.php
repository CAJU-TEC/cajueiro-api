<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class ImagesPermissionsSeeder extends Seeder
{
    public function run()
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        //
        $permissionsByRole = [
            'images' => [
                'images.destroy',
            ],
            'images.programadores' => [
                'images.destroy',
            ],
        ];

        $insertPermissions = fn ($role) => collect($permissionsByRole[$role])
            ->map(fn ($name) => Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']))
            ->toArray();

        $permissionIdsByRole = [
            'images.*' => $insertPermissions('images'),
            'images.programadores' => $insertPermissions('images.programadores'),
        ];


        foreach ($permissionIdsByRole as $role => $permissions) {
            $role = Role::firstOrCreate(['name' => $role]);

            foreach ($permissions as $permission) {
                $role->givePermissionTo($permission['name']);
            }
        }
    }
}
