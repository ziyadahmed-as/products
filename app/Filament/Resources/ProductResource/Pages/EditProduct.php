<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\InventoryBalance;
use App\Models\StockMovement;
use App\Models\StorageLocation;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    /** Capture the old quantity before saving */
    protected float $oldQuantity = 0;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Pre-fill the quantity field from the products table
        $this->oldQuantity = (float) ($this->record->quantity ?? 0);
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Capture old before it gets overwritten
        $this->oldQuantity = (float) ($this->record->quantity ?? 0);
        return $data;
    }

    protected function afterSave(): void
    {
        $newQuantity = (float) ($this->record->quantity ?? 0);
        $diff        = $newQuantity - $this->oldQuantity;

        if ($diff == 0) {
            return; // No change — nothing to do
        }

        // Get all active storage locations for the product's branches
        $branchIds = $this->record->branches()->pluck('branches.id');
        $locations = StorageLocation::whereIn('branch_id', $branchIds)
            ->where('is_active', true)
            ->get();

        if ($locations->isEmpty()) {
            return;
        }

        foreach ($locations as $location) {
            $balance = InventoryBalance::firstOrCreate(
                [
                    'product_id'          => $this->record->id,
                    'storage_location_id' => $location->id,
                ],
                ['quantity' => 0]
            );

            if ($diff > 0) {
                // Admin increased quantity — add stock
                $balance->increment('quantity', $diff);

                StockMovement::create([
                    'product_id'          => $this->record->id,
                    'storage_location_id' => $location->id,
                    'type'                => 'in',
                    'quantity'            => $diff,
                    'unit_cost'           => $this->record->purchase_cost ?? 0,
                    'user_id'             => auth()->id(),
                    'reference_type'      => 'adjustment',
                    'reference_id'        => $this->record->id,
                ]);
            } else {
                // Admin decreased quantity — deduct stock (but not below zero)
                $deduct  = abs($diff);
                $deduct  = min($deduct, $balance->quantity);
                $balance->decrement('quantity', $deduct);

                StockMovement::create([
                    'product_id'          => $this->record->id,
                    'storage_location_id' => $location->id,
                    'type'                => 'out',
                    'quantity'            => $deduct,
                    'unit_cost'           => $this->record->purchase_cost ?? 0,
                    'user_id'             => auth()->id(),
                    'reference_type'      => 'adjustment',
                    'reference_id'        => $this->record->id,
                ]);
            }
        }

        // Sync the products.quantity column from InventoryBalance totals
        $this->record->syncQuantity();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
