<?php

namespace App\Http\Controllers;

use App\Models\InventoryBalance;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\StockReceipt;
use App\Models\StorageLocation;
use App\Services\ActivityLogger;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StockReceiptController extends Controller
{
    /**
     * Display a listing of stock receipts.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        abort_unless(
            $user && ($user->hasRole('Super Admin') || $user->hasRole('Manager') || $user->hasRole('Production Officer') || $user->hasRole('Auditor')),
            403,
            'Unauthorized access to stock receipts.'
        );

        $query = StockReceipt::with(['storageLocation.branch', 'user', 'stockMovements.product.unit'])
            ->latest('date')
            ->latest('id');

        // Branch scoping
        if (!$user->hasRole('Super Admin') && !$user->hasRole('Auditor')) {
            $branchIds = $user->authorizedBranchIds();
            $query->whereHas('storageLocation', fn ($q) => $q->whereIn('branch_id', $branchIds));
        }

        // Search by reference or notes
        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('reference', 'like', $term)
                  ->orWhere('notes', 'like', $term)
                  ->orWhereHas('stockMovements.product', function ($pq) use ($term) {
                      $pq->where('name', 'like', $term)
                         ->orWhere('sku', 'like', $term);
                  });
            });
        }

        // Filter by Storage Location
        if ($request->filled('storage_location_id')) {
            $query->where('storage_location_id', $request->storage_location_id);
        }

        // Date range
        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->to);
        }

        $receipts = $query->paginate(20)->withQueryString();

        // Locations for filter dropdown
        if ($user->hasRole('Super Admin') || $user->hasRole('Auditor')) {
            $locations = StorageLocation::where('is_active', true)->with('branch')->get();
        } else {
            $locations = StorageLocation::whereIn('branch_id', $user->authorizedBranchIds())->where('is_active', true)->with('branch')->get();
        }

        $summary = [
            'total_receipts' => StockReceipt::count(),
            'total_valuation' => StockReceipt::sum('total_cost'),
            'today_receipts' => StockReceipt::whereDate('date', today())->count(),
        ];

        return view('stock_receipts.index', compact('receipts', 'locations', 'summary'));
    }

    /**
     * Show the form for creating a new stock receipt.
     */
    public function create(Request $request)
    {
        $user = auth()->user();
        abort_unless(
            $user && ($user->hasRole('Super Admin') || $user->hasRole('Manager')),
            403,
            'Only Super Admin and Managers can record new stock receipts.'
        );

        if ($user->hasRole('Super Admin')) {
            $locations = StorageLocation::where('is_active', true)->with('branch')->get();
        } else {
            $locations = StorageLocation::whereIn('branch_id', $user->authorizedBranchIds())->where('is_active', true)->with('branch')->get();
        }

        $products = Product::where('is_active', true)
            ->with(['unit', 'category'])
            ->orderBy('name')
            ->get();

        $preselectedProduct = null;
        if ($request->filled('product_id')) {
            $preselectedProduct = Product::with('unit')->find($request->product_id);
        }

        return view('stock_receipts.create', compact('locations', 'products', 'preselectedProduct'));
    }

    /**
     * Store a newly created stock receipt and atomically update inventory.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        abort_unless(
            $user && ($user->hasRole('Super Admin') || $user->hasRole('Manager')),
            403,
            'Only Super Admin and Managers can record stock receipts.'
        );

        $validated = $request->validate([
            'storage_location_id' => 'required|exists:storage_locations,id',
            'date'                => 'required|date',
            'reference'           => 'nullable|string|max:100|unique:stock_receipts,reference',
            'notes'               => 'nullable|string|max:1000',
            'items'               => 'required|array|min:1',
            'items.*.product_id'  => 'required|exists:products,id',
            'items.*.quantity'    => 'required|numeric|min:0.0001',
            'items.*.unit_cost'   => 'nullable|numeric|min:0',
        ], [
            'items.required'      => 'Please specify at least one raw material or product item to receive.',
            'items.min'           => 'Please specify at least one raw material or product item to receive.',
        ]);

        // Branch permission validation for manager
        $location = StorageLocation::findOrFail($validated['storage_location_id']);
        if (!$user->hasRole('Super Admin') && !$user->canAccessBranch($location->branch_id)) {
            abort(403, 'You are not authorized to receive stock into this branch location.');
        }

        try {
            $receipt = DB::transaction(function () use ($validated, $user, $location) {
                // Generate receipt reference if not provided
                $reference = !empty($validated['reference']) ? $validated['reference'] : null;
                if (!$reference) {
                    do {
                        $reference = 'RCT-' . date('Ymd') . '-' . strtoupper(Str::random(4));
                    } while (StockReceipt::where('reference', $reference)->exists());
                }

                $receipt = StockReceipt::create([
                    'reference'           => $reference,
                    'storage_location_id' => $location->id,
                    'user_id'             => $user->id,
                    'date'                => $validated['date'],
                    'notes'               => $validated['notes'] ?? null,
                    'total_cost'          => 0,
                ]);

                $totalCost = 0;

                foreach ($validated['items'] as $item) {
                    $productId = (int) $item['product_id'];
                    $qty = (float) $item['quantity'];

                    if ($qty <= 0) {
                        continue;
                    }

                    $product = Product::findOrFail($productId);
                    $unitCost = isset($item['unit_cost']) && $item['unit_cost'] !== ''
                        ? (float) $item['unit_cost']
                        : (float) ($product->purchase_cost ?? 0);

                    // Atomically increment InventoryBalance for this product and storage location
                    $balance = InventoryBalance::firstOrCreate(
                        [
                            'product_id'          => $productId,
                            'storage_location_id' => $location->id,
                        ],
                        ['quantity' => 0]
                    );

                    $balance->lockForUpdate();
                    $balance->increment('quantity', $qty);

                    // Sync product denormalized quantity
                    $product->syncQuantity();

                    // If unit cost is provided and higher than 0, optionally update purchase_cost
                    if ($unitCost > 0 && ($product->purchase_cost <= 0 || $product->purchase_cost != $unitCost)) {
                        $product->update(['purchase_cost' => $unitCost]);
                    }

                    // Create stock movement record linking to this receipt
                    StockMovement::create([
                        'product_id'          => $productId,
                        'storage_location_id' => $location->id,
                        'type'                => 'in',
                        'quantity'            => $qty,
                        'unit_cost'           => $unitCost,
                        'user_id'             => $user->id,
                        'reference_type'      => StockReceipt::class,
                        'reference_id'        => $receipt->id,
                    ]);

                    $totalCost += ($qty * $unitCost);
                }

                $receipt->update(['total_cost' => round($totalCost, 2)]);

                ActivityLogger::log(
                    module: 'Inventory',
                    actionType: 'stock_receipt',
                    description: "Stock receipt '{$receipt->reference}' processed at {$location->name}. Total items: " . count($validated['items']) . ", Value: Br " . number_format($totalCost, 2),
                    subject: $receipt,
                    properties: [
                        'receipt_id'          => $receipt->id,
                        'reference'           => $receipt->reference,
                        'storage_location_id' => $location->id,
                        'branch_id'           => $location->branch_id,
                        'total_cost'          => $totalCost,
                    ]
                );

                return $receipt;
            });

            return redirect()->route('stock-receipts.show', $receipt)
                ->with('success', "Stock Receipt #{$receipt->reference} completed. Inventory has been updated.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Failed to process stock receipt: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified stock receipt with all received item lines.
     */
    public function show(StockReceipt $stock_receipt)
    {
        $user = auth()->user();
        abort_unless(
            $user && ($user->hasRole('Super Admin') || $user->hasRole('Manager') || $user->hasRole('Production Officer') || $user->hasRole('Auditor')),
            403,
            'Unauthorized access to stock receipts.'
        );

        $receipt = $stock_receipt->load([
            'storageLocation.branch',
            'user',
            'stockMovements.product.unit',
            'stockMovements.product.category',
        ]);

        return view('stock_receipts.show', compact('receipt'));
    }
}
