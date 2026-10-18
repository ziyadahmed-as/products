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

        $user = auth()->user();
        $authorizedBranchIds = ($user && !$user->hasRole('Super Admin')) ? $user->authorizedBranchIds() : null;

        // Locate existing inventory balance for this product in authorized branch or first balance
        $balanceQuery = InventoryBalance::where('product_id', $this->record->id);
        if ($authorizedBranchIds !== null) {
            $balanceQuery->whereHas('storageLocation', fn ($q) => $q->whereIn('branch_id', $authorizedBranchIds));
        }
        $targetBalance = $balanceQuery->first();

        if ($targetBalance) {
            $locationId = $targetBalance->storage_location_id;
        } else {
            $locQuery = StorageLocation::where('is_active', true);
            if ($authorizedBranchIds !== null) {
                $locQuery->whereIn('branch_id', $authorizedBranchIds);
            }
            $locationId = $locQuery->first()?->id;
        }

        if (!$locationId) {
            return;
        }

        $balance = InventoryBalance::firstOrCreate(
            [
                'product_id'          => $this->record->id,
                'storage_location_id' => $locationId,
            ],
            ['quantity' => 0]
        );

        if ($diff > 0) {
            // Admin increased quantity — add stock to designated storage location
            $balance->increment('quantity', $diff);

            StockMovement::create([
                'product_id'          => $this->record->id,
                'storage_location_id' => $locationId,
                'type'                => 'in',
                'quantity'            => $diff,
                'unit_cost'           => $this->record->purchase_cost ?? 0,
                'user_id'             => auth()->id(),
                'reference_type'      => 'adjustment',
                'reference_id'        => $this->record->id,
            ]);
        } else {
            // Admin decreased quantity — deduct stock from designated storage location
            $deduct = min(abs($diff), (float) $balance->quantity);
            $balance->decrement('quantity', $deduct);

            StockMovement::create([
                'product_id'          => $this->record->id,
                'storage_location_id' => $locationId,
                'type'                => 'out',
                'quantity'            => $deduct,
                'unit_cost'           => $this->record->purchase_cost ?? 0,
                'user_id'             => auth()->id(),
                'reference_type'      => 'adjustment',
                'reference_id'        => $this->record->id,
            ]);
        }

        // Sync the products.quantity column from InventoryBalance totals
        $this->record->syncQuantity();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
