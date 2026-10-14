<?php

namespace App\Observers;

use App\Models\StockReceipt;
use App\Services\ActivityLogger;

class StockReceiptObserver
{
    public function created(StockReceipt $receipt): void
    {
        $location = $receipt->storageLocation;
        ActivityLogger::log(
            module: 'Inventory',
            actionType: 'stock_receipt',
            description: "Stock receipt '{$receipt->reference}' recorded at " . ($location?->name ?? 'store') . ". Total Cost: Br" . number_format($receipt->total_cost ?? 0, 2),
            subject: $receipt,
            properties: [
                'receipt_id'          => $receipt->id,
                'reference'           => $receipt->reference,
                'storage_location_id' => $receipt->storage_location_id,
                'branch_id'           => $location?->branch_id,
                'total_cost'          => $receipt->total_cost,
                'date'                => $receipt->date,
            ]
        );
    }
}
