<?php

namespace App\Observers;

use App\Models\ManufacturingOrder;
use App\Services\ActivityLogger;

class ManufacturingOrderObserver
{
    public function created(ManufacturingOrder $order): void
    {
        $product = $order->product;
        $location = $order->storageLocation;

        ActivityLogger::log(
            module: 'Manufacturing',
            actionType: 'order_create',
            description: "Manufacturing order '{$order->reference}' created for '" . ($product?->name ?? 'item') . "' (Planned: " . number_format($order->planned_quantity, 2) . ").",
            subject: $order,
            properties: [
                'order_id'            => $order->id,
                'reference'           => $order->reference,
                'product_id'          => $order->product_id,
                'planned_quantity'    => $order->planned_quantity,
                'storage_location_id' => $order->storage_location_id,
                'branch_id'           => $location?->branch_id,
                'status'              => $order->status,
                'planned_date'        => $order->planned_date?->format('Y-m-d'),
            ]
        );
    }

    public function updated(ManufacturingOrder $order): void
    {
        $dirty = $order->getDirty();
        unset($dirty['updated_at']);

        $location = $order->storageLocation;

        if (isset($dirty['status'])) {
            ActivityLogger::log(
                module: 'Manufacturing',
                actionType: 'status_update',
                description: "Manufacturing order '{$order->reference}' status updated from '{$order->getOriginal('status')}' to '{$dirty['status']}'.",
                subject: $order,
                properties: [
                    'order_id'  => $order->id,
                    'reference' => $order->reference,
                    'branch_id' => $location?->branch_id,
                    'from'      => $order->getOriginal('status'),
                    'to'        => $dirty['status'],
                ]
            );
        }

        if (isset($dirty['is_materials_issued']) && $dirty['is_materials_issued']) {
            ActivityLogger::log(
                module: 'Manufacturing',
                actionType: 'issue_materials',
                description: "Raw materials issued for manufacturing order '{$order->reference}'.",
                subject: $order,
                properties: [
                    'order_id'  => $order->id,
                    'reference' => $order->reference,
                    'branch_id' => $location?->branch_id,
                ]
            );
        }

        if (isset($dirty['is_finished_registered']) && $dirty['is_finished_registered']) {
            ActivityLogger::log(
                module: 'Manufacturing',
                actionType: 'complete',
                description: "Manufacturing finished goods registered for '{$order->reference}'. Good Qty: " . number_format($order->good_quantity, 2) . ", Rejected: " . number_format($order->rejected_quantity, 2) . ".",
                subject: $order,
                properties: [
                    'order_id'          => $order->id,
                    'reference'         => $order->reference,
                    'branch_id'         => $location?->branch_id,
                    'good_quantity'     => $order->good_quantity,
                    'rejected_quantity' => $order->rejected_quantity,
                    'total_cost'        => $order->total_cost,
                ]
            );
        }
    }
}
