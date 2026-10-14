<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'view_manufacturing',
            'create_manufacturing',
            'edit_manufacturing',
            'approve_manufacturing',
            'issue_manufacturing_materials',
            'complete_manufacturing',
            'cancel_manufacturing',
            'view_raw_materials',
            'create_raw_materials',
            'edit_raw_materials',
            'view_stock_receipts',
            'create_stock_receipts',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Create roles
        $superAdmin        = Role::firstOrCreate(['name' => 'Super Admin']);
        $manager           = Role::firstOrCreate(['name' => 'Manager']);
        $seller            = Role::firstOrCreate(['name' => 'Seller']);
        $productionOfficer = Role::firstOrCreate(['name' => 'Production Officer']);
        $auditor           = Role::firstOrCreate(['name' => 'Auditor']);
        $client            = Role::firstOrCreate(['name' => 'Client']);

        // Give permissions to roles
        $superAdmin->givePermissionTo(Permission::all());
        $manager->givePermissionTo([
            'view_manufacturing',
            'create_manufacturing',
            'edit_manufacturing',
            'approve_manufacturing',
            'issue_manufacturing_materials',
            'complete_manufacturing',
            'cancel_manufacturing',
            'view_raw_materials',
            'create_raw_materials',
            'edit_raw_materials',
            'view_stock_receipts',
            'create_stock_receipts',
        ]);
        $productionOfficer->givePermissionTo([
            'view_manufacturing',
            'create_manufacturing',
            'edit_manufacturing',
            'issue_manufacturing_materials',
            'complete_manufacturing',
            'cancel_manufacturing',
            'view_raw_materials',
            'view_stock_receipts',
        ]);

        // Create initial Super Admin User
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@albareck.local'],
            [
                'name'      => 'Super Admin',
                'password'  => bcrypt('password'),
                'is_active' => true,
            ]
        );
        $adminUser->syncRoles([$superAdmin]);

        // Create Demo Manager User
        $managerUser = User::firstOrCreate(
            ['email' => 'manager@albareck.local'],
            [
                'name'      => 'Store Manager',
                'password'  => bcrypt('password'),
                'is_active' => true,
            ]
        );
        $managerUser->syncRoles([$manager]);

        // Create Demo Seller User
        $sellerUser = User::firstOrCreate(
            ['email' => 'seller@albareck.local'],
            [
                'name'      => 'Sales Agent',
                'password'  => bcrypt('password'),
                'is_active' => true,
            ]
        );
        $sellerUser->syncRoles([$seller]);

        // Create Demo Production Officer User
        $productionUser = User::firstOrCreate(
            ['email' => 'production@albareck.local'],
            [
                'name'      => 'Production Officer',
                'password'  => bcrypt('password'),
                'is_active' => true,
            ]
        );
        $productionUser->syncRoles([$productionOfficer]);
    }
}
