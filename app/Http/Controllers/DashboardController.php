<?php

namespace App\Http\Controllers;

use App\Models\InventoryBalance;
use App\Models\ManufacturingOrder;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        if (auth()->check()) {
            $user = auth()->user();
            if ($user->hasRole('Client') || $user->roles()->count() === 0) {
                return redirect()->route('client.dashboard');
            }
            if ($user->hasRole('Production Officer')) {
                return redirect()->route('manufacturing.dashboard');
            }
            if ($user->hasRole('Auditor')) {
                return redirect()->route('reports.index');
            }
        }

        $user = auth()->user();
        $branchIds = null;
        if ($user && !$user->hasRole('Super Admin')) {
            $branchIds = $user->authorizedBranchIds();
        }

        $salesQuery = Sale::whereIn('status', ['confirmed', 'processing', 'shipped', 'completed']);
        $ordersQuery = ManufacturingOrder::whereIn('status', ['draft', 'pending_approval', 'approved', 'materials_issued', 'in_progress']);
        $lowStockQuery = InventoryBalance::whereColumn('inventory_balances.quantity', '<=', 'products.minimum_stock_level')
            ->where('products.minimum_stock_level', '>', 0)
            ->join('products', 'inventory_balances.product_id', '=', 'products.id');

        if ($branchIds !== null) {
            $salesQuery->whereIn('branch_id', $branchIds);
            $ordersQuery->whereHas('storageLocation', fn ($q) => $q->whereIn('branch_id', $branchIds));
            $lowStockQuery->whereHas('storageLocation', fn ($q) => $q->whereIn('branch_id', $branchIds));
        }

        $stats = [
            'total_products'  => Product::where('is_active', true)->count(),
            'today_sales'     => (clone $salesQuery)->whereDate('sales.created_at', today())->sum('total'),
            'low_stock'       => (clone $lowStockQuery)->count(),
            'active_orders'   => (clone $ordersQuery)->count(),
        ];

        $recentSales = (clone $salesQuery)->with('user')->latest('id')->limit(6)->get();

        $lowStockItems = (clone $lowStockQuery)->with(['product', 'storageLocation'])
            ->select('inventory_balances.*')
            ->limit(5)
            ->get();

        $openOrders = (clone $ordersQuery)->with(['product', 'storageLocation'])
            ->latest('id')
            ->limit(5)
            ->get();

        return view('dashboard', compact('stats', 'recentSales', 'lowStockItems', 'openOrders'));
    }
}
