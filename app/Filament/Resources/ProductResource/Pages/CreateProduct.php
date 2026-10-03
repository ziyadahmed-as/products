<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\InventoryBalance;
use App\Models\StockMovement;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function afterCreate(): void
    {
        $data = $this->data;

        $locationId = $data['initial_location_id'] ?? null;
        $quantity   = (float) ($data['initial_quantity'] ?? 0);

        if ($locationId && $quantity > 0) {
            $balance = InventoryBalance::firstOrCreate(
                [
                    'product_id'          => $this->record->id,
                    'storage_location_id' => $locationId,
                ],
                ['quantity' => 0]
            );

            $balance->increment('quantity', $quantity);

            StockMovement::create([
                'product_id'          => $this->record->id,
                'storage_location_id' => $locationId,
                'type'                => 'in',
                'quantity'            => $quantity,
                'unit_cost'           => $this->record->purchase_cost ?? 0,
                'user_id'             => auth()->id(),
                'reference_type'      => 'opening_stock',
                'reference_id'        => $this->record->id,
            ]);
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Strip virtual fields before saving to products table
        unset($data['initial_location_id'], $data['initial_quantity']);
        return $data;
    }
}
