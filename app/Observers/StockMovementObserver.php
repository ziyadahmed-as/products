<?php

namespace App\Observers;

use App\Models\StockMovement;
use App\Services\ActivityLogger;

class StockMovementObserver
{
    public function created(StockMovement $movement): void
    {
        $product = $movement->product;
        $location = $movement->storageLocation;

        ActivityLogger::log(
            module: 'Inventory',
            actionType: 'stock_movement',
            description: "Stock {$movement->type}: " . number_format($movement->quantity, 2) . " units of " . ($product?->name ?? 'item') . " at " . ($location?->name ?? 'location') . ".",
            subject: $movement,
            properties: [
                'movement_id'         => $movement->id,
                'product_id'          => $movement->product_id,
                'storage_location_id' => $movement->storage_location_id,
                'branch_id'           => $location?->branch_id,
                'type'                => $movement->type,
                'quantity'            => $movement->quantity,
                'unit_cost'           => $movement->unit_cost,
                'reference_type'      => $movement->reference_type,
                'reference_id'        => $movement->reference_id,
            ]
        );
    }
}
