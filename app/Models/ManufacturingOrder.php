<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ManufacturingOrder extends Model
{
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING_APPROVAL = 'pending_approval';
    const STATUS_APPROVED = 'approved';
    const STATUS_MATERIALS_ISSUED = 'materials_issued';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'reference',
        'product_id',
        'recipe_id',
        'planned_quantity',
        'storage_location_id',
        'unit_of_measure_id',
        'planned_date',
        'start_date',
        'expected_completion_date',
        'actual_start_date',
        'actual_completion_date',
        'status',
        'is_materials_issued',
        'is_finished_registered',
        'good_quantity',
        'rejected_quantity',
        'additional_costs',
        'total_cost',
        'user_id',
        'assigned_user_id',
        'production_notes',
        'wastage_reason',
    ];

    protected $casts = [
        'is_materials_issued'     => 'boolean',
        'is_finished_registered'  => 'boolean',
        'planned_date'            => 'date',
        'start_date'              => 'date',
        'expected_completion_date'=> 'date',
        'actual_start_date'       => 'datetime',
        'actual_completion_date'  => 'datetime',
        'planned_quantity'        => 'float',
        'good_quantity'           => 'float',
        'rejected_quantity'       => 'float',
        'additional_costs'        => 'float',
        'total_cost'              => 'float',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    public function storageLocation(): BelongsTo
    {
        return $this->belongsTo(StorageLocation::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class, 'unit_of_measure_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function materialIssues(): HasMany
    {
        return $this->hasMany(MaterialIssue::class);
    }

    public function stockMovements(): MorphMany
    {
        return $this->morphMany(StockMovement::class, 'reference');
    }

    // Helper checks
    public function canSubmitForApproval(): bool
    {
        return in_array(strtolower($this->status), ['draft', 'pending']);
    }

    public function canApprove(): bool
    {
        return in_array(strtolower($this->status), ['draft', 'pending', 'pending_approval']);
    }

    public function canIssueMaterials(): bool
    {
        return in_array(strtolower($this->status), ['approved', 'planned']) && !$this->is_materials_issued;
    }

    public function canStartProduction(): bool
    {
        return in_array(strtolower($this->status), ['materials_issued', 'approved']) && $this->is_materials_issued;
    }

    public function canComplete(): bool
    {
        return in_array(strtolower($this->status), ['in_progress', 'materials_issued']) && !$this->is_finished_registered;
    }

    public function canCancel(): bool
    {
        return !in_array(strtolower($this->status), ['completed', 'cancelled']);
    }

    /**
     * Calculate total material cost consumed for this order
     */
    public function calculateMaterialCost(): float
    {
        return (float) $this->materialIssues->sum(function ($issue) {
            $unitCost = (float) ($issue->unit_cost ?: ($issue->product->purchase_cost ?? 0));
            return $issue->quantity_consumed * $unitCost;
        });
    }

    /**
     * Calculate production efficiency percentage: accepted quantity / planned quantity
     */
    public function calculateEfficiency(): float
    {
        if ($this->planned_quantity <= 0) {
            return 0.0;
        }
        return round(($this->good_quantity / $this->planned_quantity) * 100, 2);
    }
}
