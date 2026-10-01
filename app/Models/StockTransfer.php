<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransfer extends Model
{
    protected $fillable = ['reference', 'from_location_id', 'to_location_id', 'user_id', 'status', 'dispatch_date', 'notes'];

    public function fromLocation() { return $this->belongsTo(StorageLocation::class, 'from_location_id'); }
    public function toLocation()   { return $this->belongsTo(StorageLocation::class, 'to_location_id'); }
    public function user()         { return $this->belongsTo(User::class); }
    public function items()        { return $this->hasMany(StockTransferItem::class); }
}
