<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManufacturingOrder extends Model
{
    protected $fillable = [
        'reference', 'product_id', 'recipe_id', 'planned_quantity', 'storage_location_id',
        'planned_date', 'status', 'good_quantity', 'rejected_quantity', 'additional_costs', 'total_cost', 'user_id'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }

    public function storageLocation()
    {
        return $this->belongsTo(StorageLocation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function materialIssues()
    {
        return $this->hasMany(MaterialIssue::class);
    }
}
