<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Models\ManufacturingOrder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminDashboardRedesignTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['Super Admin', 'Manager', 'Seller', 'Production Officer', 'Auditor', 'Client'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
    }

    public function test_super_admin_can_view_redesigned_light_dashboard_with_all_metrics(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin_test@example.com'],
            ['name' => 'Admin Test', 'password' => bcrypt('password123'), 'is_active' => true]
        );
        $admin->syncRoles(['Super Admin']);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Welcome back, Admin Test!');
        $response->assertSee('Revenue (Selected Period)');
        $response->assertSee('Orders Today');
        $response->assertSee('Pending Orders');
        $response->assertSee('Low Stock Alerts');
        $response->assertSee('Active Products');
        $response->assertSee('Production Orders');
        $response->assertSee('Revenue Trend (14 Days)');
        $response->assertSee('Orders by Type');
        $response->assertSee('Staff &amp; Operational Team Performance', false);
        $response->assertSee('Quick Reports &amp; Data Exports', false);
        $response->assertSee('revenueTrendChart');
        $response->assertSee('ordersByTypeChart');
        // Check sidebar items for Super Admin
        $response->assertSee('Administration');
        $response->assertSee('Users & Roles', false);
        $response->assertSee('Branches');
        $response->assertSee('Storage Locations');
    }

    public function test_manager_can_view_dashboard_with_manager_permissions(): void
    {
        $manager = User::firstOrCreate(
            ['email' => 'manager_test@example.com'],
            ['name' => 'Manager Test', 'password' => bcrypt('password123'), 'is_active' => true]
        );
        $manager->syncRoles(['Manager']);

        $response = $this->actingAs($manager)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Welcome back, Manager Test!');
        $response->assertSee('Revenue (Selected Period)');
        $response->assertSee('Products & Materials', false);
        $response->assertSee('Production Floor');
    }

    public function test_seller_sees_seller_command_center_on_dashboard(): void
    {
        $seller = User::firstOrCreate(
            ['email' => 'seller_test@example.com'],
            ['name' => 'Seller Test', 'password' => bcrypt('password123'), 'is_active' => true]
        );
        $seller->syncRoles(['Seller']);

        $response = $this->actingAs($seller)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Sales Command Center');
        $response->assertSee('New Quick Sale (POS)');
        $response->assertSee("Today's Sales", false);
        $response->assertSee("This Month's Sales", false);
        $response->assertSee('Sellable Products & Stock Lookup', false);
        // Seller should not see Super Admin only modules
        $response->assertDontSee('Users & Roles');
    }

    public function test_production_officer_views_manufacturing_operations_dashboard(): void
    {
        $production = User::firstOrCreate(
            ['email' => 'prod_test@example.com'],
            ['name' => 'Production Test', 'password' => bcrypt('password123'), 'is_active' => true]
        );
        $production->syncRoles(['Production Officer']);

        $response = $this->actingAs($production)->get(route('manufacturing.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Manufacturing Operations Floor');
        $response->assertSee('New Production Order');
        $response->assertSee('Raw Material Stock');
        $response->assertSee('Production Intelligence & Export Center', false);
    }

    public function test_auditor_can_view_dashboard_with_audit_trail_and_reports(): void
    {
        $auditor = User::firstOrCreate(
            ['email' => 'auditor_test@example.com'],
            ['name' => 'Auditor Test', 'password' => bcrypt('password123'), 'is_active' => true]
        );
        $auditor->syncRoles(['Auditor']);

        $response = $this->actingAs($auditor)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Welcome back, Auditor Test!');
        $response->assertSee('Recent System Activities & Audit');
        $response->assertSee('User Audit Trail');
    }

    public function test_unauthenticated_user_cannot_access_dashboard(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_seller_cannot_access_audit_reports(): void
    {
        $seller = User::firstOrCreate(
            ['email' => 'seller_sec@example.com'],
            ['name' => 'Seller Sec', 'password' => bcrypt('password123'), 'is_active' => true]
        );
        $seller->syncRoles(['Seller']);

        $response = $this->actingAs($seller)->get(route('reports.activity'));
        $response->assertStatus(403);
    }
}
