<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'type', // raw_material, resale_product, manufactured_product
        'name',
        'sku',
        'category_id',
        'unit_of_measure_id',
        'specification',
        'package_size',
        'minimum_stock_level',
        'purchase_cost',
        'selling_price',
        'image',
        'is_active',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function unit()
    {
        return $this->belongsTo(UnitOfMeasure::class, 'unit_of_measure_id');
    }
}
