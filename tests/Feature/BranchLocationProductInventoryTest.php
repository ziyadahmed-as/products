<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\InventoryBalance;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\StorageLocation;
use App\Models\UnitOfMeasure;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BranchLocationProductInventoryTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $managerBranchA;
    protected User $sellerBranchA;
    protected Branch $branchA;
    protected Branch $branchB;
    protected StorageLocation $locationA;
    protected StorageLocation $locationB;
    protected UnitOfMeasure $unitKg;
    protected UnitOfMeasure $unitLiters;
    protected UnitOfMeasure $unitPieces;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Super Admin']);
        Role::firstOrCreate(['name' => 'Manager']);
        Role::firstOrCreate(['name' => 'Seller']);

        $this->branchA = Branch::create(['name' => 'Addis Ababa Central', 'code' => 'BR-ADDIS', 'type' => 'retail', 'is_active' => true]);
        $this->branchB = Branch::create(['name' => 'Hawassa Regional', 'code' => 'BR-HAW', 'type' => 'retail', 'is_active' => true]);

        $this->locationA = StorageLocation::create(['name' => 'Main Warehouse A', 'code' => 'LOC-A1', 'branch_id' => $this->branchA->id, 'is_active' => true]);
        $this->locationB = StorageLocation::create(['name' => 'Regional Depot B', 'code' => 'LOC-B1', 'branch_id' => $this->branchB->id, 'is_active' => true]);

        $this->unitKg = UnitOfMeasure::create(['name' => 'Kilogram', 'code' => 'kg']);
        $this->unitLiters = UnitOfMeasure::create(['name' => 'Liter', 'code' => 'L']);
        $this->unitPieces = UnitOfMeasure::create(['name' => 'Pieces', 'code' => 'pcs']);

        $this->superAdmin = User::factory()->create(['name' => 'Global Admin', 'email' => 'admin@enterprise.com']);
        $this->superAdmin->assignRole('Super Admin');

        $this->managerBranchA = User::factory()->create(['name' => 'Branch Manager A', 'email' => 'mgr.a@enterprise.com']);
        $this->managerBranchA->assignRole('Manager');
        $this->managerBranchA->branches()->attach($this->branchA->id);

        $this->sellerBranchA = User::factory()->create(['name' => 'Cashier A', 'email' => 'seller.a@enterprise.com']);
        $this->sellerBranchA->assignRole('Seller');
        $this->sellerBranchA->branches()->attach($this->branchA->id);
    }

    /**
     * Requirement 1 & 4: Single-Branch Registration Rule
     * Registering a product assigns it to only one branch by default, and NEVER duplicates stock to other branches.
     */
    public function test_single_branch_product_registration_allocates_stock_only_to_designated_branch(): void
    {
        $response = $this->actingAs($this->managerBranchA)->post(route('products.store'), [
            'name'                        => 'Refined Ethiopian Sesame Seeds',
            'type'                        => 'resale_product',
            'sku'                         => 'SES-ADDIS-01',
            'unit_of_measure_id'          => $this->unitKg->id,
            'purchase_cost'               => 120.00,
            'selling_price'               => 180.00,
            'branch_id'                   => $this->branchA->id,
            'initial_storage_location_id' => $this->locationA->id,
            'initial_quantity'            => 250.00,
            'is_active'                   => 1,
        ]);

        $response->assertRedirect();

        $product = Product::where('sku', 'SES-ADDIS-01')->first();
        $this->assertNotNull($product);

        // Assigned ONLY to Branch A
        $this->assertEquals(1, $product->branches()->count());
        $this->assertTrue($product->branches->contains($this->branchA));
        $this->assertFalse($product->branches->contains($this->branchB));

        // Inventory balance strictly at Location A (250 kg), ZERO records at Location B
        $balanceA = InventoryBalance::where('product_id', $product->id)
            ->where('storage_location_id', $this->locationA->id)
            ->first();
        $this->assertNotNull($balanceA);
        $this->assertEquals(250.00, (float) $balanceA->quantity);

        $balanceB = InventoryBalance::where('product_id', $product->id)
            ->where('storage_location_id', $this->locationB->id)
            ->first();
        $this->assertNull($balanceB);

        // Product total quantity equals 250, not duplicated
        $this->assertEquals(250.00, (float) $product->quantity);

        // Stock movement recorded strictly for Location A
        $movement = StockMovement::where('product_id', $product->id)->first();
        $this->assertNotNull($movement);
        $this->assertEquals($this->locationA->id, $movement->storage_location_id);
        $this->assertEquals(250.00, (float) $movement->quantity);
        $this->assertEquals('in', $movement->type);
    }

    /**
     * Requirement 3: Multi-Branch Stock Distribution
     * When distributing across multiple branches, separate allocations are created without exceeding or duplicating.
     */
    public function test_multi_branch_distribution_allocates_separate_balances_without_duplication(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('products.store'), [
            'name'                        => 'Pure Ethiopian Cooking Oil 1L',
            'type'                        => 'manufactured_product',
            'sku'                         => 'OIL-DIST-1000',
            'unit_of_measure_id'          => $this->unitLiters->id,
            'purchase_cost'               => 85.00,
            'selling_price'               => 110.00,
            'branch_allocations'          => [
                [
                    'branch_id'           => $this->branchA->id,
                    'storage_location_id' => $this->locationA->id,
                    'quantity'            => 600.00,
                ],
                [
                    'branch_id'           => $this->branchB->id,
                    'storage_location_id' => $this->locationB->id,
                    'quantity'            => 400.00,
                ],
            ],
            'is_active'                   => 1,
        ]);

        $response->assertRedirect();

        $product = Product::where('sku', 'OIL-DIST-1000')->first();
        $this->assertNotNull($product);

        // Both branches assigned
        $this->assertEquals(2, $product->branches()->count());

        // Branch A has 600 L
        $balA = InventoryBalance::where('product_id', $product->id)
            ->where('storage_location_id', $this->locationA->id)
            ->first();
        $this->assertNotNull($balA);
        $this->assertEquals(600.00, (float) $balA->quantity);

        // Branch B has 400 L
        $balB = InventoryBalance::where('product_id', $product->id)
            ->where('storage_location_id', $this->locationB->id)
            ->first();
        $this->assertNotNull($balB);
        $this->assertEquals(400.00, (float) $balB->quantity);

        // Total product quantity is exactly 1,000 (600 + 400)
        $this->assertEquals(1000.00, (float) $product->quantity);
    }

    /**
     * Requirement 2: Unit-Based KPI Management
     * Never combine quantities from different units (kg, L, pcs) into a single misleading total.
     */
    public function test_dashboard_and_reports_group_stock_strictly_by_unit_of_measure(): void
    {
        // 1. Seed stock: 250 kg in Branch A
        $seed = Product::create([
            'name'                => 'Seed Stock',
            'type'                => 'raw_material',
            'sku'                 => 'SEED-250',
            'unit_of_measure_id'  => $this->unitKg->id,
            'purchase_cost'       => 50.00,
            'quantity'            => 250.00,
            'is_active'           => true,
        ]);
        $seed->branches()->sync([$this->branchA->id]);
        InventoryBalance::create(['product_id' => $seed->id, 'storage_location_id' => $this->locationA->id, 'quantity' => 250.00]);

        // 2. Oil stock: 180 L in Branch A
        $oil = Product::create([
            'name'                => 'Oil Stock',
            'type'                => 'manufactured_product',
            'sku'                 => 'OIL-180',
            'unit_of_measure_id'  => $this->unitLiters->id,
            'purchase_cost'       => 100.00,
            'quantity'            => 180.00,
            'is_active'           => true,
        ]);
        $oil->branches()->sync([$this->branchA->id]);
        InventoryBalance::create(['product_id' => $oil->id, 'storage_location_id' => $this->locationA->id, 'quantity' => 180.00]);

        // 3. Bottle stock: 500 pcs in Branch A
        $bottle = Product::create([
            'name'                => 'Bottle Stock',
            'type'                => 'raw_material',
            'sku'                 => 'BOT-500',
            'unit_of_measure_id'  => $this->unitPieces->id,
            'purchase_cost'       => 15.00,
            'quantity'            => 500.00,
            'is_active'           => true,
        ]);
        $bottle->branches()->sync([$this->branchA->id]);
        InventoryBalance::create(['product_id' => $bottle->id, 'storage_location_id' => $this->locationA->id, 'quantity' => 500.00]);

        // Access Dashboard as Super Admin
        $response = $this->actingAs($this->superAdmin)->get(route('dashboard'));
        $response->assertStatus(200);

        // Verify view received unit breakdown with correct unit quantities
        $response->assertViewHas('stockByUnit', function ($stockByUnit) {
            return isset($stockByUnit['kg']) && $stockByUnit['kg'] == 250.00
                && isset($stockByUnit['L']) && $stockByUnit['L'] == 180.00
                && isset($stockByUnit['pcs']) && $stockByUnit['pcs'] == 500.00;
        });

        // The dashboard must render the individual unit KPI values
        $response->assertSee('250.00');
        $response->assertSee('kg');
        $response->assertSee('180.00');
        $response->assertSee('L');
        $response->assertSee('500.00');
        $response->assertSee('pcs');

        // Access Inventory Report
        $reportResponse = $this->actingAs($this->superAdmin)->get(route('reports.inventory'));
        $reportResponse->assertStatus(200);
        $reportResponse->assertViewHas('stockByUnit', function ($stockByUnit) {
            return ($stockByUnit['kg'] ?? 0) == 250.00
                && ($stockByUnit['L'] ?? 0) == 180.00
                && ($stockByUnit['pcs'] ?? 0) == 500.00;
        });
    }

    /**
     * Requirement 4: Inter-Branch Stock Transfers
     * Deducts exact transferred quantity from source branch and adds to destination branch.
     */
    public function test_stock_transfer_deducts_from_source_and_adds_to_destination_branch(): void
    {
        // Product starts with 100 pcs in Branch A (Location A)
        $product = Product::create([
            'name'                => 'Glass Honey Jar 500g',
            'type'                => 'resale_product',
            'sku'                 => 'JAR-500G',
            'unit_of_measure_id'  => $this->unitPieces->id,
            'purchase_cost'       => 40.00,
            'selling_price'       => 65.00,
            'quantity'            => 100.00,
            'is_active'           => true,
        ]);
        $product->branches()->sync([$this->branchA->id]);

        $sourceBal = InventoryBalance::create([
            'product_id'          => $product->id,
            'storage_location_id' => $this->locationA->id,
            'quantity'            => 100.00,
        ]);

        // Transfer 30 pcs from Location A (Branch A) to Location B (Branch B)
        $transfer = StockTransfer::create([
            'reference'        => 'TRF-TEST-001',
            'from_location_id' => $this->locationA->id,
            'to_location_id'   => $this->locationB->id,
            'status'           => 'pending',
            'dispatch_date'    => today(),
            'user_id'          => $this->superAdmin->id,
        ]);

        $item = StockTransferItem::create([
            'stock_transfer_id'   => $transfer->id,
            'product_id'          => $product->id,
            'dispatched_quantity' => 30.00,
            'received_quantity'   => 30.00,
        ]);

        // Complete the transfer
        $transfer->update(['status' => 'completed']);

        // Verify source Location A decremented by 30 (100 - 30 = 70)
        $sourceBal->refresh();
        $this->assertEquals(70.00, (float) $sourceBal->quantity);

        // Verify destination Location B incremented by 30
        $destBal = InventoryBalance::where('product_id', $product->id)
            ->where('storage_location_id', $this->locationB->id)
            ->first();
        $this->assertNotNull($destBal);
        $this->assertEquals(30.00, (float) $destBal->quantity);

        // Verify Destination branch is now attached to product
        $this->assertTrue($product->fresh()->branches->contains($this->branchB));

        // Audit StockMovements created
        $outMovement = StockMovement::where('product_id', $product->id)
            ->where('storage_location_id', $this->locationA->id)
            ->where('type', 'out')
            ->first();
        $this->assertNotNull($outMovement);
        $this->assertEquals(30.00, (float) $outMovement->quantity);
        $this->assertEquals('transfer_out', $outMovement->reference_type);

        $inMovement = StockMovement::where('product_id', $product->id)
            ->where('storage_location_id', $this->locationB->id)
            ->where('type', 'in')
            ->first();
        $this->assertNotNull($inMovement);
        $this->assertEquals(30.00, (float) $inMovement->quantity);
        $this->assertEquals('transfer_in', $inMovement->reference_type);

        // Product total quantity remains preserved (70 + 30 = 100)
        $this->assertEquals(100.00, (float) $product->fresh()->quantity);
    }
}
