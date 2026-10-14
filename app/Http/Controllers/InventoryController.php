<?php

namespace App\Http\Controllers;

use App\Models\InventoryBalance;
use App\Models\StorageLocation;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = InventoryBalance::with(['product.category', 'product.unit', 'storageLocation.branch'])
            ->join('products', 'inventory_balances.product_id', '=', 'products.id')
            ->select('inventory_balances.*');

        // Scoping by user branch authorization
        if ($user && !$user->hasRole('Super Admin') && !$user->hasRole('Auditor')) {
            $branchIds = $user->authorizedBranchIds();
            $query->whereHas('storageLocation', fn ($q) => $q->whereIn('branch_id', $branchIds));
        }

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('products.name', 'like', $term)
                  ->orWhere('products.sku', 'like', $term);
            });
        }

        if ($request->filled('type')) {
            $query->where('products.type', $request->type);
        }

        if ($request->filled('location_id')) {
            $query->where('inventory_balances.storage_location_id', $request->location_id);
        }

        if ($request->filter === 'low') {
            $query->whereColumn('inventory_balances.quantity', '<=', 'products.minimum_stock_level')
                  ->where('products.minimum_stock_level', '>', 0);
        } elseif ($request->filter === 'zero') {
            $query->where('inventory_balances.quantity', '<=', 0);
        }

        $balances = $query->orderBy('products.name')->paginate(20)->withQueryString();

        if ($user && ($user->hasRole('Super Admin') || $user->hasRole('Auditor'))) {
            $locations = StorageLocation::where('is_active', true)->with('branch')->orderBy('name')->get();
        } else {
            $locations = StorageLocation::whereIn('branch_id', $user->authorizedBranchIds())->where('is_active', true)->with('branch')->orderBy('name')->get();
        }

        return view('inventory.index', compact('balances', 'locations'));
    }
}
