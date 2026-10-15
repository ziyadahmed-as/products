<?php

namespace App\Http\Controllers;

use App\Models\InventoryBalance;
use App\Models\ManufacturingOrder;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        if (auth()->check()) {
            $user = auth()->user();
            if ($user->hasRole('Client') || $user->roles()->count() === 0) {
                return redirect()->route('client.dashboard');
            }
            if ($user->hasRole('Production Officer') || $user->hasRole('Product Manufacturer') || $user->hasRole('Production')) {
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

        // Base queries
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

        // Financial & Sales Metrics
        $totalRevenue = (clone $salesQuery)->sum('total');
        $todaySales   = (clone $salesQuery)->whereDate('sales.created_at', today())->sum('total');
        $monthSales   = (clone $salesQuery)->whereBetween('sales.created_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('total');

        // Inventory & Raw Materials Valuation
        $balancesQuery = InventoryBalance::with(['product.unit', 'storageLocation.branch']);
        if ($branchIds !== null) {
            $balancesQuery->whereHas('storageLocation', fn ($q) => $q->whereIn('branch_id', $branchIds));
        }
        $allBalances = $balancesQuery->get();

        $rawMaterialsValuation = 0;
        $finishedGoodsValuation = 0;
        $rawMaterialsStockUnits = 0;
        $lowStockRawCount = 0;

        foreach ($allBalances as $bal) {
            $cost = (float) ($bal->product->purchase_cost ?? 0);
            $qty  = (float) $bal->quantity;
            $lineValue = $qty * $cost;

            if ($bal->product && $bal->product->type === 'raw_material') {
                $rawMaterialsValuation += $lineValue;
                $rawMaterialsStockUnits += $qty;
                if ($qty <= (float) ($bal->product->minimum_stock_level ?? 0) && ($bal->product->minimum_stock_level ?? 0) > 0) {
                    $lowStockRawCount++;
                }
            } else {
                $finishedGoodsValuation += $lineValue;
            }
        }
        $totalInventoryValuation = $rawMaterialsValuation + $finishedGoodsValuation;

        // Manufacturing Metrics
        $completedProductionUnits = ManufacturingOrder::whereIn('status', ['completed', 'Completed'])
            ->when($branchIds !== null, fn ($q) => $q->whereHas('storageLocation', fn ($sl) => $sl->whereIn('branch_id', $branchIds)))
            ->sum('good_quantity');

        $scrapUnits = ManufacturingOrder::whereIn('status', ['completed', 'Completed'])
            ->when($branchIds !== null, fn ($q) => $q->whereHas('storageLocation', fn ($sl) => $sl->whereIn('branch_id', $branchIds)))
            ->sum('rejected_quantity');

        $totalMfgUnits = $completedProductionUnits + $scrapUnits;
        $scrapRate = $totalMfgUnits > 0 ? round(($scrapUnits / $totalMfgUnits) * 100, 1) : 0;

        // Raw Materials List for Production Widget
        $rawMaterialsList = Product::where('type', 'raw_material')
            ->where('is_active', true)
            ->with(['unit', 'category'])
            ->orderBy('quantity', 'asc')
            ->limit(6)
            ->get();

        // Consolidated Stats
        $stats = [
            'total_products'           => Product::where('is_active', true)->count(),
            'total_revenue'            => $totalRevenue,
            'today_sales'              => $todaySales,
            'month_sales'              => $monthSales,
            'low_stock'                => (clone $lowStockQuery)->count(),
            'active_orders'            => (clone $ordersQuery)->count(),
            'completed_production'     => $completedProductionUnits,
            'scrap_rate'               => $scrapRate,
            'raw_materials_count'      => Product::where('type', 'raw_material')->where('is_active', true)->count(),
            'raw_materials_stock'      => $rawMaterialsStockUnits,
            'raw_materials_valuation'  => $rawMaterialsValuation,
            'finished_goods_valuation' => $finishedGoodsValuation,
            'total_inventory_valuation'=> $totalInventoryValuation,
            'low_stock_raw_materials'  => $lowStockRawCount,
            'today_activities_count'   => Activity::whereDate('created_at', today())->count(),
        ];

        // Recent Sales
        $recentSales = (clone $salesQuery)->with(['user', 'branch'])->latest('id')->limit(6)->get();

        // Low stock items
        $lowStockItems = (clone $lowStockQuery)->with(['product', 'storageLocation'])
            ->select('inventory_balances.*')
            ->limit(5)
            ->get();

        // Open Manufacturing Orders
        $openOrders = (clone $ordersQuery)->with(['product.unit', 'storageLocation.branch'])
            ->latest('id')
            ->limit(5)
            ->get();

        // ── Staff & User Performance Report Data ──
        $internalUsers = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['Super Admin', 'Manager', 'Seller', 'Production Officer', 'Auditor']);
        })->with(['roles', 'branches'])->get();

        $userPerformances = $internalUsers->map(function ($staff) use ($branchIds) {
            $salesQ = Sale::where('user_id', $staff->id);
            $mfgQ   = ManufacturingOrder::where('user_id', $staff->id);

            if ($branchIds !== null) {
                $salesQ->whereIn('branch_id', $branchIds);
            }

            $salesCount  = (clone $salesQ)->count();
            $salesVolume = (clone $salesQ)->sum('total');
            $mfgCount    = (clone $mfgQ)->count();
            $mfgOutput   = (clone $mfgQ)->where('status', 'completed')->sum('good_quantity');
            $activities  = Activity::where('causer_id', $staff->id)->count();
            $lastAction  = Activity::where('causer_id', $staff->id)->latest('id')->value('created_at');

            // Determine badge status
            $status = 'Active';
            if ($salesVolume > 50000 || $mfgOutput > 1000) {
                $status = 'Top Performer';
            } elseif ($activities === 0 && $salesCount === 0 && $mfgCount === 0) {
                $status = 'New / Idle';
            }

            return [
                'user'        => $staff,
                'role'        => $staff->roles->first()?->name ?? 'Staff',
                'branches'    => $staff->branches->pluck('name')->join(', ') ?: ($staff->hasRole('Super Admin') ? 'Global' : 'None'),
                'sales_count' => $salesCount,
                'sales_volume'=> $salesVolume,
                'mfg_count'   => $mfgCount,
                'mfg_output'  => $mfgOutput,
                'activities'  => $activities,
                'last_action' => $lastAction,
                'status'      => $status,
            ];
        })->sortByDesc('sales_volume')->values();

        return view('dashboard', compact(
            'stats',
            'recentSales',
            'lowStockItems',
            'openOrders',
            'rawMaterialsList',
            'userPerformances'
        ));
    }
}
