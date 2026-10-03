<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;
use App\Models\StorageLocation;

class StorageLocationSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Branch::all() as $branch) {
            $code = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $branch->name), 0, 4)) . '-' . $branch->id;
            StorageLocation::firstOrCreate(
                ['branch_id' => $branch->id, 'code' => $code],
                ['name' => $branch->name . ' - Main Store', 'is_active' => true]
            );
        }
    }
}
