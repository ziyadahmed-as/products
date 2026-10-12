<?php

namespace App\Services;

use App\Models\InventoryBalance;
use App\Models\ManufacturingOrder;
use App\Models\MaterialIssue;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\RecipeMaterial;
use App\Models\StockMovement;
use App\Models\StorageLocation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class ManufacturingService
{
    /**
     * Generate unique manufacturing order reference number
     */
    public function generateOrderNumber(): string
    {
        do {
            $ref = 'MO-' . date('Ymd') . '-' . strtoupper(Str::random(4));
        } while (ManufacturingOrder::where('reference', $ref)->exists());

        return $ref;
    }

    /**
     * Check raw material availability for planned quantities.
     * Returns array of material checks: ['product_id' => ..., 'name' => ..., 'needed' => ..., 'available' => ..., 'is_sufficient' => bool]
     */
    public function checkMaterialAvailability(array $materials, int $storageLocationId): array
    {
        $results = [];

        foreach ($materials as $mat) {
            $productId = $mat['product_id'];
            $neededQty = (float) ($mat['quantity'] ?? $mat['quantity_planned'] ?? 0);

            $product = Product::with('unit')->find($productId);
            if (!$product) {
                continue;
            }

            // Check stock at specific storage location, or across active locations if preferred
            $locationBalance = (float) InventoryBalance::where('product_id', $productId)
                ->where('storage_location_id', $storageLocationId)
                ->value('quantity') ?? 0;

            $totalStock = (float) InventoryBalance::where('product_id', $productId)
                ->sum('quantity');

            $available = $locationBalance > 0 ? $locationBalance : $totalStock;

            $results[] = [
                'product_id'        => $productId,
                'name'              => $product->name,
                'sku'               => $product->sku,
                'unit'              => $product->unit?->code ?? $product->unit?->name ?? 'unit',
                'needed'            => $neededQty,
                'available_location'=> $locationBalance,
                'available_total'   => $totalStock,
                'is_sufficient'     => ($locationBalance >= $neededQty) || ($totalStock >= $neededQty),
            ];
        }

        return $results;
    }

    /**
     * Create a new Manufacturing Order along with planned MaterialIssue records.
     */
    public function createOrder(array $data, User $creator): ManufacturingOrder
    {
        return DB::transaction(function () use ($data, $creator) {
            $product = Product::findOrFail($data['product_id']);
            $plannedQty = (float) $data['planned_quantity'];

            $reference = !empty($data['reference']) ? $data['reference'] : $this->generateOrderNumber();
            $storageLocationId = $data['storage_location_id'];
            $recipeId = $data['recipe_id'] ?? null;
            $unitId = $data['unit_of_measure_id'] ?? $product->unit_of_measure_id;

            $order = ManufacturingOrder::create([
                'reference'                => $reference,
                'product_id'               => $product->id,
                'recipe_id'                => $recipeId,
                'storage_location_id'      => $storageLocationId,
                'unit_of_measure_id'       => $unitId,
                'planned_quantity'         => $plannedQty,
                'planned_date'             => $data['planned_date'] ?? today(),
                'start_date'               => $data['start_date'] ?? $data['planned_date'] ?? today(),
                'expected_completion_date' => $data['expected_completion_date'] ?? null,
                'status'                   => $data['status'] ?? ManufacturingOrder::STATUS_DRAFT,
                'user_id'                  => $creator->id,
                'assigned_user_id'         => $data['assigned_user_id'] ?? null,
                'production_notes'         => $data['production_notes'] ?? null,
                'additional_costs'         => (float) ($data['additional_costs'] ?? 0),
                'good_quantity'            => 0,
                'rejected_quantity'        => 0,
                'total_cost'               => 0,
                'is_materials_issued'      => false,
                'is_finished_registered'   => false,
            ]);

            // Determine raw materials: either explicitly provided or derived from recipe
            $materialsToCreate = [];

            if (!empty($data['materials']) && is_array($data['materials'])) {
                foreach ($data['materials'] as $mat) {
                    if (empty($mat['product_id']) || empty($mat['quantity'])) {
                        continue;
                    }
                    $rawProduct = Product::find($mat['product_id']);
                    $materialsToCreate[] = [
                        'product_id'        => $mat['product_id'],
                        'quantity_planned'  => (float) $mat['quantity'],
                        'unit_of_measure_id'=> $mat['unit_of_measure_id'] ?? $rawProduct?->unit_of_measure_id,
                        'unit_cost'         => (float) ($rawProduct?->purchase_cost ?? 0),
                    ];
                }
            } elseif ($recipeId) {
                $recipe = Recipe::with('materials.rawMaterial')->find($recipeId);
                if ($recipe) {
                    foreach ($recipe->materials as $recipeMat) {
                        $rawProduct = $recipeMat->rawMaterial;
                        // Quantity in recipe is typically per-unit or batch; scale by plannedQty
                        $calcPlanned = (float) $recipeMat->quantity * $plannedQty;
                        $materialsToCreate[] = [
                            'product_id'        => $recipeMat->raw_material_id,
                            'quantity_planned'  => $calcPlanned,
                            'unit_of_measure_id'=> $rawProduct?->unit_of_measure_id,
                            'unit_cost'         => (float) ($rawProduct?->purchase_cost ?? 0),
                        ];
                    }
                }
            }

            foreach ($materialsToCreate as $item) {
                MaterialIssue::create([
                    'manufacturing_order_id' => $order->id,
                    'product_id'             => $item['product_id'],
                    'quantity_planned'       => $item['quantity_planned'],
                    'quantity_issued'        => 0,
                    'quantity_consumed'      => 0,
                    'quantity_returned'      => 0,
                    'quantity_wasted'        => 0,
                    'unit_of_measure_id'     => $item['unit_of_measure_id'],
                    'unit_cost'              => $item['unit_cost'],
                ]);
            }

            return $order->load(['product', 'recipe', 'storageLocation', 'unit', 'materialIssues.product', 'materialIssues.unit']);
        });
    }

    /**
     * Submit order for approval
     */
    public function submitForApproval(ManufacturingOrder $order, User $user): void
    {
        if (!in_array(strtolower($order->status), [ManufacturingOrder::STATUS_DRAFT, 'pending'])) {
            throw new RuntimeException("Only draft orders can be submitted for approval.");
        }

        $order->update([
            'status' => ManufacturingOrder::STATUS_PENDING_APPROVAL,
        ]);
    }

    /**
     * Approve manufacturing order
     */
    public function approveOrder(ManufacturingOrder $order, User $user): void
    {
        if (!$order->canApprove()) {
            throw new RuntimeException("Order #{$order->reference} cannot be approved in its current status ({$order->status}).");
        }

        $order->update([
            'status' => ManufacturingOrder::STATUS_APPROVED,
        ]);
    }

    /**
     * Issue raw materials for production and deduct stock atomically.
     * Prevents negative inventory, duplicates, and race conditions.
     */
    public function issueMaterials(ManufacturingOrder $order, User $operator): void
    {
        DB::transaction(function () use ($order, $operator) {
            // Lock order for update to prevent concurrent submissions
            $order = ManufacturingOrder::where('id', $order->id)->lockForUpdate()->first();

            if ($order->is_materials_issued) {
                throw new RuntimeException("Materials for Order #{$order->reference} have already been issued.");
            }

            if (in_array(strtolower($order->status), [ManufacturingOrder::STATUS_COMPLETED, ManufacturingOrder::STATUS_CANCELLED])) {
                throw new RuntimeException("Cannot issue materials for an order that is {$order->status}.");
            }

            $storageLocationId = $order->storage_location_id;
            $materialIssues = $order->materialIssues()->lockForUpdate()->get();

            if ($materialIssues->isEmpty()) {
                throw new RuntimeException("No raw materials listed for Order #{$order->reference}.");
            }

            // Step 1: Pre-validate all raw materials have sufficient stock before deducting any
            foreach ($materialIssues as $issue) {
                $productId = $issue->product_id;
                $qtyToIssue = (float) $issue->quantity_planned;

                if ($qtyToIssue <= 0) {
                    continue;
                }

                // First look for inventory balance at the order's storage location
                $locBalance = InventoryBalance::where('product_id', $productId)
                    ->where('storage_location_id', $storageLocationId)
                    ->lockForUpdate()
                    ->first();

                $availableInLocation = (float) ($locBalance?->quantity ?? 0);

                // If not enough in location, check total available across branch/locations
                if ($availableInLocation < $qtyToIssue) {
                    $totalAvailable = (float) InventoryBalance::where('product_id', $productId)
                        ->lockForUpdate()
                        ->sum('quantity');

                    if ($totalAvailable < $qtyToIssue) {
                        $productName = Product::find($productId)?->name ?? "Product #{$productId}";
                        throw new RuntimeException(
                            "Insufficient stock for raw material \"{$productName}\". " .
                            "Required: {$qtyToIssue}, Available in location: {$availableInLocation}, Total in stock: {$totalAvailable}."
                        );
                    }
                }
            }

            // Step 2: Deduct stock atomically and record StockMovement per material
            foreach ($materialIssues as $issue) {
                $productId = $issue->product_id;
                $qtyToDeduct = (float) $issue->quantity_planned;

                if ($qtyToDeduct <= 0) {
                    continue;
                }

                $remainingToDeduct = $qtyToDeduct;

                // Priority 1: Deduct from the designated storage location
                $primaryBalance = InventoryBalance::where('product_id', $productId)
                    ->where('storage_location_id', $storageLocationId)
                    ->lockForUpdate()
                    ->first();

                if ($primaryBalance && $primaryBalance->quantity > 0) {
                    $deduct = min($primaryBalance->quantity, $remainingToDeduct);
                    $primaryBalance->decrement('quantity', $deduct);
                    $remainingToDeduct -= $deduct;

                    StockMovement::create([
                        'product_id'          => $productId,
                        'storage_location_id' => $storageLocationId,
                        'type'                => 'out',
                        'quantity'            => $deduct,
                        'unit_cost'           => $issue->unit_cost ?: ($primaryBalance->product->purchase_cost ?? 0),
                        'user_id'             => $operator->id,
                        'reference_type'      => ManufacturingOrder::class,
                        'reference_id'        => $order->id,
                    ]);
                }

                // Priority 2: If still remaining, deduct from other storage locations
                if ($remainingToDeduct > 0) {
                    $otherBalances = InventoryBalance::where('product_id', $productId)
                        ->where('storage_location_id', '!=', $storageLocationId)
                        ->where('quantity', '>', 0)
                        ->orderBy('quantity', 'desc')
                        ->lockForUpdate()
                        ->get();

                    foreach ($otherBalances as $balance) {
                        if ($remainingToDeduct <= 0) {
                            break;
                        }
                        $deduct = min($balance->quantity, $remainingToDeduct);
                        $balance->decrement('quantity', $deduct);
                        $remainingToDeduct -= $deduct;

                        StockMovement::create([
                            'product_id'          => $productId,
                            'storage_location_id' => $balance->storage_location_id,
                            'type'                => 'out',
                            'quantity'            => $deduct,
                            'unit_cost'           => $issue->unit_cost ?: ($balance->product->purchase_cost ?? 0),
                            'user_id'             => $operator->id,
                            'reference_type'      => ManufacturingOrder::class,
                            'reference_id'        => $order->id,
                        ]);
                    }
                }

                if ($remainingToDeduct > 0) {
                    throw new RuntimeException("Could not deduct full requested quantity for raw material #{$productId}.");
                }

                // Update the MaterialIssue record
                $issue->update([
                    'quantity_issued'   => $qtyToDeduct,
                    'quantity_consumed' => $qtyToDeduct, // default until completion adjustments
                ]);

                // Sync product overall quantity
                Product::find($productId)?->syncQuantity();
            }

            // Update order state
            $order->update([
                'status'              => ManufacturingOrder::STATUS_MATERIALS_ISSUED,
                'is_materials_issued' => true,
            ]);
        });
    }

    /**
     * Transition order to In Production status
     */
    public function startProduction(ManufacturingOrder $order, User $operator): void
    {
        if (!$order->is_materials_issued) {
            throw new RuntimeException("Raw materials must be issued before starting production.");
        }

        if (in_array(strtolower($order->status), [ManufacturingOrder::STATUS_COMPLETED, ManufacturingOrder::STATUS_CANCELLED])) {
            throw new RuntimeException("Cannot start production for order that is already {$order->status}.");
        }

        $order->update([
            'status'            => ManufacturingOrder::STATUS_IN_PROGRESS,
            'actual_start_date' => $order->actual_start_date ?? now(),
        ]);
    }

    /**
     * Complete production and register finished products.
     * Only verified accepted good quantities are registered into saleable inventory.
     * Unused raw materials are returned to inventory with StockMovement reversal.
     * Prevents duplicate registration.
     */
    public function completeProduction(ManufacturingOrder $order, array $data, User $operator): void
    {
        DB::transaction(function () use ($order, $data, $operator) {
            $order = ManufacturingOrder::where('id', $order->id)->lockForUpdate()->first();

            if ($order->is_finished_registered) {
                throw new RuntimeException("Finished products for Order #{$order->reference} have already been registered.");
            }

            if (!$order->is_materials_issued) {
                throw new RuntimeException("Cannot complete production: materials have not been issued yet.");
            }

            if (strtolower($order->status) === ManufacturingOrder::STATUS_CANCELLED) {
                throw new RuntimeException("Cannot complete a cancelled order.");
            }

            $goodQty     = (float) ($data['good_quantity'] ?? $order->good_quantity ?? 0);
            $rejectedQty = (float) ($data['rejected_quantity'] ?? $order->rejected_quantity ?? 0);
            $extraCosts  = (float) ($data['additional_costs'] ?? $order->additional_costs ?? 0);
            $wastageNote = $data['wastage_reason'] ?? $order->wastage_reason ?? null;
            $prodNotes   = $data['production_notes'] ?? $order->production_notes ?? null;

            if ($goodQty <= 0 && $rejectedQty <= 0) {
                throw new RuntimeException("Please provide accepted or rejected quantities produced.");
            }

            // Step 1: Process actual raw material consumption & returns if specified
            $materialLines = $data['materials'] ?? [];
            $totalMaterialCost = 0;

            foreach ($order->materialIssues as $issue) {
                $lineData = $materialLines[$issue->id] ?? null;

                $consumedQty = $lineData ? (float) ($lineData['quantity_consumed'] ?? $issue->quantity_issued) : (float) $issue->quantity_consumed;
                $returnedQty = $lineData ? (float) ($lineData['quantity_returned'] ?? 0) : (float) $issue->quantity_returned;
                $wastedQty   = $lineData ? (float) ($lineData['quantity_wasted'] ?? 0) : (float) $issue->quantity_wasted;
                $lineReason  = $lineData['wastage_reason'] ?? $issue->wastage_reason;

                // Validate: consumed + returned + wasted cannot exceed issued quantity
                if (($consumedQty + $returnedQty) > $issue->quantity_issued) {
                    $returnedQty = max(0, $issue->quantity_issued - $consumedQty);
                }

                // If unused materials returned, restore back to InventoryBalance & log StockMovement ('in')
                if ($returnedQty > 0) {
                    $balance = InventoryBalance::firstOrCreate(
                        [
                            'product_id'          => $issue->product_id,
                            'storage_location_id' => $order->storage_location_id,
                        ],
                        ['quantity' => 0]
                    );

                    $balance->lockForUpdate();
                    $balance->increment('quantity', $returnedQty);
                    Product::find($issue->product_id)?->syncQuantity();

                    StockMovement::create([
                        'product_id'          => $issue->product_id,
                        'storage_location_id' => $order->storage_location_id,
                        'type'                => 'in',
                        'quantity'            => $returnedQty,
                        'unit_cost'           => $issue->unit_cost,
                        'user_id'             => $operator->id,
                        'reference_type'      => ManufacturingOrder::class,
                        'reference_id'        => $order->id,
                    ]);
                }

                $issue->update([
                    'quantity_consumed' => $consumedQty,
                    'quantity_returned' => $returnedQty,
                    'quantity_wasted'   => $wastedQty,
                    'wastage_reason'    => $lineReason,
                ]);

                $unitCost = (float) ($issue->unit_cost ?: ($issue->product->purchase_cost ?? 0));
                $totalMaterialCost += ($consumedQty * $unitCost);
            }

            // Step 2: Register verified good finished product in inventory
            if ($goodQty > 0) {
                $finishedProduct = Product::findOrFail($order->product_id);

                $finishedBalance = InventoryBalance::firstOrCreate(
                    [
                        'product_id'          => $finishedProduct->id,
                        'storage_location_id' => $order->storage_location_id,
                    ],
                    ['quantity' => 0]
                );

                $finishedBalance->lockForUpdate();
                $finishedBalance->increment('quantity', $goodQty);

                // Sync the denormalised total quantity on product
                $finishedProduct->syncQuantity();

                // Calculate unit production cost for stock movement
                $totalOrderCost = $totalMaterialCost + $extraCosts;
                $unitCost = $goodQty > 0 ? ($totalOrderCost / $goodQty) : 0;

                // Record finished-product stock movement in history
                StockMovement::create([
                    'product_id'          => $finishedProduct->id,
                    'storage_location_id' => $order->storage_location_id,
                    'type'                => 'in',
                    'quantity'            => $goodQty,
                    'unit_cost'           => round($unitCost, 2),
                    'user_id'             => $operator->id,
                    'reference_type'      => ManufacturingOrder::class,
                    'reference_id'        => $order->id,
                ]);
            }

            $totalOrderCost = $totalMaterialCost + $extraCosts;

            // Step 3: Update order status to Completed and mark is_finished_registered = true
            $order->update([
                'status'                 => ManufacturingOrder::STATUS_COMPLETED,
                'is_finished_registered' => true,
                'good_quantity'          => $goodQty,
                'rejected_quantity'      => $rejectedQty,
                'additional_costs'       => $extraCosts,
                'total_cost'             => round($totalOrderCost, 2),
                'actual_completion_date' => now(),
                'production_notes'       => $prodNotes,
                'wastage_reason'         => $wastageNote,
            ]);
        });
    }

    /**
     * Cancel an order.
     * If materials were already issued, restore all issued raw materials safely with StockMovement ('in').
     */
    public function cancelOrder(ManufacturingOrder $order, ?string $reason, User $operator): void
    {
        DB::transaction(function () use ($order, $reason, $operator) {
            $order = ManufacturingOrder::where('id', $order->id)->lockForUpdate()->first();

            if (in_array(strtolower($order->status), [ManufacturingOrder::STATUS_COMPLETED, ManufacturingOrder::STATUS_CANCELLED])) {
                throw new RuntimeException("Cannot cancel an order that is {$order->status}.");
            }

            // If materials were issued, reverse the issued materials back to inventory
            if ($order->is_materials_issued && !$order->is_finished_registered) {
                foreach ($order->materialIssues as $issue) {
                    $qtyToReturn = (float) $issue->quantity_issued;
                    if ($qtyToReturn <= 0) {
                        continue;
                    }

                    $balance = InventoryBalance::firstOrCreate(
                        [
                            'product_id'          => $issue->product_id,
                            'storage_location_id' => $order->storage_location_id,
                        ],
                        ['quantity' => 0]
                    );

                    $balance->lockForUpdate();
                    $balance->increment('quantity', $qtyToReturn);
                    Product::find($issue->product_id)?->syncQuantity();

                    StockMovement::create([
                        'product_id'          => $issue->product_id,
                        'storage_location_id' => $order->storage_location_id,
                        'type'                => 'in',
                        'quantity'            => $qtyToReturn,
                        'unit_cost'           => $issue->unit_cost,
                        'user_id'             => $operator->id,
                        'reference_type'      => ManufacturingOrder::class,
                        'reference_id'        => $order->id,
                    ]);

                    $issue->update([
                        'quantity_returned' => $qtyToReturn,
                        'quantity_consumed' => 0,
                    ]);
                }
            }

            $currentNotes = $order->production_notes ? $order->production_notes . "\n" : '';
            $cancelNote = "Cancelled by {$operator->name} at " . now()->toDateTimeString() . ($reason ? ": {$reason}" : "");

            $order->update([
                'status'           => ManufacturingOrder::STATUS_CANCELLED,
                'production_notes' => $currentNotes . $cancelNote,
            ]);
        });
    }
}
