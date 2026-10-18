<?php

namespace App\Observers;

use App\Models\InventoryBalance;
use App\Models\StockMovement;
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

        if ($transfer->status === 'completed') {
            $this->processCompletedTransfer($transfer);
        }
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

            if ($dirty['status'] === 'completed') {
                $this->processCompletedTransfer($transfer);
            }
        }
    }

    /**
     * Deduct transferred stock from source location/branch and add to destination location/branch.
     * Guarantees branch stock isolation, audit trail movements, and idempotency.
     */
    protected function processCompletedTransfer(StockTransfer $transfer): void
    {
        // Prevent duplicate processing
        $alreadyProcessed = StockMovement::where('reference_type', 'transfer_out')
            ->where('reference_id', $transfer->id)
            ->exists();

        if ($alreadyProcessed) {
            return;
        }

        $fromLocId = $transfer->from_location_id;
        $toLocId   = $transfer->to_location_id;
        $toBranchId = $transfer->toLocation?->branch_id;
        $userId    = $transfer->user_id ?? auth()->id();

        $transfer->loadMissing(['items.product', 'toLocation']);

        foreach ($transfer->items as $item) {
            $product = $item->product;
            if (!$product) {
                continue;
            }

            $dispatchedQty = (float) $item->dispatched_quantity;
            $receivedQty   = (float) ($item->received_quantity ?: $item->dispatched_quantity);

            if ($dispatchedQty <= 0) {
                continue;
            }

            // 1. Deduct from source storage location
            $fromBalance = InventoryBalance::firstOrCreate(
                [
                    'product_id'          => $product->id,
                    'storage_location_id' => $fromLocId,
                ],
                ['quantity' => 0]
            );

            $actualDeduct = min($dispatchedQty, (float) $fromBalance->quantity);
            $fromBalance->decrement('quantity', $actualDeduct);

            StockMovement::create([
                'product_id'          => $product->id,
                'storage_location_id' => $fromLocId,
                'type'                => 'out',
                'quantity'            => $actualDeduct,
                'unit_cost'           => $product->purchase_cost ?? 0,
                'user_id'             => $userId,
                'reference_type'      => 'transfer_out',
                'reference_id'        => $transfer->id,
            ]);

            // 2. Add to destination storage location
            $toBalance = InventoryBalance::firstOrCreate(
                [
                    'product_id'          => $product->id,
                    'storage_location_id' => $toLocId,
                ],
                ['quantity' => 0]
            );

            $toBalance->increment('quantity', $receivedQty);

            StockMovement::create([
                'product_id'          => $product->id,
                'storage_location_id' => $toLocId,
                'type'                => 'in',
                'quantity'            => $receivedQty,
                'unit_cost'           => $product->purchase_cost ?? 0,
                'user_id'             => $userId,
                'reference_type'      => 'transfer_in',
                'reference_id'        => $transfer->id,
            ]);

            // 3. Attach destination branch to product's permitted branches
            if ($toBranchId) {
                $product->branches()->syncWithoutDetaching([$toBranchId]);
            }

            // 4. Sync product's denormalized quantity column
            $product->syncQuantity();
        }
    }
}
