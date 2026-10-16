<?php

namespace App\Http\Controllers;

use App\Models\InventoryBalance;
use App\Models\ManufacturingOrder;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleLine;
use App\Models\Payment;
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

        // Period metrics for KPI cards (matching business period trend)
        $startDate = Carbon::now()->startOfMonth();
        $endDate   = Carbon::now()->endOfMonth();
        $diffDays  = $startDate->diffInDays($endDate) + 1;
        $prevStartDate = (clone $startDate)->subDays($diffDays);
        $prevEndDate   = (clone $endDate)->subDays($diffDays);

        $salesThisPeriod = (clone $salesQuery)->whereIn('sales.status', ['confirmed', 'processing', 'shipped', 'completed'])
            ->whereBetween('sales.created_at', [$startDate, $endDate])
            ->sum('total');
        $salesPrevPeriod = (clone $salesQuery)->whereIn('sales.status', ['confirmed', 'processing', 'shipped', 'completed'])
            ->whereBetween('sales.created_at', [$prevStartDate, $prevEndDate])
            ->sum('total');
        $revenueGrowth = $salesPrevPeriod > 0
            ? round((($salesThisPeriod - $salesPrevPeriod) / $salesPrevPeriod) * 100, 1)
            : 100;

        $ordersTodayCount = (clone $salesQuery)->whereDate('sales.created_at', today())->count();
        $pendingSalesOrders = Sale::whereIn('status', ['pending'])->count();
        $completedMfgThisPeriod = ManufacturingOrder::whereIn('status', ['completed', 'Completed'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->when($branchIds !== null, fn ($q) => $q->whereHas('storageLocation', fn ($sl) => $sl->whereIn('branch_id', $branchIds)))
            ->count();

        // Consolidated Stats
        $yesterdaySales = (clone $salesQuery)->whereDate('sales.created_at', Carbon::yesterday())->sum('total');

        $stats = [
            'total_products'           => Product::where('is_active', true)->count(),
            'total_revenue'            => $totalRevenue,
            'selected_revenue'         => $salesThisPeriod,
            'today_sales'              => $todaySales,
            'today_sales_count'        => $ordersTodayCount,
            'orders_today'             => $ordersTodayCount,
            'pending_sales_orders'     => $pendingSalesOrders,
            'month_sales'              => $monthSales,
            'yesterday_sales'          => $yesterdaySales,
            'revenue_growth'           => $revenueGrowth,
            'revenue_trend'            => $revenueGrowth,
            'low_stock'                => (clone $lowStockQuery)->count(),
            'low_stock_count'          => (clone $lowStockQuery)->count(),
            'active_orders'            => (clone $ordersQuery)->count(),
            'active_production_orders' => (clone $ordersQuery)->count(),
            'pending_orders'           => (clone $ordersQuery)->whereIn('status', ['draft', 'pending_approval', 'planned'])->count(),
            'in_progress_orders'       => (clone $ordersQuery)->whereIn('status', ['approved', 'materials_issued', 'in_progress'])->count(),
            'completed_production'     => $completedProductionUnits,
            'completed_production_period' => $completedMfgThisPeriod,
            'scrap_rate'               => $scrapRate,
            'raw_materials_count'      => Product::where('type', 'raw_material')->where('is_active', true)->count(),
            'raw_materials_stock'      => $rawMaterialsStockUnits,
            'raw_materials_valuation'  => $rawMaterialsValuation,
            'finished_goods_valuation' => $finishedGoodsValuation,
            'total_inventory_valuation'=> $totalInventoryValuation,
            'low_stock_raw_materials'  => $lowStockRawCount,
            'today_activities_count'   => Activity::whereDate('created_at', today())->count(),
        ];

        // Chart 1: 14-Day Revenue Trend (Real Data)
        $daysCount = 14;
        $dailySales = (clone $salesQuery)
            ->where('sales.created_at', '>=', now()->subDays($daysCount - 1)->startOfDay())
            ->selectRaw('DATE(sales.created_at) as sale_date, SUM(sales.total) as total')
            ->groupBy('sale_date')
            ->pluck('total', 'sale_date');

        $revenueChartLabels = [];
        $revenueChartData = [];
        for ($i = $daysCount - 1; $i >= 0; $i--) {
            $dt = now()->subDays($i);
            $dStr = $dt->format('Y-m-d');
            $revenueChartLabels[] = $dt->format('M d');
            $revenueChartData[] = (float) ($dailySales[$dStr] ?? 0);
        }
        $revenueChart = [
            'labels' => $revenueChartLabels,
            'data'   => $revenueChartData,
        ];

        // Chart 2: Category / Distribution (Real Data)
        $categorySales = SaleLine::whereHas('sale', function ($q) use ($branchIds) {
            $q->whereIn('status', ['confirmed', 'processing', 'shipped', 'completed']);
            if ($branchIds !== null) {
                $q->whereIn('branch_id', $branchIds);
            }
        })
        ->join('products', 'sale_lines.product_id', '=', 'products.id')
        ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
        ->selectRaw('COALESCE(categories.name, "Unassigned") as cat_name, SUM(sale_lines.total) as total')
        ->groupBy('cat_name')
        ->orderByDesc('total')
        ->limit(5)
        ->pluck('total', 'cat_name');

        if ($categorySales->isEmpty()) {
            $categorySales = Product::where('products.is_active', true)
                ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                ->selectRaw('COALESCE(categories.name, "General") as cat_name, COUNT(products.id) as total')
                ->groupBy('cat_name')
                ->limit(5)
                ->pluck('total', 'cat_name');
        }

        $categoryChart = [
            'labels' => $categorySales->keys()->toArray(),
            'data'   => $categorySales->values()->map(fn($v) => (float)$v)->toArray(),
        ];

        $ordersByType = [
            'online' => (clone $salesQuery)->where('type', 'online')->count(),
            'pos'    => (clone $salesQuery)->where('type', 'pos')->count(),
            'direct' => (clone $salesQuery)->where('type', 'direct')->count(),
        ];

        // If all are zero, provide default distribution for visual display
        $ordersByTypeTotal = array_sum($ordersByType);
        $ordersByTypeChart = [
            'labels' => ['Online', 'POS', 'Direct'],
            'data'   => [$ordersByType['online'], $ordersByType['pos'], $ordersByType['direct']],
        ];

        // Chart 3: Manufacturing Status Breakdown (Real Data)
        $orderStatusBreakdown = ManufacturingOrder::when($branchIds !== null, fn ($q) => $q->whereHas('storageLocation', fn ($sl) => $sl->whereIn('branch_id', $branchIds)))
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Recent System Activity Logs
        $recentActivities = Activity::with('causer')->latest('id')->limit(8)->get();

        // Recent Sales
        $recentSales = (clone $salesQuery)->with(['user', 'branch'])->latest('id')->limit(8)->get();

        // Low stock items
        $lowStockItems = (clone $lowStockQuery)->with(['product', 'storageLocation'])
            ->select('inventory_balances.*')
            ->limit(6)
            ->get();

        // Open Manufacturing Orders
        $openOrders = (clone $ordersQuery)->with(['product.unit', 'storageLocation.branch'])
            ->latest('id')
            ->limit(6)
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

        $isSeller = $user && $user->hasRole('Seller') && !$user->hasRole('Super Admin') && !$user->hasRole('Manager');
        
        $sellerData = null;
        if ($isSeller) {
            $mySalesBase = Sale::where('user_id', $user->id);
            if ($branchIds !== null) {
                $mySalesBase->whereIn('branch_id', $branchIds);
            }

            $myCompletedSales = (clone $mySalesBase)->whereIn('status', ['confirmed', 'processing', 'shipped', 'completed']);
            $myTodaySales     = (float) (clone $myCompletedSales)->whereDate('created_at', today())->sum('total');
            $myTodayCount     = (int) (clone $myCompletedSales)->whereDate('created_at', today())->count();
            $myMonthSales     = (float) (clone $myCompletedSales)->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('total');
            $myMonthCount     = (int) (clone $myCompletedSales)->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count();
            $myTotalRevenue   = (float) (clone $myCompletedSales)->sum('total');
            $myTotalCount     = (int) (clone $myCompletedSales)->count();
            $myTotalPaid      = (float) (clone $myCompletedSales)->sum('paid_amount');
            $myTotalUnpaid    = max(0, $myTotalRevenue - $myTotalPaid);
            $avgOrderValue    = $myTotalCount > 0 ? round($myTotalRevenue / $myTotalCount, 2) : 0;

            // Seller recent sales with payment and lines details
            $sellerRecentSales = (clone $mySalesBase)
                ->with(['payments', 'lines.product.unit', 'branch'])
                ->latest('id')
                ->limit(8)
                ->get();

            // Sellable products catalog for rapid price & stock check
            $sellableProductsQuery = Product::where('is_active', true)
                ->where('type', '!=', 'raw_material')
                ->with(['unit', 'category', 'inventoryBalances' => function ($q) use ($branchIds) {
                    if ($branchIds !== null) {
                        $q->whereHas('storageLocation', fn ($sl) => $sl->whereIn('branch_id', $branchIds));
                    }
                }]);

            $sellableCatalog = $sellableProductsQuery->orderBy('name')->limit(12)->get();

            // Payment methods breakdown for this seller
            $sellerSaleIds = (clone $myCompletedSales)->pluck('id');
            $paymentsByMethod = Payment::whereIn('sale_id', $sellerSaleIds)
                ->selectRaw('method, sum(amount) as total_amount, count(*) as count')
                ->groupBy('method')
                ->get();

            // Top sold products by this seller
            $topProductsSold = SaleLine::whereIn('sale_id', $sellerSaleIds)
                ->with('product.unit')
                ->selectRaw('product_id, sum(quantity) as total_qty, sum(total) as total_revenue')
                ->groupBy('product_id')
                ->orderByDesc('total_qty')
                ->limit(5)
                ->get();

            $sellerData = [
                'today_sales'        => $myTodaySales,
                'today_count'        => $myTodayCount,
                'month_sales'        => $myMonthSales,
                'month_count'        => $myMonthCount,
                'total_revenue'      => $myTotalRevenue,
                'total_count'        => $myTotalCount,
                'total_paid'         => $myTotalPaid,
                'total_unpaid'       => $myTotalUnpaid,
                'avg_order_value'    => $avgOrderValue,
                'recent_sales'       => $sellerRecentSales,
                'sellable_catalog'   => $sellableCatalog,
                'payments_breakdown' => $paymentsByMethod,
                'top_products'       => $topProductsSold,
            ];
        }

        return view('dashboard', compact(
            'stats',
            'revenueChart',
            'ordersByTypeChart',
            'categoryChart',
            'orderStatusBreakdown',
            'recentActivities',
            'recentSales',
            'lowStockItems',
            'openOrders',
            'rawMaterialsList',
            'userPerformances',
            'isSeller',
            'sellerData'
        ));
    }
}
