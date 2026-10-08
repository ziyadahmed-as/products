<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'reference', 'type', 'user_id', 'client_id', 'branch_id', 'storage_location_id', 'customer_name',
        'subtotal', 'discount', 'tax', 'total', 'paid_amount', 'payment_status', 'status',
        'is_stock_deducted',
    ];

    protected $casts = [
        'is_stock_deducted' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /** The branch this sale belongs to (immutable audit record). */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
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
