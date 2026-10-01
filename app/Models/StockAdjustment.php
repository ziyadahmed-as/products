<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    protected $fillable = ['product_id', 'storage_location_id', 'user_id', 'type', 'quantity', 'reason', 'status'];

    public function product()         { return $this->belongsTo(Product::class); }
    public function storageLocation() { return $this->belongsTo(StorageLocation::class); }
    public function user()            { return $this->belongsTo(User::class); }
}
