<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockReceipt extends Model
{
    protected $fillable = ['reference', 'purchase_order_id', 'storage_location_id', 'user_id', 'date', 'notes', 'total_cost'];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function storageLocation()
    {
        return $this->belongsTo(StorageLocation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
