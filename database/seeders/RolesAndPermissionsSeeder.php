<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles
        $superAdmin = \Spatie\Permission\Models\Role::create(['name' => 'Super Admin']);
        $manager = \Spatie\Permission\Models\Role::create(['name' => 'Manager']);
        $seller = \Spatie\Permission\Models\Role::create(['name' => 'Seller']);
        $productionOfficer = \Spatie\Permission\Models\Role::create(['name' => 'Production Officer']);
        $auditor = \Spatie\Permission\Models\Role::create(['name' => 'Auditor']);

        // Create initial Super Admin User
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'admin@albareck.local'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
                'is_active' => true,
            ]
        );

        $user->assignRole($superAdmin);
    }
}
