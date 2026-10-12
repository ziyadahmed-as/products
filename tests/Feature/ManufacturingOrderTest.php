<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Category;
use App\Models\InventoryBalance;
use App\Models\ManufacturingOrder;
use App\Models\MaterialIssue;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\RecipeMaterial;
use App\Models\StockMovement;
use App\Models\StorageLocation;
use App\Models\UnitOfMeasure;
use App\Models\User;
use App\Services\ManufacturingService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class ManufacturingOrderTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $productionUser;
    protected User $sellerUser;
    protected Branch $branch;
    protected StorageLocation $location;
    protected UnitOfMeasure $unitKg;
    protected UnitOfMeasure $unitBottle;
    protected Product $rawSeed;
    protected Product $rawBottle;
    protected Product $finishedOil;
    protected Recipe $recipe;
    protected ManufacturingService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->adminUser = User::where('email', 'admin@albareck.local')->first();
        $this->productionUser = User::where('email', 'production@albareck.local')->first();
        $this->sellerUser = User::where('email', 'seller@albareck.local')->first();

        $this->service = app(ManufacturingService::class);

        $this->branch = Branch::create([
            'name'      => 'Hawassa Factory',
            'code'      => 'FAC-01',
            'is_active' => true,
        ]);

        $this->sellerUser->branches()->attach($this->branch->id);

        $this->location = StorageLocation::create([
            'name'      => 'Factory Central Store',
            'code'      => 'LOC-01',
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);

        $this->unitKg = UnitOfMeasure::create(['name' => 'Kilogram', 'code' => 'kg']);
        $this->unitBottle = UnitOfMeasure::create(['name' => 'Bottle', 'code' => 'btl']);

        $rawCategory = Category::create(['name' => 'Raw Materials', 'slug' => 'raw-materials', 'is_active' => true]);
        $oilCategory = Category::create(['name' => 'Edible Oils', 'slug' => 'edible-oils', 'is_active' => true]);

        // Raw Material 1: Sunflower Seeds
        $this->rawSeed = Product::create([
            'name'                => 'Sunflower Seeds Grade A',
            'sku'                 => 'RAW-SEED-01',
            'type'                => 'raw_material',
            'category_id'         => $rawCategory->id,
            'unit_of_measure_id'  => $this->unitKg->id,
            'purchase_cost'       => 50.00,
            'quantity'            => 500.00,
            'minimum_stock_level' => 100.00,
            'is_active'           => true,
        ]);
        InventoryBalance::create([
            'product_id'          => $this->rawSeed->id,
            'storage_location_id' => $this->location->id,
            'quantity'            => 500.00,
        ]);

        // Raw Material 2: 1L Plastic Bottles
        $this->rawBottle = Product::create([
            'name'                => 'Empty 1L PET Bottle',
            'sku'                 => 'RAW-BTL-01',
            'type'                => 'raw_material',
            'category_id'         => $rawCategory->id,
            'unit_of_measure_id'  => $this->unitBottle->id,
            'purchase_cost'       => 10.00,
            'quantity'            => 200.00,
            'minimum_stock_level' => 50.00,
            'is_active'           => true,
        ]);
        InventoryBalance::create([
            'product_id'          => $this->rawBottle->id,
            'storage_location_id' => $this->location->id,
            'quantity'            => 200.00,
        ]);

        // Finished Product: Bottled Sunflower Oil 1L
        $this->finishedOil = Product::create([
            'name'                => 'Pure Sunflower Oil 1L',
            'sku'                 => 'FIN-OIL-01',
            'type'                => 'manufactured_product',
            'category_id'         => $oilCategory->id,
            'unit_of_measure_id'  => $this->unitBottle->id,
            'selling_price'       => 250.00,
            'purchase_cost'       => 150.00,
            'quantity'            => 0.00,
            'is_active'           => true,
        ]);
        $this->finishedOil->branches()->attach($this->branch->id);

        // Recipe: 1 bottle of oil requires 2.5 kg seeds + 1 empty bottle
        $this->recipe = Recipe::create([
            'product_id' => $this->finishedOil->id,
            'name'       => 'Standard 1L Sunflower Oil Recipe',
            'is_active'  => true,
        ]);
        RecipeMaterial::create([
            'recipe_id'       => $this->recipe->id,
            'raw_material_id' => $this->rawSeed->id,
            'quantity'        => 2.5, // 2.5 kg per bottle
        ]);
        RecipeMaterial::create([
            'recipe_id'       => $this->recipe->id,
            'raw_material_id' => $this->rawBottle->id,
            'quantity'        => 1.0, // 1 bottle per unit
        ]);
    }

    /**
     * 1. Create a manufacturing order using existing raw materials.
     */
    public function test_1_create_manufacturing_order_using_existing_raw_materials(): void
    {
        $this->actingAs($this->productionUser);

        $order = $this->service->createOrder([
            'product_id'          => $this->finishedOil->id,
            'recipe_id'           => $this->recipe->id,
            'planned_quantity'    => 100, // 100 bottles
            'storage_location_id' => $this->location->id,
            'planned_date'        => now()->toDateString(),
            'assigned_user_id'    => $this->productionUser->id,
        ], $this->productionUser);

        $this->assertNotNull($order->id);
        $this->assertStringStartsWith('MO-', $order->reference);
        $this->assertEquals(100, $order->planned_quantity);
        $this->assertEquals('draft', strtolower($order->status));
        $this->assertCount(2, $order->materialIssues);

        // Assert planned material quantities: 100 * 2.5 = 250kg seeds, 100 * 1 = 100 bottles
        $seedIssue = $order->materialIssues->where('product_id', $this->rawSeed->id)->first();
        $btlIssue = $order->materialIssues->where('product_id', $this->rawBottle->id)->first();

        $this->assertEquals(250.0, $seedIssue->quantity_planned);
        $this->assertEquals(100.0, $btlIssue->quantity_planned);
    }

    /**
     * 2. Reject material issuance when available stock is insufficient.
     */
    public function test_2_reject_material_issuance_when_available_stock_is_insufficient(): void
    {
        $this->actingAs($this->productionUser);

        // Planned for 500 bottles: requires 1250kg seeds, but only 500kg available in inventory
        $order = $this->service->createOrder([
            'product_id'          => $this->finishedOil->id,
            'recipe_id'           => $this->recipe->id,
            'planned_quantity'    => 500,
            'storage_location_id' => $this->location->id,
            'planned_date'        => now()->toDateString(),
        ], $this->productionUser);

        $this->service->approveOrder($order, $this->adminUser);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/Insufficient stock/i');

        $this->service->issueMaterials($order, $this->productionUser);
    }

    /**
     * 3. Issue materials and verify that the correct quantities are deducted.
     */
    public function test_3_issue_materials_and_verify_correct_quantities_deducted(): void
    {
        $this->actingAs($this->productionUser);

        // 100 bottles: requires 250kg seeds and 100 empty bottles
        $order = $this->service->createOrder([
            'product_id'          => $this->finishedOil->id,
            'recipe_id'           => $this->recipe->id,
            'planned_quantity'    => 100,
            'storage_location_id' => $this->location->id,
            'planned_date'        => now()->toDateString(),
        ], $this->productionUser);

        $this->service->approveOrder($order, $this->adminUser);
        $this->service->issueMaterials($order, $this->productionUser);

        $order->refresh();
        $this->assertTrue($order->is_materials_issued);
        $this->assertEquals('materials_issued', $order->status);

        // Assert seeds inventory deducted: 500 - 250 = 250
        $seedBalance = InventoryBalance::where('product_id', $this->rawSeed->id)
            ->where('storage_location_id', $this->location->id)->first();
        $this->assertEquals(250.0, (float) $seedBalance->quantity);
        $this->assertEquals(250.0, (float) $this->rawSeed->fresh()->quantity);

        // Assert bottles inventory deducted: 200 - 100 = 100
        $bottleBalance = InventoryBalance::where('product_id', $this->rawBottle->id)
            ->where('storage_location_id', $this->location->id)->first();
        $this->assertEquals(100.0, (float) $bottleBalance->quantity);
        $this->assertEquals(100.0, (float) $this->rawBottle->fresh()->quantity);

        // Assert StockMovement 'out' records created with reference
        $movements = StockMovement::where('reference_type', ManufacturingOrder::class)
            ->where('reference_id', $order->id)
            ->where('type', 'out')
            ->get();

        $this->assertCount(2, $movements);
        $this->assertEquals(250.0, (float) $movements->where('product_id', $this->rawSeed->id)->first()->quantity);
        $this->assertEquals(100.0, (float) $movements->where('product_id', $this->rawBottle->id)->first()->quantity);
    }

    /**
     * 4. Record actual consumption and return unused materials where applicable.
     */
    public function test_4_record_actual_consumption_and_return_unused_materials(): void
    {
        $this->actingAs($this->productionUser);

        $order = $this->service->createOrder([
            'product_id'          => $this->finishedOil->id,
            'recipe_id'           => $this->recipe->id,
            'planned_quantity'    => 100,
            'storage_location_id' => $this->location->id,
            'planned_date'        => now()->toDateString(),
        ], $this->productionUser);

        $this->service->approveOrder($order, $this->adminUser);
        $this->service->issueMaterials($order, $this->productionUser);
        $this->service->startProduction($order, $this->productionUser);

        $seedIssue = $order->materialIssues->where('product_id', $this->rawSeed->id)->first();
        $btlIssue = $order->materialIssues->where('product_id', $this->rawBottle->id)->first();

        // 10 unused bottles were returned back to inventory
        $completionData = [
            'good_quantity'     => 90,
            'rejected_quantity' => 0,
            'materials'         => [
                $seedIssue->id => [
                    'quantity_consumed' => 250,
                    'quantity_returned' => 0,
                    'quantity_wasted'   => 0,
                ],
                $btlIssue->id => [
                    'quantity_consumed' => 90,
                    'quantity_returned' => 10, // 10 unused returned
                    'quantity_wasted'   => 0,
                ],
            ],
        ];

        $this->service->completeProduction($order, $completionData, $this->productionUser);

        // Bottle inventory was 100 after issuance; now +10 returned = 110
        $bottleBalance = InventoryBalance::where('product_id', $this->rawBottle->id)
            ->where('storage_location_id', $this->location->id)->first();
        $this->assertEquals(110.0, (float) $bottleBalance->quantity);
        $this->assertEquals(110.0, (float) $this->rawBottle->fresh()->quantity);

        // Assert StockMovement 'in' recorded for returned material
        $returnMovement = StockMovement::where('reference_type', ManufacturingOrder::class)
            ->where('reference_id', $order->id)
            ->where('product_id', $this->rawBottle->id)
            ->where('type', 'in')
            ->first();

        $this->assertNotNull($returnMovement);
        $this->assertEquals(10.0, (float) $returnMovement->quantity);
    }

    /**
     * 5. Complete production and register only the accepted finished quantity.
     * Example: If 100 planned, 96 pass quality checks, register 96, record 4 rejected.
     */
    public function test_5_complete_production_and_register_only_accepted_finished_quantity(): void
    {
        $this->actingAs($this->productionUser);

        $order = $this->service->createOrder([
            'product_id'          => $this->finishedOil->id,
            'recipe_id'           => $this->recipe->id,
            'planned_quantity'    => 100,
            'storage_location_id' => $this->location->id,
            'planned_date'        => now()->toDateString(),
        ], $this->productionUser);

        $this->service->approveOrder($order, $this->adminUser);
        $this->service->issueMaterials($order, $this->productionUser);
        $this->service->startProduction($order, $this->productionUser);

        $this->service->completeProduction($order, [
            'good_quantity'     => 96,
            'rejected_quantity' => 4,
            'wastage_reason'    => '4 bottles failed seal inspection',
        ], $this->productionUser);

        $order->refresh();
        $this->assertEquals('completed', $order->status);
        $this->assertEquals(96, $order->good_quantity);
        $this->assertEquals(4, $order->rejected_quantity);

        // Finished product inventory must ONLY increase by 96, NOT 100!
        $oilBalance = InventoryBalance::where('product_id', $this->finishedOil->id)
            ->where('storage_location_id', $this->location->id)->first();
        $this->assertEquals(96.0, (float) $oilBalance->quantity);
        $this->assertEquals(96.0, (float) $this->finishedOil->fresh()->quantity);
    }

    /**
     * 6. Verify that finished-product stock increases correctly.
     */
    public function test_6_verify_finished_product_stock_increases_correctly(): void
    {
        $this->actingAs($this->productionUser);

        // Initially 0
        $this->assertEquals(0, (float) $this->finishedOil->quantity);

        $order = $this->service->createOrder([
            'product_id'          => $this->finishedOil->id,
            'recipe_id'           => $this->recipe->id,
            'planned_quantity'    => 50,
            'storage_location_id' => $this->location->id,
            'planned_date'        => now()->toDateString(),
        ], $this->productionUser);

        $this->service->approveOrder($order, $this->adminUser);
        $this->service->issueMaterials($order, $this->productionUser);
        $this->service->completeProduction($order, [
            'good_quantity'     => 50,
            'rejected_quantity' => 0,
        ], $this->productionUser);

        $this->assertEquals(50.0, (float) $this->finishedOil->fresh()->quantity);

        // Verify finished product StockMovement
        $finishedMovement = StockMovement::where('reference_type', ManufacturingOrder::class)
            ->where('reference_id', $order->id)
            ->where('product_id', $this->finishedOil->id)
            ->where('type', 'in')
            ->first();

        $this->assertNotNull($finishedMovement);
        $this->assertEquals(50.0, (float) $finishedMovement->quantity);
    }

    /**
     * 7. Confirm that repeated submissions cannot deduct or add stock twice.
     */
    public function test_7_confirm_repeated_submissions_cannot_deduct_or_add_stock_twice(): void
    {
        $this->actingAs($this->productionUser);

        $order = $this->service->createOrder([
            'product_id'          => $this->finishedOil->id,
            'recipe_id'           => $this->recipe->id,
            'planned_quantity'    => 100,
            'storage_location_id' => $this->location->id,
            'planned_date'        => now()->toDateString(),
        ], $this->productionUser);

        $this->service->approveOrder($order, $this->adminUser);
        $this->service->issueMaterials($order, $this->productionUser);

        // Attempting to issue materials a second time must throw RuntimeException
        try {
            $this->service->issueMaterials($order, $this->productionUser);
            $this->fail("Should not allow duplicate material issuance.");
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('already been issued', $e->getMessage());
        }

        // Seed balance must have been deducted only once (500 - 250 = 250)
        $this->assertEquals(250.0, (float) $this->rawSeed->fresh()->quantity);

        // Complete once
        $this->service->completeProduction($order, ['good_quantity' => 95, 'rejected_quantity' => 5], $this->productionUser);
        $this->assertEquals(95.0, (float) $this->finishedOil->fresh()->quantity);

        // Attempting to complete a second time must throw RuntimeException
        try {
            $this->service->completeProduction($order, ['good_quantity' => 95, 'rejected_quantity' => 5], $this->productionUser);
            $this->fail("Should not allow duplicate completion.");
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('already been registered', $e->getMessage());
        }

        // Finished stock must remain 95, NOT 190!
        $this->assertEquals(95.0, (float) $this->finishedOil->fresh()->quantity);
    }

    /**
     * 8. Verify cancellation, stock return, and reversal rules.
     */
    public function test_8_verify_cancellation_stock_return_and_reversal_rules(): void
    {
        $this->actingAs($this->productionUser);

        $order = $this->service->createOrder([
            'product_id'          => $this->finishedOil->id,
            'recipe_id'           => $this->recipe->id,
            'planned_quantity'    => 100,
            'storage_location_id' => $this->location->id,
            'planned_date'        => now()->toDateString(),
        ], $this->productionUser);

        $this->service->approveOrder($order, $this->adminUser);
        $this->service->issueMaterials($order, $this->productionUser);

        // Stock was deducted: Seeds from 500 to 250, Bottles from 200 to 100
        $this->assertEquals(250.0, (float) $this->rawSeed->fresh()->quantity);
        $this->assertEquals(100.0, (float) $this->rawBottle->fresh()->quantity);

        // Cancel order after materials issued
        $this->service->cancelOrder($order, 'Power outage halted production', $this->productionUser);

        $order->refresh();
        $this->assertEquals('cancelled', $order->status);

        // Stock must be fully restored! Seeds back to 500, Bottles back to 200
        $this->assertEquals(500.0, (float) $this->rawSeed->fresh()->quantity);
        $this->assertEquals(200.0, (float) $this->rawBottle->fresh()->quantity);

        // Reversal stock movements recorded with type 'in'
        $reversalMovements = StockMovement::where('reference_type', ManufacturingOrder::class)
            ->where('reference_id', $order->id)
            ->where('type', 'in')
            ->get();

        $this->assertCount(2, $reversalMovements);
    }

    /**
     * 9. Confirm that sellers can access eligible finished products through the existing sales workflow.
     */
    public function test_9_confirm_sellers_can_access_eligible_finished_products_through_sales_workflow(): void
    {
        $this->actingAs($this->productionUser);

        // Produce 80 bottles of finished oil
        $order = $this->service->createOrder([
            'product_id'          => $this->finishedOil->id,
            'recipe_id'           => $this->recipe->id,
            'planned_quantity'    => 80,
            'storage_location_id' => $this->location->id,
            'planned_date'        => now()->toDateString(),
        ], $this->productionUser);

        $this->service->approveOrder($order, $this->adminUser);
        $this->service->issueMaterials($order, $this->productionUser);
        $this->service->completeProduction($order, ['good_quantity' => 80], $this->productionUser);

        // Switch to seller
        $this->actingAs($this->sellerUser);

        // Assert seller sees finished oil with available quantity 80
        $sellerProducts = Product::whereHas('branches', fn ($q) => $q->where('branches.id', $this->branch->id))
            ->whereIn('type', ['resale_product', 'manufactured_product'])
            ->where('is_active', true)
            ->get();

        $this->assertTrue($sellerProducts->contains('id', $this->finishedOil->id));
        $this->assertEquals(80.0, (float) $this->finishedOil->fresh()->quantity);

        // Assert raw materials are NOT accessible to sellers
        $this->assertFalse($sellerProducts->contains('id', $this->rawSeed->id));
        $this->assertFalse($sellerProducts->contains('id', $this->rawBottle->id));
    }

    /**
     * 10. Verify that all stock movements and manufacturing records remain available in the history.
     */
    public function test_10_verify_all_stock_movements_and_manufacturing_records_remain_in_history(): void
    {
        $this->actingAs($this->productionUser);

        $order = $this->service->createOrder([
            'product_id'          => $this->finishedOil->id,
            'recipe_id'           => $this->recipe->id,
            'planned_quantity'    => 100,
            'storage_location_id' => $this->location->id,
            'planned_date'        => now()->toDateString(),
            'additional_costs'    => 500.00,
        ], $this->productionUser);

        $this->service->approveOrder($order, $this->adminUser);
        $this->service->issueMaterials($order, $this->productionUser);
        $this->service->completeProduction($order, [
            'good_quantity'     => 98,
            'rejected_quantity' => 2,
            'production_notes'  => 'Batch test 10 completed',
        ], $this->productionUser);

        // Fetch complete history
        $persistedOrder = ManufacturingOrder::with(['stockMovements', 'materialIssues.product', 'user'])->find($order->id);

        $this->assertNotNull($persistedOrder);
        $this->assertEquals('completed', $persistedOrder->status);
        $this->assertEquals(98, $persistedOrder->good_quantity);
        $this->assertEquals(2, $persistedOrder->rejected_quantity);

        // Check stock movement records (2 out for materials, 1 in for finished product = 3 total)
        $this->assertCount(3, $persistedOrder->stockMovements);
        $this->assertEquals(2, $persistedOrder->stockMovements->where('type', 'out')->count());
        $this->assertEquals(1, $persistedOrder->stockMovements->where('type', 'in')->count());

        // Check cost calculations
        $this->assertGreaterThan(0, $persistedOrder->total_cost);
    }

    /**
     * Test HTTP Manufacturing Dashboard endpoint and data rendering.
     */
    public function test_11_manufacturing_dashboard_renders_successfully(): void
    {
        $this->actingAs($this->productionUser);

        // Create an active order so it displays on the dashboard
        $this->service->createOrder([
            'product_id'          => $this->finishedOil->id,
            'recipe_id'           => $this->recipe->id,
            'planned_quantity'    => 50,
            'storage_location_id' => $this->location->id,
            'planned_date'        => now()->toDateString(),
        ], $this->productionUser);

        $response = $this->get(route('manufacturing.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Manufacturing Operations Dashboard');
        $response->assertSee('Sunflower Seeds Grade A');
        $response->assertSee('Pure Sunflower Oil 1L');
    }

    /**
     * Test HTTP Controller web order creation, issuance, and completion endpoints.
     */
    public function test_12_manufacturing_order_web_workflow(): void
    {
        $this->actingAs($this->productionUser);

        // 1. Create order via Web POST
        $createResponse = $this->post(route('manufacturing-orders.store'), [
            'product_id'          => $this->finishedOil->id,
            'recipe_id'           => $this->recipe->id,
            'planned_quantity'    => 40,
            'storage_location_id' => $this->location->id,
            'planned_date'        => now()->toDateString(),
            'assigned_user_id'    => $this->productionUser->id,
        ]);

        $order = ManufacturingOrder::latest()->first();
        $createResponse->assertRedirect(route('manufacturing-orders.show', $order));

        // 2. View Order Detail page
        $showResponse = $this->get(route('manufacturing-orders.show', $order));
        $showResponse->assertStatus(200);
        $showResponse->assertSee($order->reference);

        // 3. Approve via Web POST
        $this->post(route('manufacturing-orders.approve', $order))->assertRedirect();
        $this->assertEquals('approved', $order->fresh()->status);

        // 4. Issue materials via Web POST
        $this->post(route('manufacturing-orders.issue-materials', $order))->assertRedirect();
        $this->assertTrue($order->fresh()->is_materials_issued);

        // 5. Start Production via Web POST
        $this->post(route('manufacturing-orders.start-production', $order))->assertRedirect();
        $this->assertEquals('in_progress', $order->fresh()->status);

        // 6. Complete Production via Web POST
        $this->post(route('manufacturing-orders.complete', $order), [
            'good_quantity'     => 38,
            'rejected_quantity' => 2,
        ])->assertRedirect();

        $this->assertEquals('completed', $order->fresh()->status);
        $this->assertEquals(38, $order->fresh()->good_quantity);
        $this->assertEquals(38.0, (float) $this->finishedOil->fresh()->quantity);
    }
}

