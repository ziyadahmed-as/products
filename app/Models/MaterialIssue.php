<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialIssue extends Model
{
    protected $fillable = [
        'manufacturing_order_id',
        'product_id',
        'quantity_planned',
        'quantity_issued',
        'quantity_consumed',
        'quantity_returned',
        'quantity_wasted',
        'unit_of_measure_id',
        'unit_cost',
        'wastage_reason',
    ];

    protected $casts = [
        'quantity_planned'  => 'float',
        'quantity_issued'   => 'float',
        'quantity_consumed' => 'float',
        'quantity_returned' => 'float',
        'quantity_wasted'   => 'float',
        'unit_cost'         => 'float',
    ];

    public function manufacturingOrder(): BelongsTo
    {
        return $this->belongsTo(ManufacturingOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class, 'unit_of_measure_id');
    }

    /**
     * Backward-compatible accessor for quantity_consumed
     */
    public function getQuantityUsedAttribute(): float
    {
        return (float) ($this->quantity_consumed ?? 0);
    }
}
