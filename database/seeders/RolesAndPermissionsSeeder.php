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
        $superAdmin        = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Super Admin']);
        $manager           = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Manager']);
        $seller            = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Seller']);
        $productionOfficer = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Production Officer']);
        $auditor           = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Auditor']);
        $client            = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Client']);

        // Create initial Super Admin User
        $adminUser = \App\Models\User::firstOrCreate(
            ['email' => 'admin@albareck.local'],
            [
                'name'      => 'Super Admin',
                'password'  => bcrypt('password'),
                'is_active' => true,
            ]
        );
        $adminUser->syncRoles([$superAdmin]);

        // Create Demo Manager User
        $managerUser = \App\Models\User::firstOrCreate(
            ['email' => 'manager@albareck.local'],
            [
                'name'      => 'Store Manager',
                'password'  => bcrypt('password'),
                'is_active' => true,
            ]
        );
        $managerUser->syncRoles([$manager]);

        // Create Demo Seller User
        $sellerUser = \App\Models\User::firstOrCreate(
            ['email' => 'seller@albareck.local'],
            [
                'name'      => 'Sales Agent',
                'password'  => bcrypt('password'),
                'is_active' => true,
            ]
        );
        $sellerUser->syncRoles([$seller]);
    }
}
