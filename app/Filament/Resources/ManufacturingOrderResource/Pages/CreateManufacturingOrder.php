<?php

namespace App\Filament\Resources\ManufacturingOrderResource\Pages;

use App\Filament\Resources\ManufacturingOrderResource;
use App\Models\MaterialIssue;
use App\Models\Recipe;
use Filament\Resources\Pages\CreateRecord;

class CreateManufacturingOrder extends CreateRecord
{
    protected static string $resource = ManufacturingOrderResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        $order = $this->record;
        if ($order->recipe_id && $order->materialIssues()->count() === 0) {
            $recipe = Recipe::with('materials.rawMaterial')->find($order->recipe_id);
            if ($recipe) {
                foreach ($recipe->materials as $recipeMat) {
                    $rawProduct = $recipeMat->rawMaterial;
                    $calcPlanned = (float) $recipeMat->quantity * (float) $order->planned_quantity;
                    MaterialIssue::create([
                        'manufacturing_order_id' => $order->id,
                        'product_id'             => $recipeMat->raw_material_id,
                        'quantity_planned'       => $calcPlanned,
                        'quantity_issued'        => 0,
                        'quantity_consumed'      => 0,
                        'quantity_returned'      => 0,
                        'quantity_wasted'        => 0,
                        'unit_of_measure_id'     => $rawProduct?->unit_of_measure_id,
                        'unit_cost'              => (float) ($rawProduct?->purchase_cost ?? 0),
                    ]);
                }
            }
        }
    }
}
