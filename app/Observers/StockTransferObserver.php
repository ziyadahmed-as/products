<?php

namespace App\Observers;

use App\Models\StockTransfer;
use App\Services\ActivityLogger;

class StockTransferObserver
{
    public function created(StockTransfer $transfer): void
    {
        $from = $transfer->fromLocation;
        $to = $transfer->toLocation;

        ActivityLogger::log(
            module: 'Transfers',
            actionType: 'create',
            description: "Stock transfer '{$transfer->reference}' created: From '" . ($from?->name ?? 'N/A') . "' to '" . ($to?->name ?? 'N/A') . "'.",
            subject: $transfer,
            properties: [
                'transfer_id'      => $transfer->id,
                'reference'        => $transfer->reference,
                'from_location_id' => $transfer->from_location_id,
                'to_location_id'   => $transfer->to_location_id,
                'branch_id'        => $from?->branch_id,
                'status'           => $transfer->status,
                'dispatch_date'    => $transfer->dispatch_date,
            ]
        );
    }

    public function updated(StockTransfer $transfer): void
    {
        $dirty = $transfer->getDirty();
        unset($dirty['updated_at']);

        if (isset($dirty['status'])) {
            ActivityLogger::log(
                module: 'Transfers',
                actionType: 'status_update',
                description: "Stock transfer '{$transfer->reference}' status changed to '{$dirty['status']}'.",
                subject: $transfer,
                properties: [
                    'transfer_id' => $transfer->id,
                    'reference'   => $transfer->reference,
                    'from'        => $transfer->getOriginal('status'),
                    'to'          => $dirty['status'],
                ]
            );
        }
    }
}
