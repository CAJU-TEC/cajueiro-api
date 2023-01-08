<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            ImpactsSeeder::class,
            JobPlansSeeder::class,
            PermissionsDemoSeeder::class,
            TicketsPermissionsSeeder::class,
            CollaboratorPermissionsSeeder::class,
            ClientsPermissionsSeeder::class,
            GroupsPermissionsSeeder::class,
            ImagesPermissionsSeeder::class,
            ImpactsPermissionsSeeder::class,
            JobPlansPermissionsSeeder::class,
            UsersPermissionsSeeder::class,
        ]);
    }
}
