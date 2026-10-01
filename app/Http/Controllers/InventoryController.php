<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryBalance;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = InventoryBalance::with(['product.category', 'storageLocation'])
                    ->join('products', 'inventory_balances.product_id', '=', 'products.id')
                    ->select('inventory_balances.*');

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('products.name', 'like', '%' . $request->search . '%')
                  ->orWhere('products.sku', 'like', '%' . $request->search . '%');
            });
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

        $balances = $query->orderBy('products.name')->paginate(15);
        $locations = \App\Models\StorageLocation::orderBy('name')->get();

        return view('inventory.index', compact('balances', 'locations'));
    }
}