<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Branch;
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
        return $data;
    }

    protected function afterCreate(): void
    {
        $data     = $this->data;
        $quantity = (float) ($data['quantity'] ?? 0);
        $user     = auth()->user();

        // 1. Multi-branch distribution mode (if enabled and provided)
        if (!empty($data['distribute_to_multiple_branches']) && !empty($data['branch_allocations'])) {
            $allocatedBranchIds = [];

            foreach ($data['branch_allocations'] as $alloc) {
                $bId      = $alloc['branch_id'] ?? null;
                $locId    = $alloc['storage_location_id'] ?? null;
                $allocQty = (float) ($alloc['quantity'] ?? 0);

                if (!$bId || !$locId || $allocQty <= 0) {
                    continue;
                }

                $allocatedBranchIds[] = $bId;

                $balance = InventoryBalance::firstOrCreate(
                    [
                        'product_id'          => $this->record->id,
                        'storage_location_id' => $locId,
                    ],
                    ['quantity' => 0]
                );

                $balance->increment('quantity', $allocQty);

                StockMovement::create([
                    'product_id'          => $this->record->id,
                    'storage_location_id' => $locId,
                    'type'                => 'in',
                    'quantity'            => $allocQty,
                    'unit_cost'           => $this->record->purchase_cost ?? 0,
                    'user_id'             => $user?->id,
                    'reference_type'      => 'opening_stock',
                    'reference_id'        => $this->record->id,
                ]);
            }

            if (!empty($allocatedBranchIds)) {
                $this->record->branches()->sync(array_values(array_unique($allocatedBranchIds)));
            }
        } else {
            // 2. Single-branch registration rule (Default)
            $defaultBranchId = $data['branch_id']
                ?? $user?->branches()->first()?->id
                ?? Branch::where('is_active', true)->first()?->id;

            if ($defaultBranchId) {
                $this->record->branches()->sync([$defaultBranchId]);
            }

            $storageLocationId = $data['storage_location_id'] ?? null;
            if (!$storageLocationId && $defaultBranchId) {
                $storageLocationId = StorageLocation::where('branch_id', $defaultBranchId)
                    ->where('is_active', true)
                    ->first()?->id;
            }

            if ($quantity > 0 && $storageLocationId) {
                $balance = InventoryBalance::firstOrCreate(
                    [
                        'product_id'          => $this->record->id,
                        'storage_location_id' => $storageLocationId,
                    ],
                    ['quantity' => 0]
                );

                $balance->increment('quantity', $quantity);

                StockMovement::create([
                    'product_id'          => $this->record->id,
                    'storage_location_id' => $storageLocationId,
                    'type'                => 'in',
                    'quantity'            => $quantity,
                    'unit_cost'           => $this->record->purchase_cost ?? 0,
                    'user_id'             => $user?->id,
                    'reference_type'      => 'opening_stock',
                    'reference_id'        => $this->record->id,
                ]);
            }
        }

        // Sync the product's denormalized quantity column
        $this->record->syncQuantity();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
