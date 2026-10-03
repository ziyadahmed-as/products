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
        'quantity',
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
        'quantity'    => 'float',
    ];

    /**
     * Sync the product's quantity column from the InventoryBalance totals.
     * Call this after any stock change.
     */
    public function syncQuantity(): void
    {
        $total = $this->inventoryBalances()->sum('quantity');
        $this->update(['quantity' => $total]);
    }

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

    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
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

    /**
     * Recalculate rating and reviews_count from approved reviews.
     */
    public function syncRating(): void
    {
        $approved = $this->reviews()->where('is_approved', true);
        $this->update([
            'reviews_count' => $approved->count(),
            'rating'        => round($approved->avg('rating') ?? 0, 1),
        ]);
    }
}
