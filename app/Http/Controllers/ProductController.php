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

        if ($request->filled('type')) {
            $query->where('type', $request->type);
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
        } else {
            $branchIds = $user->authorizedBranchIds();
            $branches = Branch::whereIn('id', $branchIds)->where('is_active', true)->orderBy('name')->get();
            $storageLocations = StorageLocation::whereIn('branch_id', $branchIds)->where('is_active', true)->with('branch')->get();
        }

        $defaultType = $request->query('type', 'raw_material');

        return view('products.create', compact('categories', 'units', 'branches', 'storageLocations', 'defaultType'));
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
            'branches'                    => 'nullable|array',
            'branches.*'                  => 'exists:branches,id',
            'initial_quantity'            => 'nullable|numeric|min:0',
            'initial_storage_location_id' => 'nullable|exists:storage_locations,id',
        ]);

        // Selling price is required for resale and manufactured products
        if (in_array($validated['type'], ['resale_product', 'manufactured_product']) && empty($validated['selling_price'])) {
            $validated['selling_price'] = 0;
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

            // Sync branches
            $branchIds = $validated['branches'] ?? [];
            if ($user->hasRole('Manager') && !empty($branchIds)) {
                // Ensure manager can only allocate to their permitted branches
                $authorizedIds = $user->authorizedBranchIds()->toArray();
                $branchIds = array_intersect($branchIds, $authorizedIds);
            }
            if (!empty($branchIds)) {
                $product->branches()->sync($branchIds);
            }

            // Handle optional initial opening stock
            $initialQty = (float) ($validated['initial_quantity'] ?? 0);
            $locationId = $validated['initial_storage_location_id'] ?? null;

            if ($initialQty > 0 && $locationId) {
                $storageLocation = StorageLocation::find($locationId);
                if ($storageLocation) {
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

        $typeLabel = $product->type === 'raw_material' ? 'Raw Material' : 'Product';

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
