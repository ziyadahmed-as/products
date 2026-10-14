<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UnitOfMeasure;
use App\Models\Category;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // Units of Measure
        $units = [
            ['name' => 'Kilogram', 'code' => 'kg'],
            ['name' => 'Gram', 'code' => 'g'],
            ['name' => 'Liter', 'code' => 'L'],
            ['name' => 'Milliliter', 'code' => 'ml'],
            ['name' => 'Bottle', 'code' => 'btl'],
            ['name' => 'Pack / Packaging', 'code' => 'pck'],
            ['name' => 'Piece', 'code' => 'pcs'],
            ['name' => 'Bag / Sack', 'code' => 'bag'],
            ['name' => 'Can / Drum', 'code' => 'drum'],
            ['name' => 'Meter', 'code' => 'm'],
        ];

        foreach ($units as $u) {
            UnitOfMeasure::firstOrCreate(
                ['code' => $u['code']],
                ['name' => $u['name']]
            );
        }

        // Categories
        $categories = [
            ['name' => 'Raw Materials', 'description' => 'Unprocessed ingredients and chemical raw materials for manufacturing.'],
            ['name' => 'Packaging Materials', 'description' => 'Bottles, caps, labels, cartons, and packaging supplies.'],
            ['name' => 'Chemicals & Solvents', 'description' => 'Active chemicals, surfactants, and liquid solvents.'],
            ['name' => 'Manufactured Detergents', 'description' => 'Finished cleaning and detergent products produced in-house.'],
            ['name' => 'Resale Products', 'description' => 'Finished merchandise purchased for resale.'],
            ['name' => 'Consumables', 'description' => 'Factory supplies and operational consumables.'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(
                ['name' => $cat['name']],
                ['description' => $cat['description'], 'is_active' => true]
            );
        }
    }
}
