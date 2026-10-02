<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'type', // raw_material, resale_product, manufactured_product, consumable
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
        'is_featured',
        'rating',
        'reviews_count',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'is_featured' => 'boolean',
        'rating'      => 'float',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function unit()
    {
        return $this->belongsTo(UnitOfMeasure::class, 'unit_of_measure_id');
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class);
    }

    public function recipe()
    {
        return $this->hasOne(Recipe::class);
    }

    public function inventoryBalances()
    {
        return $this->hasMany(InventoryBalance::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    // Helpers
    public function isForSale(): bool
    {
        return in_array($this->type, ['resale_product', 'manufactured_product']);
    }

    public function isRawMaterial(): bool
    {
        return $this->type === 'raw_material';
    }
}
