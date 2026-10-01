<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryBalance;
use App\Models\Sale;
use App\Models\ManufacturingOrder;
use Spatie\Activitylog\Models\Activity;

class ReportController extends Controller
{
    public function inventory(Request $request)
    {
        $balances = InventoryBalance::with(['product.category', 'storageLocation'])
            ->join('products', 'inventory_balances.product_id', '=', 'products.id')
            ->select('inventory_balances.*')
            ->orderBy('products.name')
            ->get();

        $totalValue = 0;
        foreach ($balances as $b) {
            $totalValue += ($b->quantity * ($b->product->purchase_cost ?? 0));
        }

        return view('reports.inventory', compact('balances', 'totalValue'));
    }

    public function sales(Request $request)
    {
        $query = Sale::with('user')->latest();

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $sales = $query->get();

        $summary = [
            'total_revenue' => $sales->sum('total'),
            'total_count'   => $sales->count(),
            'paid'          => $sales->sum('paid_amount'),
            'unpaid'        => $sales->sum('total') - $sales->sum('paid_amount'),
        ];

        return view('reports.sales', compact('sales', 'summary'));
    }

    public function production(Request $request)
    {
        $query = ManufacturingOrder::with(['product', 'recipe'])->latest();

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $orders = $query->get();

        $summary = [
            'total_orders'     => $orders->count(),
            'completed'        => $orders->where('status', 'Completed')->count(),
            'total_produced'   => $orders->where('status', 'Completed')->sum('actual_quantity'),
        ];

        return view('reports.production', compact('orders', 'summary'));
    }
}
