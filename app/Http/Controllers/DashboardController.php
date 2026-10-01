<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Sale;
use App\Models\InventoryBalance;
use App\Models\ManufacturingOrder;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_products'  => Product::where('is_active', true)->count(),
            'today_sales'     => Sale::whereDate('created_at', today())->sum('total'),
            'low_stock'       => InventoryBalance::whereColumn('quantity', '<=', 'products.minimum_stock_level')
                                    ->join('products', 'inventory_balances.product_id', '=', 'products.id')
                                    ->count(),
            'active_orders'   => ManufacturingOrder::whereIn('status', ['Pending', 'In Progress'])->count(),
        ];

        $recentSales = Sale::with('user')->latest()->limit(6)->get();

        $lowStockItems = InventoryBalance::with('product')
            ->join('products', 'inventory_balances.product_id', '=', 'products.id')
            ->whereColumn('inventory_balances.quantity', '<=', 'products.minimum_stock_level')
            ->select('inventory_balances.*')
            ->limit(5)
            ->get();

        $openOrders = ManufacturingOrder::with('product')
            ->whereIn('status', ['Pending', 'In Progress'])
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard', compact('stats', 'recentSales', 'lowStockItems', 'openOrders'));
    }
}
