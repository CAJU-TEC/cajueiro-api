<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Support\Str;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionsDemoSeeder extends Seeder
{
    /**
     * Create the initial roles and permissions.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // gets all permissions via Gate::before rule; see AuthServiceProvider

        // create demo users
        // $user = \App\Models\User::factory()->create([
        //     'name' => 'Programador',
        //     'email' => 'programador@programador.com',
        //     'password' => 'password'
        // ]);

        // $user = \App\Models\User::factory()->create([
        //     'name' => 'Super-Admin',
        //     'email' => 'superadmin@superadmin.com',
        //     'password' => 'mqnGsd9LPB0ElTN9yTsKer8tGNwrxUqo'
        // ]);
        // $superAdmin = Role::create(['name' => 'super-admin']);
        // $user->assignRole($superAdmin);
    }
}
