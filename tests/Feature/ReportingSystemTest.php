<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Branch;
use App\Models\StorageLocation;
use App\Models\Product;
use App\Models\Category;
use App\Models\Sale;
use App\Models\SaleLine;
use App\Models\ManufacturingOrder;
use App\Models\InventoryBalance;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReportingSystemTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles if not present
        foreach (['Super Admin', 'Manager', 'Seller', 'Production Officer', 'Auditor', 'Client'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
    }

    public function test_super_admin_can_access_all_reports()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');

        $this->actingAs($admin)->get(route('reports.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('reports.sales'))->assertStatus(200);
        $this->actingAs($admin)->get(route('reports.inventory'))->assertStatus(200);
        $this->actingAs($admin)->get(route('reports.production'))->assertStatus(200);
        $this->actingAs($admin)->get(route('reports.branches'))->assertStatus(200);
        $this->actingAs($admin)->get(route('reports.transfers'))->assertStatus(200);
        $this->actingAs($admin)->get(route('reports.activity'))->assertStatus(200);
    }

    public function test_seller_is_restricted_to_own_sales_scope()
    {
        $branch = Branch::create(['name' => 'Addis Branch', 'code' => 'ADD-1', 'is_active' => true]);
        $location = StorageLocation::create(['branch_id' => $branch->id, 'name' => 'Main Addis Store', 'code' => 'ADD-MAIN', 'is_active' => true]);

        $seller1 = User::factory()->create(['name' => 'Seller One']);
        $seller1->assignRole('Seller');
        $seller1->branches()->attach($branch->id);

        $seller2 = User::factory()->create(['name' => 'Seller Two']);
        $seller2->assignRole('Seller');
        $seller2->branches()->attach($branch->id);

        $product = Product::create([
            'name'                => 'Albareck Detergent 5L',
            'sku'                 => 'DET-5L-TEST',
            'type'                => 'resale_product',
            'selling_price'       => 250.00,
            'purchase_cost'       => 180.00,
            'minimum_stock_level' => 10,
            'is_active'           => true,
        ]);

        // Sale by seller 1
        $sale1 = Sale::create([
            'reference'           => 'INV-SELL-001',
            'type'                => 'quick_sale',
            'user_id'             => $seller1->id,
            'branch_id'           => $branch->id,
            'storage_location_id' => $location->id,
            'customer_name'       => 'Walk-in 1',
            'total'               => 500.00,
            'paid_amount'         => 500.00,
            'payment_status'      => 'Paid',
            'status'              => 'Completed',
        ]);

        // Sale by seller 2
        $sale2 = Sale::create([
            'reference'           => 'INV-SELL-002',
            'type'                => 'quick_sale',
            'user_id'             => $seller2->id,
            'branch_id'           => $branch->id,
            'storage_location_id' => $location->id,
            'customer_name'       => 'Walk-in 2',
            'total'               => 1000.00,
            'paid_amount'         => 1000.00,
            'payment_status'      => 'Paid',
            'status'              => 'Completed',
        ]);

        // Seller 1 viewing sales report should see only their own sale
        $response = $this->actingAs($seller1)->get(route('reports.sales'));
        $response->assertStatus(200);
        $response->assertSee('INV-SELL-001');
        $response->assertDontSee('INV-SELL-002');

        // Seller cannot access branch performance or system activities
        $this->actingAs($seller1)->get(route('reports.branches'))->assertStatus(403);
        $this->actingAs($seller1)->get(route('reports.activity'))->assertStatus(403);
    }

    public function test_manager_access_is_scoped_to_authorized_branches()
    {
        $branch1 = Branch::create(['name' => 'Hawassa Branch', 'code' => 'HAW-1', 'is_active' => true]);
        $branch2 = Branch::create(['name' => 'Dire Dawa Branch', 'code' => 'DIR-1', 'is_active' => true]);

        $manager = User::factory()->create(['name' => 'Hawassa Manager']);
        $manager->assignRole('Manager');
        $manager->branches()->attach($branch1->id);

        $loc1 = StorageLocation::create(['branch_id' => $branch1->id, 'name' => 'Hawassa Store', 'code' => 'HAW-STORE', 'is_active' => true]);
        $loc2 = StorageLocation::create(['branch_id' => $branch2->id, 'name' => 'Dire Store', 'code' => 'DIR-STORE', 'is_active' => true]);

        Sale::create([
            'reference'           => 'INV-HAW-001',
            'type'                => 'quick_sale',
            'branch_id'           => $branch1->id,
            'storage_location_id' => $loc1->id,
            'total'               => 750.00,
            'paid_amount'         => 750.00,
            'payment_status'      => 'Paid',
            'status'              => 'Completed',
        ]);

        Sale::create([
            'reference'           => 'INV-DIR-002',
            'type'                => 'quick_sale',
            'branch_id'           => $branch2->id,
            'storage_location_id' => $loc2->id,
            'total'               => 1500.00,
            'paid_amount'         => 1500.00,
            'payment_status'      => 'Paid',
            'status'              => 'Completed',
        ]);

        // Manager should see Hawassa sale but not Dire Dawa sale
        $response = $this->actingAs($manager)->get(route('reports.sales'));
        $response->assertStatus(200);
        $response->assertSee('INV-HAW-001');
        $response->assertDontSee('INV-DIR-002');
    }

    public function test_client_portal_reporting_and_isolation()
    {
        $client1 = User::factory()->create(['name' => 'Client One', 'email' => 'client1@albareck.com']);
        $client1->assignRole('Client');

        $client2 = User::factory()->create(['name' => 'Client Two', 'email' => 'client2@albareck.com']);
        $client2->assignRole('Client');

        $branch = Branch::create(['name' => 'Online Hub', 'code' => 'HUB-1', 'is_active' => true]);
        $location = StorageLocation::create(['branch_id' => $branch->id, 'name' => 'Fulfillment Center', 'code' => 'FULFILL', 'is_active' => true]);

        Sale::create([
            'reference'           => 'ORD-CLI-001',
            'type'                => 'customer_order',
            'client_id'           => $client1->id,
            'branch_id'           => $branch->id,
            'storage_location_id' => $location->id,
            'customer_name'       => 'Client One',
            'customer_email'      => 'client1@albareck.com',
            'total'               => 1200.00,
            'paid_amount'         => 1200.00,
            'payment_status'      => 'Paid',
            'status'              => 'Completed',
        ]);

        Sale::create([
            'reference'           => 'ORD-CLI-002',
            'type'                => 'customer_order',
            'client_id'           => $client2->id,
            'branch_id'           => $branch->id,
            'storage_location_id' => $location->id,
            'customer_name'       => 'Client Two',
            'customer_email'      => 'client2@albareck.com',
            'total'               => 2400.00,
            'paid_amount'         => 0.00,
            'payment_status'      => 'Unpaid',
            'status'              => 'Pending',
        ]);

        // Client 1 can access client reports and sees only their order
        $response = $this->actingAs($client1)->get(route('client.reports'));
        $response->assertStatus(200);
        $response->assertSee('ORD-CLI-001');
        $response->assertDontSee('ORD-CLI-002');

        // Client 1 cannot access internal reports
        $this->actingAs($client1)->get(route('reports.activity'))->assertStatus(403);
    }

    public function test_csv_export_returns_streamed_file_with_valid_headers()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');

        $response = $this->actingAs($admin)->get(route('reports.sales.export'));
        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
        $this->assertStringContainsString('attachment; filename="sales_report_', $response->headers->get('content-disposition'));
    }
}
