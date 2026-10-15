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
            ['name' => 'packing materials', 'description' => 'Packing materials for products.'],
            ['name' => 'bottles', 'description' => 'bottles for oils.'],
            ['name'=> 'resallable products', 'description' => 'products that are sold as they are.'],
            ['name'=> 'seeds', 'description' => 'seeds.'],
            
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(
                ['name' => $cat['name']],
                ['description' => $cat['description'], 'is_active' => true]
            );
        }
    }
}
