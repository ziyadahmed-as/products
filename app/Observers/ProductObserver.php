<?php

namespace App\Observers;

use App\Models\Product;
use App\Services\ActivityLogger;

class ProductObserver
{
    public function created(Product $product): void
    {
        ActivityLogger::log(
            module: 'Inventory',
            actionType: 'create',
            description: "Product '{$product->name}' (SKU: {$product->sku}, Type: {$product->type}) was created.",
            subject: $product,
            properties: [
                'product_id' => $product->id,
                'name'       => $product->name,
                'sku'        => $product->sku,
                'type'       => $product->type,
                'price'      => $product->selling_price,
                'cost'       => $product->purchase_cost,
            ]
        );
    }

    public function updated(Product $product): void
    {
        $dirty = $product->getDirty();
        // Ignore automatic timestamp updates or simple review count refreshes
        unset($dirty['updated_at']);

        if (!empty($dirty)) {
            $original = array_intersect_key($product->getOriginal(), $dirty);
            ActivityLogger::log(
                module: 'Inventory',
                actionType: 'update',
                description: "Product '{$product->name}' was updated.",
                subject: $product,
                properties: [
                    'product_id' => $product->id,
                    'sku'        => $product->sku,
                    'before'     => $original,
                    'after'      => $dirty,
                ]
            );
        }
    }

    public function deleted(Product $product): void
    {
        ActivityLogger::log(
            module: 'Inventory',
            actionType: 'delete',
            description: "Product '{$product->name}' (SKU: {$product->sku}) was deleted.",
            subject: $product,
            properties: [
                'product_id' => $product->id,
                'sku'        => $product->sku,
            ]
        );
    }
}
