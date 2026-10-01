<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialIssue extends Model
{
    protected $fillable = [
        'manufacturing_order_id', 'product_id', 'quantity_issued',
        'quantity_consumed', 'quantity_wasted', 'wastage_reason'
    ];

    public function manufacturingOrder()
    {
        return $this->belongsTo(ManufacturingOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
