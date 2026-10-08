<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\InventoryBalance;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleLine;
use App\Models\StorageLocation;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SaleService
{
    /**
     * Resolve the authorised Branch for a sale creation/update.
     *
     * Rules:
     *  - Seller  → automatically uses their single assigned branch; ignores any passed branch_id.
     *  - Manager → must supply a branch_id that is within their authorised branches.
     *  - Super Admin → may supply any active branch_id.
     *
     * @throws \RuntimeException on violation
     */
    public function resolveAuthorizedBranch(User $user, ?int $requestedBranchId): Branch
    {
        if ($user->hasRole('Seller')) {
            // Sellers are locked to their assigned branch – ignore any submitted branch_id.
            $branch = $user->branches()->where('is_active', true)->first();

            if (!$branch) {
                throw new \RuntimeException(
                    'You are not assigned to any active branch. Contact your administrator.'
                );
            }

            return $branch;
        }

        // Manager or Super Admin must provide a branch_id.
        if (!$requestedBranchId) {
            throw new \RuntimeException('Please select a branch to complete this sale.');
        }

        $branch = Branch::where('id', $requestedBranchId)->where('is_active', true)->first();

        if (!$branch) {
            throw new \RuntimeException('The selected branch does not exist or is not active.');
        }

        // Verify the user is authorised for the selected branch.
        if (!$user->canAccessBranch($requestedBranchId)) {
            throw new \RuntimeException(
                'You are not authorised to sell from the selected branch.'
            );
        }

        return $branch;
    }

    /**
     * Resolve the primary (first active) StorageLocation for a branch.
     * Prefers a specific location_id when one is explicitly provided and belongs to the branch.
     *
     * @throws \RuntimeException when the branch has no active storage location.
     */
    public function resolveStorageLocation(Branch $branch, ?int $locationId = null): StorageLocation
    {
        if ($locationId) {
            $location = StorageLocation::where('id', $locationId)
                ->where('branch_id', $branch->id)
                ->where('is_active', true)
                ->first();

            if ($location) {
                return $location;
            }
        }

        $location = StorageLocation::where('branch_id', $branch->id)
            ->where('is_active', true)
            ->first();

        if (!$location) {
            throw new \RuntimeException(
                "Branch \"{$branch->name}\" has no active storage location. Contact your administrator."
            );
        }

        return $location;
    }

    /**
     * Validate that each requested line can be fulfilled from the given branch stock.
     * Lines format: [['product_id' => int, 'quantity' => float], ...]
     *
     * @throws \RuntimeException on first insufficient-stock product.
     */
    public function validateBranchStock(Branch $branch, array $lines): void
    {
        $locationIds = StorageLocation::where('branch_id', $branch->id)
            ->where('is_active', true)
            ->pluck('id');

        foreach ($lines as $line) {
            $productId = $line['product_id'] ?? null;
            $qty       = (float) ($line['quantity'] ?? 0);

            if (!$productId || $qty <= 0) {
                continue;
            }

            $available = InventoryBalance::where('product_id', $productId)
                ->whereIn('storage_location_id', $locationIds)
                ->lockForUpdate()          // Prevent concurrent race conditions.
                ->sum('quantity');

            if ((float) $available < $qty) {
                $name = Product::find($productId)?->name ?? "Product #{$productId}";
                throw new \RuntimeException(
                    "Insufficient stock for \"{$name}\" in branch \"{$branch->name}\". "
                    . "Available: {$available}, Requested: {$qty}."
                );
            }
        }
    }

    /**
     * Deduct stock from branch inventory and create StockMovement records.
     * Called inside an existing DB transaction.
     *
     * @param Branch   $branch      The branch whose stock is being deducted.
     * @param Sale     $sale        The confirmed sale driving the deduction.
     * @param User     $operator    The authenticated user performing the operation.
     */
    public function deductBranchStock(Branch $branch, Sale $sale, User $operator): void
    {
        $locationIds = StorageLocation::where('branch_id', $branch->id)
            ->where('is_active', true)
            ->pluck('id');

        foreach ($sale->lines as $line) {
            $productId     = $line->product_id;
            $qtyToDeduct   = (float) $line->quantity;

            // Re-validate within the transaction for concurrency safety.
            $available = InventoryBalance::where('product_id', $productId)
                ->whereIn('storage_location_id', $locationIds)
                ->lockForUpdate()
                ->sum('quantity');

            if ((float) $available < $qtyToDeduct) {
                $name = Product::find($productId)?->name ?? "Product #{$productId}";
                throw new \RuntimeException(
                    "Insufficient stock for \"{$name}\" in branch \"{$branch->name}\". "
                    . "Available: {$available}, Requested: {$qtyToDeduct}."
                );
            }

            // Deduct from individual location balances (highest quantity first).
            $balances = InventoryBalance::where('product_id', $productId)
                ->whereIn('storage_location_id', $locationIds)
                ->where('quantity', '>', 0)
                ->orderBy('quantity', 'desc')
                ->lockForUpdate()
                ->get();

            foreach ($balances as $balance) {
                if ($qtyToDeduct <= 0) {
                    break;
                }

                $deduct = min($balance->quantity, $qtyToDeduct);
                $balance->decrement('quantity', $deduct);
                $qtyToDeduct -= $deduct;

                // Record the inventory movement per location.
                StockMovement::create([
                    'product_id'          => $productId,
                    'storage_location_id' => $balance->storage_location_id,
                    'type'                => 'out',
                    'quantity'            => $deduct,
                    'unit_cost'           => $line->unit_cost ?? 0,
                    'user_id'             => $operator->id,
                    'reference_type'      => Sale::class,
                    'reference_id'        => $sale->id,
                ]);
            }
        }

        // Sync product.quantity (denormalised total) for each sold product.
        $sale->lines->each(function ($line) {
            Product::find($line->product_id)?->syncQuantity();
        });
    }

    /**
     * Confirm a pending sale and deduct its stock — wrapped in a DB transaction.
     * Idempotent: does nothing if is_stock_deducted is already true.
     *
     * @throws \RuntimeException on insufficient stock or branch auth violation.
     */
    public function confirmAndDeductStock(Sale $sale, User $operator): void
    {
        if ($sale->is_stock_deducted) {
            return; // Already processed — prevent double-deduction.
        }

        // Resolve the branch from the sale's stored branch_id.
        $branch = $sale->branch
            ?? ($sale->storageLocation?->branch)
            ?? null;

        if (!$branch) {
            throw new \RuntimeException(
                "Sale #{$sale->reference} has no branch associated. Cannot deduct stock."
            );
        }

        // Security: verify the operator is authorised for this branch.
        if (!$operator->hasRole('Super Admin') && !$operator->canAccessBranch($branch->id)) {
            throw new \RuntimeException(
                "You are not authorised to confirm sales for branch \"{$branch->name}\"."
            );
        }

        DB::transaction(function () use ($sale, $branch, $operator) {
            $this->validateBranchStock($branch, $sale->lines->map(fn ($l) => [
                'product_id' => $l->product_id,
                'quantity'   => $l->quantity,
            ])->toArray());

            $this->deductBranchStock($branch, $sale, $operator);

            $sale->update([
                'status'            => 'confirmed',
                'is_stock_deducted' => true,
            ]);
        });
    }
}
