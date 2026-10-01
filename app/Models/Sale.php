<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'reference', 'type', 'user_id', 'storage_location_id', 'customer_name',
        'subtotal', 'discount', 'tax', 'total', 'paid_amount', 'payment_status', 'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function storageLocation()
    {
        return $this->belongsTo(StorageLocation::class);
    }

    public function lines()
    {
        return $this->hasMany(SaleLine::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
