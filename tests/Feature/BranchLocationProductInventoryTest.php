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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    /**
     * Test fixing the error: when empty/blank branch allocations are submitted,
     * it does NOT trigger validation failure and registers raw material successfully.
     */
    public function test_raw_material_registration_with_blank_branch_allocations_succeeds(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('products.store'), [
            'name'                        => 'Seed',
            'type'                        => 'raw_material',
            'sku'                         => 'seed-001',
            'unit_of_measure_id'          => $this->unitKg->id,
            'package_size'                => '250kg',
            'specification'               => 'High Quality',
            'purchase_cost'               => 320.00,
            'selling_price'               => '',
            'minimum_stock_level'         => 10,
            'branch_id'                   => $this->branchA->id,
            'initial_storage_location_id' => $this->locationA->id,
            'initial_quantity'            => 250.00,
            'is_active'                   => 1,
            // Simulates empty inputs from collapsed multi-branch distribution accordion
            'branch_allocations'          => [
                [
                    'branch_id'           => $this->branchA->id,
                    'storage_location_id' => $this->locationA->id,
                    'quantity'            => '', // Blank quantity left by user
                ],
                [
                    'branch_id'           => $this->branchB->id,
                    'storage_location_id' => $this->locationB->id,
                    'quantity'            => null,
                ],
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $product = Product::where('sku', 'seed-001')->first();
        $this->assertNotNull($product);
        $this->assertEquals('raw_material', $product->type);
        $this->assertNull($product->selling_price); // Raw material selling price is null
        $this->assertEquals(250.00, (float) $product->quantity);

        // Verify balance in Location A is 250
        $balanceA = InventoryBalance::where('product_id', $product->id)
            ->where('storage_location_id', $this->locationA->id)
            ->first();
        $this->assertNotNull($balanceA);
        $this->assertEquals(250.00, (float) $balanceA->quantity);
    }

    /**
     * Test separated routes for Raw Materials, Manufactured Products, and Resale Goods.
     */
    public function test_separated_item_type_routes_and_processes(): void
    {
        // 1. Raw Materials Create Page
        $resRaw = $this->actingAs($this->superAdmin)->get(route('raw-materials.create'));
        $resRaw->assertOk();
        $resRaw->assertSee('Register Raw Material');

        // 2. Manufactured Products Create Page
        $resMfg = $this->actingAs($this->superAdmin)->get(route('manufactured-products.create'));
        $resMfg->assertOk();
        $resMfg->assertSee('Register Manufactured Product');

        // 3. Resale Goods Create Page
        $resRes = $this->actingAs($this->superAdmin)->get(route('resale-products.create'));
        $resRes->assertOk();
        $resRes->assertSee('Register Resale Goods');

        // 4. Listing Filters
        $resRawIndex = $this->actingAs($this->superAdmin)->get(route('raw-materials.index'));
        $resRawIndex->assertOk();

        $resMfgIndex = $this->actingAs($this->superAdmin)->get(route('manufactured-products.index'));
        $resMfgIndex->assertOk();

        $resResIndex = $this->actingAs($this->superAdmin)->get(route('resale-products.index'));
        $resResIndex->assertOk();
    }

    /**
     * Test Image attribute is supported on Resale Goods and Manufactured Products,
     * but omitted/ignored on Raw Materials.
     */
    public function test_image_attribute_allowed_on_resale_and_manufactured_products_and_omitted_for_raw_materials(): void
    {
        Storage::fake('public');

        $imageFile = UploadedFile::fake()->image('test_product.jpg', 600, 600);

        // 1. Resale Product with Image
        $resaleResponse = $this->actingAs($this->superAdmin)->post(route('products.store'), [
            'name'                        => 'Imported Dispenser Pump',
            'type'                        => 'resale_product',
            'sku'                         => 'RES-PUMP-99',
            'unit_of_measure_id'          => $this->unitKg->id,
            'purchase_cost'               => 45.00,
            'selling_price'               => 75.00,
            'minimum_stock_level'         => 10,
            'branch_id'                   => $this->branchA->id,
            'initial_storage_location_id' => $this->locationA->id,
            'initial_quantity'            => 50,
            'image'                       => $imageFile,
        ]);

        $resaleResponse->assertSessionHasNoErrors();
        $resaleResponse->assertRedirect();

        $resaleProduct = Product::where('sku', 'RES-PUMP-99')->first();
        $this->assertNotNull($resaleProduct);
        $this->assertNotNull($resaleProduct->image);
        Storage::disk('public')->assertExists($resaleProduct->image);

        // 2. Manufactured Product with Image
        $mfgImage = UploadedFile::fake()->image('mfg_cooking_oil.png', 800, 800);
        $mfgResponse = $this->actingAs($this->superAdmin)->post(route('products.store'), [
            'name'                        => 'Pure Sesame Oil 1L',
            'type'                        => 'manufactured_product',
            'sku'                         => 'MFG-OIL-01',
            'unit_of_measure_id'          => $this->unitLiters->id,
            'purchase_cost'               => 120.00,
            'selling_price'               => 195.00,
            'minimum_stock_level'         => 20,
            'branch_id'                   => $this->branchA->id,
            'initial_storage_location_id' => $this->locationA->id,
            'initial_quantity'            => 100,
            'image'                       => $mfgImage,
        ]);

        $mfgResponse->assertSessionHasNoErrors();
        $mfgProduct = Product::where('sku', 'MFG-OIL-01')->first();
        $this->assertNotNull($mfgProduct);
        $this->assertNotNull($mfgProduct->image);
        Storage::disk('public')->assertExists($mfgProduct->image);

        // 3. Raw Material: Image is omitted / not saved even if passed
        $rawImage = UploadedFile::fake()->image('raw_material.jpg');
        $rawResponse = $this->actingAs($this->superAdmin)->post(route('products.store'), [
            'name'                        => 'Crude Seed Extract',
            'type'                        => 'raw_material',
            'sku'                         => 'RAW-EXT-01',
            'unit_of_measure_id'          => $this->unitKg->id,
            'purchase_cost'               => 80.00,
            'minimum_stock_level'         => 15,
            'branch_id'                   => $this->branchA->id,
            'initial_storage_location_id' => $this->locationA->id,
            'initial_quantity'            => 200,
            'image'                       => $rawImage,
        ]);

        $rawResponse->assertSessionHasNoErrors();
        $rawProduct = Product::where('sku', 'RAW-EXT-01')->first();
        $this->assertNotNull($rawProduct);
        $this->assertNull($rawProduct->image, 'Raw materials should not have product image stored.');

        // 4. Update Resale Product with new image replaces old file
        $oldImagePath = $resaleProduct->image;
        $newImage = UploadedFile::fake()->image('updated_pump.jpg');

        $updateResponse = $this->actingAs($this->superAdmin)->put(route('products.update', $resaleProduct), [
            'name'                => 'Imported Dispenser Pump Deluxe',
            'type'                => 'resale_product',
            'sku'                 => 'RES-PUMP-99',
            'unit_of_measure_id'  => $this->unitKg->id,
            'purchase_cost'       => 50.00,
            'selling_price'       => 85.00,
            'image'               => $newImage,
        ]);

        $updateResponse->assertSessionHasNoErrors();
        $resaleProduct->refresh();
        $this->assertNotEquals($oldImagePath, $resaleProduct->image);
        Storage::disk('public')->assertMissing($oldImagePath);
        Storage::disk('public')->assertExists($resaleProduct->image);

        // 5. Update Resale Product with remove_image checkbox removes image
        $removeResponse = $this->actingAs($this->superAdmin)->put(route('products.update', $resaleProduct), [
            'name'                => 'Imported Dispenser Pump Deluxe',
            'type'                => 'resale_product',
            'sku'                 => 'RES-PUMP-99',
            'unit_of_measure_id'  => $this->unitKg->id,
            'purchase_cost'       => 50.00,
            'selling_price'       => 85.00,
            'remove_image'        => '1',
        ]);

        $removeResponse->assertSessionHasNoErrors();
        $resaleProduct->refresh();
        $this->assertNull($resaleProduct->image);

        // 6. Verify views render the image attribute on create and edit pages
        $createResaleView = $this->actingAs($this->superAdmin)->get(route('resale-products.create'));
        $createResaleView->assertOk();
        $createResaleView->assertSee('Product Image');

        $editResaleView = $this->actingAs($this->superAdmin)->get(route('products.edit', $mfgProduct));
        $editResaleView->assertOk();
        $editResaleView->assertSee('Product Image');
    }
}
