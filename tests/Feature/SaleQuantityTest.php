<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleLine;
use App\Models\User;
use App\Models\StorageLocation;
use App\Models\InventoryBalance;
use App\Filament\Resources\SaleResource;
use App\Filament\Resources\SaleResource\Pages\ListSales;
use Livewire\Livewire;
use Database\Seeders\DatabaseSeeder;

class SaleQuantityTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_quantity_is_minimized_during_sale_confirmation()
    {
        // Setup initial data
        $user = User::factory()->create();
        $this->actingAs($user);

        $branch = \App\Models\Branch::create([
            'name' => 'Main Branch',
            'code' => 'B-01',
            'is_active' => true,
        ]);

        $location = StorageLocation::create([
            'name' => 'Main Warehouse',
            'code' => 'WH-01',
            'branch_id' => $branch->id,
            'is_active' => true
        ]);
        
        $unit = \App\Models\UnitOfMeasure::create([
            'name' => 'Pieces',
            'code' => 'PCS',
        ]);

        $category = \App\Models\Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'is_active' => true,
        ]);

        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TEST-01',
            'type' => 'resale_product',
            'quantity' => 100,
            'category_id' => $category->id,
            'unit_of_measure_id' => $unit->id,
        ]);
        
        InventoryBalance::create([
            'product_id' => $product->id,
            'storage_location_id' => $location->id,
            'quantity' => 100,
        ]);

        $sale = Sale::create([
            'reference' => 'ORD-TEST',
            'type' => 'pos',
            'status' => 'pending',
            'payment_status' => 'pending',
            'storage_location_id' => $location->id,
            'user_id' => $user->id,
        ]);
        
        SaleLine::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 20,
            'unit_price' => 10,
            'total' => 200,
        ]);

        // Assert starting quantity is 100
        $this->assertEquals(100, $product->fresh()->quantity);

        // Call the table action 'confirm_and_deduct'
        Livewire::test(ListSales::class)
            ->callTableAction('confirm_and_deduct', $sale);

        // Assert sale is confirmed
        $this->assertEquals('confirmed', $sale->fresh()->status);

        // Assert inventory balance is decreased
        $balance = InventoryBalance::where('product_id', $product->id)
            ->where('storage_location_id', $location->id)
            ->first();
        $this->assertEquals(80, $balance->quantity);

        // Assert product quantity is decreased
        $this->assertEquals(80, $product->fresh()->quantity);
    }
}

