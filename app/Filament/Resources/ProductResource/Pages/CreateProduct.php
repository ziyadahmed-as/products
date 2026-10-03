<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\InventoryBalance;
use App\Models\StorageLocation;
use App\Models\StockMovement;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // quantity is a real column now — no need to strip it
        return $data;
    }

    protected function afterCreate(): void
    {
        $data     = $this->data;
        $quantity = (float) ($data['quantity'] ?? 0);

        if ($quantity <= 0) {
            return;
        }

        // Get branch IDs assigned to the product
        $branchIds = $this->record->branches()->pluck('branches.id');

        if ($branchIds->isEmpty()) {
            return;
        }

        // Get all active storage locations linked to those branches
        $locations = StorageLocation::whereIn('branch_id', $branchIds)
            ->where('is_active', true)
            ->get();

        foreach ($locations as $location) {
            $balance = InventoryBalance::firstOrCreate(
                [
                    'product_id'          => $this->record->id,
                    'storage_location_id' => $location->id,
                ],
                ['quantity' => 0]
            );

            $balance->increment('quantity', $quantity);

            StockMovement::create([
                'product_id'          => $this->record->id,
                'storage_location_id' => $location->id,
                'type'                => 'in',
                'quantity'            => $quantity,
                'unit_cost'           => $this->record->purchase_cost ?? 0,
                'user_id'             => auth()->id(),
                'reference_type'      => 'opening_stock',
                'reference_id'        => $this->record->id,
            ]);
        }

        // Sync the product's quantity column
        $this->record->syncQuantity();
    }
}
