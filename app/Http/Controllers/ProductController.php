<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Category;
use App\Models\InventoryBalance;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\StorageLocation;
use App\Models\UnitOfMeasure;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of products and raw materials.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Clients redirect to catalog
        if ($user && $user->hasRole('Client')) {
            return redirect()->route('public.shop');
        }

        $query = Product::with(['category', 'unit', 'branches'])->latest('id');

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                  ->orWhere('sku', 'like', $term)
                  ->orWhere('specification', 'like', $term);
            });
        }

        $selectedType = $request->query('type') ?? $request->route('type');
        if (!empty($selectedType)) {
            $query->where('type', $selectedType);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'low') {
                $query->whereColumn('quantity', '<=', 'minimum_stock_level')
                      ->where('minimum_stock_level', '>', 0);
            } elseif ($request->stock_status === 'out') {
                $query->where('quantity', '<=', 0);
            } elseif ($request->stock_status === 'in_stock') {
                $query->where('quantity', '>', 0);
            }
        }

        $products = $query->paginate(15)->withQueryString();
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $units = UnitOfMeasure::orderBy('name')->get();

        $counts = [
            'all'           => Product::count(),
            'raw_material'  => Product::where('type', 'raw_material')->count(),
            'manufactured'  => Product::where('type', 'manufactured_product')->count(),
            'resale'        => Product::where('type', 'resale_product')->count(),
            'low_stock'     => Product::whereColumn('quantity', '<=', 'minimum_stock_level')->where('minimum_stock_level', '>', 0)->count(),
        ];

        return view('products.index', compact('products', 'categories', 'units', 'counts'));
    }

    /**
     * Show the form for creating a new product or raw material.
     */
    public function create(Request $request)
    {
        $user = auth()->user();
        abort_unless(
            $user && ($user->hasRole('Super Admin') || $user->hasRole('Manager')),
            403,
            'Only Super Admin and Managers can register new raw materials and products.'
        );

        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $units = UnitOfMeasure::orderBy('name')->get();

        // Branch filtering for authorized scope
        if ($user->hasRole('Super Admin')) {
            $branches = Branch::where('is_active', true)->orderBy('name')->get();
            $storageLocations = StorageLocation::where('is_active', true)->with('branch')->get();
            $canChangeBranch = true;
            $defaultBranchId = $branches->first()?->id;
        } else {
            $branchIds = $user->authorizedBranchIds();
            $branches = Branch::whereIn('id', $branchIds)->where('is_active', true)->orderBy('name')->get();
            $storageLocations = StorageLocation::whereIn('branch_id', $branchIds)->where('is_active', true)->with('branch')->get();
            $canChangeBranch = $branches->count() > 1;
            $defaultBranchId = $user->branches()->first()?->id ?? $branches->first()?->id;
        }

        $defaultLocationId = StorageLocation::where('branch_id', $defaultBranchId)->where('is_active', true)->first()?->id;
        $defaultType = $request->query('type') ?? $request->route('type') ?? 'raw_material';

        return view('products.create', compact('categories', 'units', 'branches', 'storageLocations', 'defaultType', 'defaultBranchId', 'defaultLocationId', 'canChangeBranch'));
    }

    /**
     * Store a newly created product or raw material.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        abort_unless(
            $user && ($user->hasRole('Super Admin') || $user->hasRole('Manager')),
            403,
            'Only Super Admin and Managers can register new raw materials and products.'
        );

        // Pre-process branch allocations: drop any empty or non-positive rows so optional table lines don't fail validation
        if ($request->has('branch_allocations')) {
            $allocations = $request->input('branch_allocations');
            if (is_array($allocations)) {
                $filtered = array_values(array_filter($allocations, function ($alloc) {
                    if (!is_array($alloc)) {
                        return false;
                    }
                    $qty = $alloc['quantity'] ?? null;
                    return $qty !== null && trim((string) $qty) !== '' && is_numeric($qty) && (float) $qty > 0;
                }));

                if (empty($filtered)) {
                    $request->request->remove('branch_allocations');
                } else {
                    $request->merge(['branch_allocations' => $filtered]);
                }
            } else {
                $request->request->remove('branch_allocations');
            }
        }

        $validated = $request->validate([
            'name'                        => 'required|string|max:255',
            'type'                        => 'required|in:raw_material,manufactured_product,resale_product,consumable',
            'sku'                         => 'nullable|string|max:100|unique:products,sku',
            'category_id'                 => 'nullable|exists:categories,id',
            'unit_of_measure_id'          => 'nullable|exists:unit_of_measures,id',
            'specification'               => 'nullable|string|max:1000',
            'package_size'                => 'nullable|string|max:100',
            'minimum_stock_level'         => 'nullable|numeric|min:0',
            'purchase_cost'               => 'nullable|numeric|min:0',
            'selling_price'               => 'nullable|numeric|min:0',
            'is_active'                   => 'nullable|boolean',
            'branch_id'                   => 'nullable|exists:branches,id',
            'branches'                    => 'nullable|array',
            'branches.*'                  => 'exists:branches,id',
            'initial_quantity'            => 'nullable|numeric|min:0',
            'initial_storage_location_id' => 'nullable|exists:storage_locations,id',
            'branch_allocations'          => 'nullable|array',
            'branch_allocations.*.branch_id'           => 'required_with:branch_allocations|exists:branches,id',
            'branch_allocations.*.storage_location_id' => 'required_with:branch_allocations|exists:storage_locations,id',
            'branch_allocations.*.quantity'            => 'required_with:branch_allocations|numeric|min:0.01',
        ]);

        // Specific business rules per item type
        if ($validated['type'] === 'raw_material') {
            $validated['selling_price'] = null; // Raw materials are for production, not retail sale
        } elseif (in_array($validated['type'], ['resale_product', 'manufactured_product'])) {
            if (empty($validated['selling_price'])) {
                $validated['selling_price'] = 0;
            }
        }

        // Auto-generate SKU if omitted
        if (empty($validated['sku'])) {
            $prefix = match($validated['type']) {
                'raw_material'         => 'RAW',
                'manufactured_product' => 'MFG',
                'resale_product'       => 'RES',
                default                => 'PRD',
            };
            do {
                $sku = $prefix . '-' . strtoupper(Str::random(6));
            } while (Product::where('sku', $sku)->exists());
            $validated['sku'] = $sku;
        }

        $validated['is_active'] = $request->has('is_active');
        $validated['minimum_stock_level'] = $validated['minimum_stock_level'] ?? 0;
        $validated['purchase_cost'] = $validated['purchase_cost'] ?? 0;
        $validated['quantity'] = 0; // Starts at 0, synced via inventory balance

        $product = DB::transaction(function () use ($validated, $user, $request) {
            $product = Product::create($validated);

            $authorizedBranchIds = $user->hasRole('Super Admin') ? null : $user->authorizedBranchIds()->toArray();

            // 1. Multi-branch distribution mode if provided by authorized user
            if (!empty($validated['branch_allocations'])) {
                $allocatedBranchIds = [];
                foreach ($validated['branch_allocations'] as $alloc) {
                    $bId = (int) $alloc['branch_id'];
                    $locId = (int) $alloc['storage_location_id'];
                    $allocQty = (float) $alloc['quantity'];

                    if ($authorizedBranchIds !== null && !in_array($bId, $authorizedBranchIds)) {
                        continue;
                    }

                    $allocatedBranchIds[] = $bId;

                    $balance = InventoryBalance::firstOrCreate(
                        [
                            'product_id'          => $product->id,
                            'storage_location_id' => $locId,
                        ],
                        ['quantity' => 0]
                    );
                    $balance->increment('quantity', $allocQty);

                    StockMovement::create([
                        'product_id'          => $product->id,
                        'storage_location_id' => $locId,
                        'type'                => 'in',
                        'quantity'            => $allocQty,
                        'unit_cost'           => $product->purchase_cost ?? 0,
                        'user_id'             => $user->id,
                        'reference_type'      => 'opening_stock',
                        'reference_id'        => $product->id,
                    ]);
                }

                if (!empty($allocatedBranchIds)) {
                    $product->branches()->sync(array_values(array_unique($allocatedBranchIds)));
                }
                $product->syncQuantity();
            } else {
                // 2. Single-branch registration rule (Default)
                $selectedBranchId = $validated['branch_id']
                    ?? (!empty($validated['branches']) ? (int) $validated['branches'][0] : null)
                    ?? $user->branches()->first()?->id
                    ?? Branch::where('is_active', true)->first()?->id;

                if ($authorizedBranchIds !== null && !in_array($selectedBranchId, $authorizedBranchIds)) {
                    $selectedBranchId = $user->branches()->first()?->id ?? $authorizedBranchIds[0];
                }

                if ($selectedBranchId) {
                    $product->branches()->sync([$selectedBranchId]);
                }

                $initialQty = (float) ($validated['initial_quantity'] ?? 0);
                $locationId = $validated['initial_storage_location_id'] ?? null;
                if (!$locationId && $selectedBranchId) {
                    $locationId = StorageLocation::where('branch_id', $selectedBranchId)->where('is_active', true)->first()?->id;
                }

                if ($initialQty > 0 && $locationId) {
                    $balance = InventoryBalance::firstOrCreate(
                        [
                            'product_id'          => $product->id,
                            'storage_location_id' => $locationId,
                        ],
                        ['quantity' => 0]
                    );
                    $balance->increment('quantity', $initialQty);

                    StockMovement::create([
                        'product_id'          => $product->id,
                        'storage_location_id' => $locationId,
                        'type'                => 'in',
                        'quantity'            => $initialQty,
                        'unit_cost'           => $product->purchase_cost ?? 0,
                        'user_id'             => $user->id,
                        'reference_type'      => 'opening_stock',
                        'reference_id'        => $product->id,
                    ]);

                    $product->syncQuantity();
                }
            }

            ActivityLogger::log(
                module: 'Inventory',
                actionType: 'create',
                description: "Registered new {$product->type} '{$product->name}' (SKU: {$product->sku}).",
                subject: $product,
                properties: [
                    'product_id' => $product->id,
                    'sku'        => $product->sku,
                    'type'       => $product->type,
                    'cost'       => $product->purchase_cost,
                ]
            );

            return $product;
        });

        $typeLabel = match($product->type) {
            'raw_material'         => 'Raw Material',
            'manufactured_product' => 'Manufactured Product',
            'resale_product'       => 'Resale Product',
            'consumable'           => 'Consumable Item',
            default                => 'Item',
        };

        return redirect()->route('products.show', $product)
            ->with('success', "{$typeLabel} '{$product->name}' registered successfully.");
    }

    /**
     * Display the specified product or raw material.
     */
    public function show(Product $product)
    {
        $user = auth()->user();

        $product->load([
            'category',
            'unit',
            'branches',
            'recipe.materials.rawMaterial',
            'inventoryBalances.storageLocation.branch',
            'stockMovements' => fn ($q) => $q->with(['storageLocation.branch', 'user'])->latest('id')->limit(20),
        ]);

        return view('products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product or raw material.
     */
    public function edit(Product $product)
    {
        $user = auth()->user();
        abort_unless(
            $user && ($user->hasRole('Super Admin') || $user->hasRole('Manager')),
            403,
            'Only Super Admin and Managers can edit raw materials and products.'
        );

        $product->load(['branches', 'inventoryBalances.storageLocation']);
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $units = UnitOfMeasure::orderBy('name')->get();

        if ($user->hasRole('Super Admin')) {
            $branches = Branch::where('is_active', true)->orderBy('name')->get();
        } else {
            $branches = Branch::whereIn('id', $user->authorizedBranchIds())->where('is_active', true)->orderBy('name')->get();
        }

        return view('products.edit', compact('product', 'categories', 'units', 'branches'));
    }

    /**
     * Update the specified product or raw material master data.
     */
    public function update(Request $request, Product $product)
    {
        $user = auth()->user();
        abort_unless(
            $user && ($user->hasRole('Super Admin') || $user->hasRole('Manager')),
            403,
            'Only Super Admin and Managers can update raw materials and products.'
        );

        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'type'                => 'required|in:raw_material,manufactured_product,resale_product,consumable',
            'sku'                 => 'required|string|max:100|unique:products,sku,' . $product->id,
            'category_id'         => 'nullable|exists:categories,id',
            'unit_of_measure_id'  => 'nullable|exists:unit_of_measures,id',
            'specification'       => 'nullable|string|max:1000',
            'package_size'        => 'nullable|string|max:100',
            'minimum_stock_level' => 'nullable|numeric|min:0',
            'purchase_cost'       => 'nullable|numeric|min:0',
            'selling_price'       => 'nullable|numeric|min:0',
            'is_active'           => 'nullable|boolean',
            'branches'            => 'nullable|array',
            'branches.*'          => 'exists:branches,id',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['minimum_stock_level'] = $validated['minimum_stock_level'] ?? 0;
        $validated['purchase_cost'] = $validated['purchase_cost'] ?? 0;
        $validated['selling_price'] = $validated['selling_price'] ?? 0;

        DB::transaction(function () use ($product, $validated, $user) {
            $product->update($validated);

            if (isset($validated['branches'])) {
                $branchIds = $validated['branches'];
                if ($user->hasRole('Manager')) {
                    $authorizedIds = $user->authorizedBranchIds()->toArray();
                    $branchIds = array_intersect($branchIds, $authorizedIds);
                }
                $product->branches()->sync($branchIds);
            }

            ActivityLogger::log(
                module: 'Inventory',
                actionType: 'update',
                description: "Updated master data for {$product->type} '{$product->name}' (SKU: {$product->sku}).",
                subject: $product,
                properties: [
                    'product_id' => $product->id,
                    'sku'        => $product->sku,
                ]
            );
        });

        $typeLabel = $product->type === 'raw_material' ? 'Raw Material' : 'Product';

        return redirect()->route('products.show', $product)
            ->with('success', "{$typeLabel} '{$product->name}' updated successfully.");
    }

    /**
     * Deactivate or remove product.
     */
    public function destroy(Product $product)
    {
        $user = auth()->user();
        abort_unless(
            $user && ($user->hasRole('Super Admin') || $user->hasRole('Manager')),
            403,
            'Only Super Admin and Managers can deactivate or delete products.'
        );

        // Check if product has active inventory or movements
        $hasMovements = $product->stockMovements()->exists();
        $hasBalances = $product->inventoryBalances()->where('quantity', '>', 0)->exists();

        if ($hasBalances || $hasMovements) {
            // Cannot hard delete without breaking audit trail; deactivate instead
            $product->update(['is_active' => false]);
            return back()->with('success', "'{$product->name}' has existing inventory transactions and was deactivated instead of deleted.");
        }

        $product->branches()->detach();
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', "'{$product->name}' deleted successfully.");
    }
}
