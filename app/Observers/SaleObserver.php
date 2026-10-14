<?php

namespace App\Observers;

use App\Models\Sale;
use App\Services\ActivityLogger;

class SaleObserver
{
    public function created(Sale $sale): void
    {
        ActivityLogger::log(
            module: 'Sales',
            actionType: 'create',
            description: "Sale order '{$sale->reference}' (" . ($sale->type ?? 'sale') . ") created. Total: Br" . number_format($sale->total, 2),
            subject: $sale,
            properties: [
                'sale_id'        => $sale->id,
                'reference'      => $sale->reference,
                'type'           => $sale->type,
                'branch_id'      => $sale->branch_id,
                'customer_name'  => $sale->customer_name,
                'total'          => $sale->total,
                'payment_status' => $sale->payment_status,
                'status'         => $sale->status,
            ]
        );
    }

    public function updated(Sale $sale): void
    {
        $dirty = $sale->getDirty();
        unset($dirty['updated_at']);

        if (isset($dirty['is_stock_deducted']) && $dirty['is_stock_deducted']) {
            ActivityLogger::log(
                module: 'Sales',
                actionType: 'stock_deducted',
                description: "Inventory stock deducted for sale '{$sale->reference}'.",
                subject: $sale,
                properties: [
                    'sale_id'   => $sale->id,
                    'reference' => $sale->reference,
                    'branch_id' => $sale->branch_id,
                ]
            );
        }

        if (isset($dirty['status'])) {
            ActivityLogger::log(
                module: 'Sales',
                actionType: 'status_update',
                description: "Sale '{$sale->reference}' status changed from '{$sale->getOriginal('status')}' to '{$dirty['status']}'.",
                subject: $sale,
                properties: [
                    'sale_id'   => $sale->id,
                    'reference' => $sale->reference,
                    'branch_id' => $sale->branch_id,
                    'from'      => $sale->getOriginal('status'),
                    'to'        => $dirty['status'],
                ]
            );
        }

        if (isset($dirty['payment_status'])) {
            ActivityLogger::log(
                module: 'Sales',
                actionType: 'payment_status',
                description: "Sale '{$sale->reference}' payment status changed to '{$dirty['payment_status']}' (Paid: Br" . number_format($sale->paid_amount, 2) . ").",
                subject: $sale,
                properties: [
                    'sale_id'        => $sale->id,
                    'reference'      => $sale->reference,
                    'branch_id'      => $sale->branch_id,
                    'payment_status' => $dirty['payment_status'],
                    'paid_amount'    => $sale->paid_amount,
                ]
            );
        }
    }
}
