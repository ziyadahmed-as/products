<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryBalance;
use App\Models\Sale;
use App\Models\SaleLine;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Category;
use App\Models\ManufacturingOrder;
use App\Models\MaterialIssue;
use App\Models\StockTransfer;
use App\Models\StockMovement;
use App\Models\StorageLocation;
use App\Models\User;
use App\Services\ActivityLogger;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Consolidated Business Overview / Executive Dashboard
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Role-based redirection if role has a specific focused report
        if ($user->hasRole('Client')) {
            return redirect()->route('client.reports');
        }

        if ($user->hasRole('Seller')) {
            return redirect()->route('reports.sales');
        }

        if ($user->hasRole('Production Officer')) {
            return redirect()->route('reports.production');
        }

        // Accessible to Super Admin, Manager, Auditor
        abort_unless(
            $user->hasRole('Super Admin') || $user->hasRole('Manager') || $user->hasRole('Auditor'),
            403,
            'Unauthorized access to business reports.'
        );

        $branchIds = $this->getAuthorizedBranchIds($user);

        // Sales query
        $salesQuery = Sale::query();
        if ($branchIds !== null) {
            $salesQuery->whereIn('branch_id', $branchIds);
        }
        $salesQuery = $this->applyDateRange($salesQuery, $request);

        $totalSalesCount = (clone $salesQuery)->count();
        $totalRevenue    = (clone $salesQuery)->sum('total');
        $totalPaid       = (clone $salesQuery)->sum('paid_amount');
        $totalUnpaid     = $totalRevenue - $totalPaid;

        // Inventory valuation query
        $invQuery = InventoryBalance::with(['product', 'storageLocation.branch']);
        if ($branchIds !== null) {
            $invQuery->whereHas('storageLocation', fn ($q) => $q->whereIn('branch_id', $branchIds));
        }
        $inventoryBalances = $invQuery->get();

        $totalInventoryValue = 0;
        $totalStockUnits = 0;
        foreach ($inventoryBalances as $b) {
            $totalInventoryValue += ($b->quantity * ($b->product->purchase_cost ?? 0));
            $totalStockUnits += $b->quantity;
        }

        // Low stock items count
        $lowStockQuery = InventoryBalance::join('products', 'inventory_balances.product_id', '=', 'products.id')
            ->whereColumn('inventory_balances.quantity', '<=', 'products.minimum_stock_level');
        if ($branchIds !== null) {
            $lowStockQuery->join('storage_locations', 'inventory_balances.storage_location_id', '=', 'storage_locations.id')
                ->whereIn('storage_locations.branch_id', $branchIds);
        }
        $lowStockCount = $lowStockQuery->count();

        // Manufacturing query
        $moQuery = ManufacturingOrder::query();
        if ($branchIds !== null) {
            $moQuery->whereHas('storageLocation', fn ($q) => $q->whereIn('branch_id', $branchIds));
        }
        $moQuery = $this->applyDateRange($moQuery, $request);
        $activeManufacturingCount = (clone $moQuery)->whereIn('status', ['in_progress', 'materials_issued', 'approved'])->count();
        $completedProductionQty = (clone $moQuery)->where('status', 'completed')->sum('good_quantity');

        // Branch Performance Breakdown
        $branchesQuery = Branch::where('is_active', true);
        if ($branchIds !== null) {
            $branchesQuery->whereIn('id', $branchIds);
        }
        $branches = $branchesQuery->withCount('storageLocations')->get();

        $branchStats = [];
        foreach ($branches as $branch) {
            $bSales = Sale::where('branch_id', $branch->id);
            $bSales = $this->applyDateRange($bSales, $request);

            $bRev = (clone $bSales)->sum('total');
            $bPaid = (clone $bSales)->sum('paid_amount');
            $bCount = (clone $bSales)->count();

            // Branch stock valuation
            $bBalances = InventoryBalance::whereHas('storageLocation', fn ($q) => $q->where('branch_id', $branch->id))
                ->with('product')
                ->get();

            $bVal = 0;
            $bUnits = 0;
            foreach ($bBalances as $bal) {
                $bVal += ($bal->quantity * ($bal->product->purchase_cost ?? 0));
                $bUnits += $bal->quantity;
            }

            $branchStats[] = [
                'branch'       => $branch,
                'orders_count' => $bCount,
                'revenue'      => $bRev,
                'paid'         => $bPaid,
                'outstanding'  => $bRev - $bPaid,
                'stock_units'  => $bUnits,
                'stock_value'  => $bVal,
            ];
        }

        // Recent sales
        $recentSales = (clone $salesQuery)->with(['user', 'branch'])->latest('id')->limit(8)->get();

        return view('reports.index', compact(
            'totalSalesCount',
            'totalRevenue',
            'totalPaid',
            'totalUnpaid',
            'totalInventoryValue',
            'totalStockUnits',
            'lowStockCount',
            'activeManufacturingCount',
            'completedProductionQty',
            'branchStats',
            'recentSales'
        ));
    }

    /**
     * Sales and Revenue Reports
     */
    public function sales(Request $request)
    {
        $user = auth()->user();
        if ($user->hasRole('Client')) {
            return redirect()->route('client.reports');
        }

        $branchIds = $this->getAuthorizedBranchIds($user);
        $isSeller = $user->hasRole('Seller');

        $query = Sale::with(['user', 'branch', 'storageLocation', 'lines.product'])->latest('id');

        // Scoping
        if ($isSeller) {
            $query->where('user_id', $user->id);
            if ($branchIds !== null) {
                $query->whereIn('branch_id', $branchIds);
            }
        } elseif ($branchIds !== null) {
            $query->whereIn('branch_id', $branchIds);
        }

        $query = $this->applySalesFilters($query, $request);

        // Summary calculations
        $totalRevenue = (clone $query)->sum('total');
        $totalPaid    = (clone $query)->sum('paid_amount');
        $totalOrders  = (clone $query)->count();
        $totalSubtotal = (clone $query)->sum('subtotal');
        $totalDiscount = (clone $query)->sum('discount');
        $totalTax      = (clone $query)->sum('tax');
        $totalUnpaid  = $totalRevenue - $totalPaid;
        $avgOrderValue = $totalOrders > 0 ? ($totalRevenue / $totalOrders) : 0;

        $sales = $query->paginate(25)->withQueryString();

        // Filter dropdown options
        $filterBranches = $isSeller
            ? Branch::whereIn('id', $branchIds ?? [])->pluck('name', 'id')->toArray()
            : ($branchIds !== null
                ? Branch::whereIn('id', $branchIds)->pluck('name', 'id')->toArray()
                : Branch::where('is_active', true)->pluck('name', 'id')->toArray());

        $filterSellers = $isSeller
            ? []
            : User::whereHas('roles', fn ($q) => $q->whereIn('name', ['Seller', 'Manager', 'Super Admin']))
                ->select('id', 'name', 'email')
                ->orderBy('name')
                ->get();

        return view('reports.sales', compact(
            'sales',
            'totalRevenue',
            'totalPaid',
            'totalUnpaid',
            'totalOrders',
            'totalSubtotal',
            'totalDiscount',
            'totalTax',
            'avgOrderValue',
            'filterBranches',
            'filterSellers',
            'isSeller'
        ));
    }

    /**
     * Export Sales Report to CSV
     */
    public function exportSales(Request $request): StreamedResponse
    {
        $user = auth()->user();
        if ($user->hasRole('Client')) {
            abort(403);
        }

        ActivityLogger::log(
            module: 'Reports',
            actionType: 'export',
            description: "User {$user->name} exported Sales Report to CSV.",
            properties: ['format' => 'CSV']
        );

        $branchIds = $this->getAuthorizedBranchIds($user);
        $isSeller = $user->hasRole('Seller');

        $query = Sale::with(['user', 'branch', 'storageLocation', 'lines.product'])->latest('id');

        if ($isSeller) {
            $query->where('user_id', $user->id);
            if ($branchIds !== null) {
                $query->whereIn('branch_id', $branchIds);
            }
        } elseif ($branchIds !== null) {
            $query->whereIn('branch_id', $branchIds);
        }

        $query = $this->applySalesFilters($query, $request);

        $filename = 'sales_report_' . date('Y_m_d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'Invoice #',
                'Date',
                'Type',
                'Customer Name',
                'Customer Phone',
                'Branch',
                'Storage Location',
                'Seller',
                'Items Summary',
                'Subtotal (ETB)',
                'Discount (ETB)',
                'Tax (ETB)',
                'Total (ETB)',
                'Paid Amount (ETB)',
                'Outstanding (ETB)',
                'Payment Status',
                'Order Status',
                'Stock Deducted',
            ]);

            $query->chunk(200, function ($rows) use ($handle) {
                foreach ($rows as $s) {
                    $itemsSummary = $s->lines->map(function ($l) {
                        return ($l->product?->name ?? 'Item') . ' (' . number_format($l->quantity, 2) . ' @ Br' . number_format($l->unit_price, 2) . ')';
                    })->implode('; ');

                    fputcsv($handle, [
                        $s->reference,
                        $s->created_at ? $s->created_at->format('Y-m-d H:i') : '',
                        $s->type ?? 'quick_sale',
                        $s->customer_name ?? 'Walk-in Customer',
                        $s->customer_phone ?? '',
                        $s->branch?->name ?? '',
                        $s->storageLocation?->name ?? '',
                        $s->user?->name ?? 'System',
                        $itemsSummary,
                        number_format($s->subtotal, 2, '.', ''),
                        number_format($s->discount, 2, '.', ''),
                        number_format($s->tax, 2, '.', ''),
                        number_format($s->total, 2, '.', ''),
                        number_format($s->paid_amount, 2, '.', ''),
                        number_format($s->total - $s->paid_amount, 2, '.', ''),
                        ucfirst($s->payment_status),
                        ucfirst($s->status),
                        $s->is_stock_deducted ? 'Yes' : 'No',
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Inventory and Stock Movement Reports
     */
    public function inventory(Request $request)
    {
        $user = auth()->user();
        if ($user->hasRole('Client')) {
            return redirect()->route('client.reports');
        }

        $branchIds = $this->getAuthorizedBranchIds($user);
        $isSeller = $user->hasRole('Seller');

        $query = InventoryBalance::with(['product.category', 'product.unit', 'storageLocation.branch'])
            ->join('products', 'inventory_balances.product_id', '=', 'products.id')
            ->select('inventory_balances.*');

        // Scoping
        if ($branchIds !== null) {
            $query->whereHas('storageLocation', fn ($q) => $q->whereIn('branch_id', $branchIds));
        }

        // Filters
        if ($request->filled('branch_id')) {
            $branchId = (int) $request->branch_id;
            if ($branchIds === null || in_array($branchId, $branchIds->toArray())) {
                $query->whereHas('storageLocation', fn ($q) => $q->where('branch_id', $branchId));
            }
        }

        if ($request->filled('storage_location_id')) {
            $query->where('inventory_balances.storage_location_id', (int) $request->storage_location_id);
        }

        if ($request->filled('category_id')) {
            $query->where('products.category_id', (int) $request->category_id);
        }

        if ($request->filled('product_type')) {
            $query->where('products.type', $request->product_type);
        }

        if ($request->boolean('only_low_stock')) {
            $query->whereColumn('inventory_balances.quantity', '<=', 'products.minimum_stock_level');
        }

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('products.name', 'like', $term)
                  ->orWhere('products.sku', 'like', $term);
            });
        }

        $query->orderBy('products.name');

        // Summary valuation calculation before pagination
        $allMatching = (clone $query)->get();
        $totalValuation = 0;
        $totalStockUnits = 0;
        $lowStockCount = 0;
        $stockByUnit = [];
        foreach ($allMatching as $b) {
            $qty = (float) $b->quantity;
            $unitCode = $b->product?->unit?->code ?? $b->product?->unit?->name ?? 'units';
            $totalValuation += ($qty * ($b->product->purchase_cost ?? 0));
            $totalStockUnits += $qty;
            $stockByUnit[$unitCode] = ($stockByUnit[$unitCode] ?? 0) + $qty;
            if ($qty <= ($b->product->minimum_stock_level ?? 0)) {
                $lowStockCount++;
            }
        }

        $balances = $query->paginate(25)->withQueryString();

        // Recent Stock Movements (for movement audit context)
        $movementsQuery = StockMovement::with(['product', 'storageLocation.branch', 'user'])->latest('id');
        if ($branchIds !== null) {
            $movementsQuery->whereHas('storageLocation', fn ($q) => $q->whereIn('branch_id', $branchIds));
        }
        $recentMovements = $movementsQuery->limit(10)->get();

        // Dropdown data
        $filterBranches = $branchIds !== null
            ? Branch::whereIn('id', $branchIds)->pluck('name', 'id')->toArray()
            : Branch::where('is_active', true)->pluck('name', 'id')->toArray();

        $filterLocationsQuery = StorageLocation::where('is_active', true);
        if ($branchIds !== null) {
            $filterLocationsQuery->whereIn('branch_id', $branchIds);
        }
        $filterLocations = $filterLocationsQuery->pluck('name', 'id')->toArray();

        $filterCategories = Category::pluck('name', 'id')->toArray();

        return view('reports.inventory', compact(
            'balances',
            'totalValuation',
            'totalStockUnits',
            'stockByUnit',
            'lowStockCount',
            'recentMovements',
            'filterBranches',
            'filterLocations',
            'filterCategories',
            'isSeller'
        ));
    }

    /**
     * Export Inventory Report to CSV
     */
    public function exportInventory(Request $request): StreamedResponse
    {
        $user = auth()->user();
        if ($user->hasRole('Client')) {
            abort(403);
        }

        ActivityLogger::log(
            module: 'Reports',
            actionType: 'export',
            description: "User {$user->name} exported Inventory Report to CSV.",
            properties: ['format' => 'CSV']
        );

        $branchIds = $this->getAuthorizedBranchIds($user);

        $query = InventoryBalance::with(['product.category', 'product.unit', 'storageLocation.branch'])
            ->join('products', 'inventory_balances.product_id', '=', 'products.id')
            ->select('inventory_balances.*');

        if ($branchIds !== null) {
            $query->whereHas('storageLocation', fn ($q) => $q->whereIn('branch_id', $branchIds));
        }

        if ($request->filled('branch_id')) {
            $branchId = (int) $request->branch_id;
            if ($branchIds === null || in_array($branchId, $branchIds->toArray())) {
                $query->whereHas('storageLocation', fn ($q) => $q->where('branch_id', $branchId));
            }
        }
        if ($request->filled('storage_location_id')) {
            $query->where('inventory_balances.storage_location_id', (int) $request->storage_location_id);
        }
        if ($request->filled('category_id')) {
            $query->where('products.category_id', (int) $request->category_id);
        }
        if ($request->filled('product_type')) {
            $query->where('products.type', $request->product_type);
        }
        if ($request->boolean('only_low_stock')) {
            $query->whereColumn('inventory_balances.quantity', '<=', 'products.minimum_stock_level');
        }
        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('products.name', 'like', $term)
                  ->orWhere('products.sku', 'like', $term);
            });
        }

        $query->orderBy('products.name');

        $filename = 'inventory_report_' . date('Y_m_d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'Product Name',
                'SKU',
                'Type',
                'Category',
                'Storage Location',
                'Branch',
                'Quantity In Stock',
                'Unit of Measure',
                'Min Stock Level',
                'Unit Cost (ETB)',
                'Selling Price (ETB)',
                'Total Valuation (ETB)',
                'Stock Status',
            ]);

            $query->chunk(200, function ($rows) use ($handle) {
                foreach ($rows as $b) {
                    $prod = $b->product;
                    $status = $b->quantity <= 0 ? 'Out of Stock' : ($b->quantity <= ($prod->minimum_stock_level ?? 0) ? 'Low Stock' : 'In Stock');
                    $val = $b->quantity * ($prod->purchase_cost ?? 0);

                    fputcsv($handle, [
                        $prod->name ?? '—',
                        $prod->sku ?? '—',
                        ucwords(str_replace('_', ' ', $prod->type ?? '')),
                        $prod->category?->name ?? '—',
                        $b->storageLocation?->name ?? '—',
                        $b->storageLocation?->branch?->name ?? '—',
                        number_format($b->quantity, 2, '.', ''),
                        $prod->unit?->code ?? $prod->unit?->name ?? 'units',
                        number_format($prod->minimum_stock_level ?? 0, 2, '.', ''),
                        number_format($prod->purchase_cost ?? 0, 2, '.', ''),
                        number_format($prod->selling_price ?? 0, 2, '.', ''),
                        number_format($val, 2, '.', ''),
                        $status,
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Manufacturing and Raw Material Reports
     */
    public function production(Request $request)
    {
        $user = auth()->user();
        abort_unless(
            $user->hasRole('Super Admin') || $user->hasRole('Manager') || $user->hasRole('Production Officer') || $user->hasRole('Auditor'),
            403,
            'Unauthorized access to manufacturing reports.'
        );

        $branchIds = $this->getAuthorizedBranchIds($user);

        $query = ManufacturingOrder::with(['product.unit', 'recipe', 'storageLocation.branch', 'assignedUser', 'materialIssues.product'])->latest('id');

        if ($branchIds !== null) {
            $query->whereHas('storageLocation', fn ($q) => $q->whereIn('branch_id', $branchIds));
        }

        $query = $this->applyProductionFilters($query, $request);

        // Summaries
        $totalOrders = (clone $query)->count();
        $completedOrdersCount = (clone $query)->where('status', 'completed')->count();
        $totalPlannedQuantity = (clone $query)->sum('planned_quantity');
        $totalGoodProduced    = (clone $query)->where('status', 'completed')->sum('good_quantity');
        $totalRejected        = (clone $query)->where('status', 'completed')->sum('rejected_quantity');
        $totalCost            = (clone $query)->sum('total_cost');

        // Calculate efficiency on completed orders
        $completedOrders = (clone $query)->where('status', 'completed')->where('planned_quantity', '>', 0)->get();
        $avgEfficiency = $completedOrders->count() > 0
            ? round($completedOrders->avg(fn ($o) => $o->calculateEfficiency()), 1)
            : 100.0;

        // Raw Material Consumption & Wastage breakdown
        $materialIssues = MaterialIssue::with(['product.unit', 'manufacturingOrder'])
            ->whereHas('manufacturingOrder', function ($q) use ($branchIds, $request) {
                if ($branchIds !== null) {
                    $q->whereHas('storageLocation', fn ($l) => $l->whereIn('branch_id', $branchIds));
                }
                $this->applyDateRange($q, $request, 'planned_date');
            })
            ->latest('id')
            ->limit(15)
            ->get();

        $totalWastedMaterials = MaterialIssue::whereHas('manufacturingOrder', function ($q) use ($branchIds, $request) {
            if ($branchIds !== null) {
                $q->whereHas('storageLocation', fn ($l) => $l->whereIn('branch_id', $branchIds));
            }
            $this->applyDateRange($q, $request, 'planned_date');
        })->sum('quantity_wasted');

        $orders = $query->paginate(25)->withQueryString();

        $filterProducts = Product::where('type', 'manufactured_product')->pluck('name', 'id')->toArray();

        return view('reports.production', compact(
            'orders',
            'totalOrders',
            'completedOrdersCount',
            'totalPlannedQuantity',
            'totalGoodProduced',
            'totalRejected',
            'totalCost',
            'avgEfficiency',
            'materialIssues',
            'totalWastedMaterials',
            'filterProducts'
        ));
    }

    /**
     * Export Production Report to CSV
     */
    public function exportProduction(Request $request): StreamedResponse
    {
        $user = auth()->user();
        abort_unless(
            $user->hasRole('Super Admin') || $user->hasRole('Manager') || $user->hasRole('Production Officer') || $user->hasRole('Auditor'),
            403
        );

        ActivityLogger::log(
            module: 'Reports',
            actionType: 'export',
            description: "User {$user->name} exported Manufacturing Report to CSV.",
            properties: ['format' => 'CSV']
        );

        $branchIds = $this->getAuthorizedBranchIds($user);

        $query = ManufacturingOrder::with(['product.unit', 'recipe', 'storageLocation.branch', 'assignedUser'])->latest('id');

        if ($branchIds !== null) {
            $query->whereHas('storageLocation', fn ($q) => $q->whereIn('branch_id', $branchIds));
        }

        $query = $this->applyProductionFilters($query, $request);

        $filename = 'manufacturing_report_' . date('Y_m_d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'Order Reference',
                'Date Planned',
                'Actual Completion Date',
                'Product',
                'Recipe / BOM',
                'Location / Branch',
                'Planned Quantity',
                'Good Quantity',
                'Rejected Quantity',
                'Unit of Measure',
                'Efficiency (%)',
                'Status',
                'Assigned Staff',
                'Total Cost (ETB)',
            ]);

            $query->chunk(200, function ($rows) use ($handle) {
                foreach ($rows as $mo) {
                    fputcsv($handle, [
                        $mo->reference,
                        $mo->planned_date ? $mo->planned_date->format('Y-m-d') : '',
                        $mo->actual_completion_date ? $mo->actual_completion_date->format('Y-m-d H:i') : '',
                        $mo->product?->name ?? '—',
                        $mo->recipe?->name ?? 'Default',
                        ($mo->storageLocation?->name ?? '') . ' (' . ($mo->storageLocation?->branch?->name ?? '') . ')',
                        number_format($mo->planned_quantity, 2, '.', ''),
                        number_format($mo->good_quantity ?? 0, 2, '.', ''),
                        number_format($mo->rejected_quantity ?? 0, 2, '.', ''),
                        $mo->unit?->code ?? $mo->unit?->name ?? 'units',
                        number_format($mo->calculateEfficiency(), 1, '.', ''),
                        ucfirst(str_replace('_', ' ', $mo->status)),
                        $mo->assignedUser?->name ?? 'Unassigned',
                        number_format($mo->total_cost ?? 0, 2, '.', ''),
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Branch Performance Reports
     */
    public function branchPerformance(Request $request)
    {
        $user = auth()->user();
        abort_unless(
            $user->hasRole('Super Admin') || $user->hasRole('Manager') || $user->hasRole('Auditor'),
            403,
            'Unauthorized access to branch performance reports.'
        );

        $branchIds = $this->getAuthorizedBranchIds($user);

        $branchesQuery = Branch::where('is_active', true);
        if ($branchIds !== null) {
            $branchesQuery->whereIn('id', $branchIds);
        }
        $branches = $branchesQuery->with(['storageLocations', 'users'])->get();

        $branchStats = [];
        $totalEnterpriseRevenue = 0;
        $totalEnterpriseOrders = 0;
        $totalEnterprisePaid = 0;
        $totalEnterpriseValuation = 0;

        foreach ($branches as $branch) {
            $salesQuery = Sale::where('branch_id', $branch->id);
            $salesQuery = $this->applyDateRange($salesQuery, $request);

            $revenue = (clone $salesQuery)->sum('total');
            $paid = (clone $salesQuery)->sum('paid_amount');
            $ordersCount = (clone $salesQuery)->count();
            $outstanding = $revenue - $paid;

            // Stock valuation
            $balances = InventoryBalance::whereHas('storageLocation', fn ($q) => $q->where('branch_id', $branch->id))
                ->with('product')
                ->get();

            $stockValue = 0;
            $stockUnits = 0;
            foreach ($balances as $b) {
                $stockValue += ($b->quantity * ($b->product->purchase_cost ?? 0));
                $stockUnits += $b->quantity;
            }

            $totalEnterpriseRevenue += $revenue;
            $totalEnterpriseOrders += $ordersCount;
            $totalEnterprisePaid += $paid;
            $totalEnterpriseValuation += $stockValue;

            $branchStats[] = [
                'branch'          => $branch,
                'orders_count'    => $bSalesCount = $ordersCount,
                'revenue'         => $revenue,
                'paid'            => $paid,
                'outstanding'     => $outstanding,
                'stock_units'     => $stockUnits,
                'stock_value'     => $stockValue,
                'staff_count'     => $branch->users->count(),
                'locations_count' => $branch->storageLocations->count(),
            ];
        }

        return view('reports.branches', compact(
            'branchStats',
            'totalEnterpriseRevenue',
            'totalEnterpriseOrders',
            'totalEnterprisePaid',
            'totalEnterpriseValuation'
        ));
    }

    /**
     * Export Branch Performance Report to CSV
     */
    public function exportBranchPerformance(Request $request): StreamedResponse
    {
        $user = auth()->user();
        abort_unless($user->hasRole('Super Admin') || $user->hasRole('Manager') || $user->hasRole('Auditor'), 403);

        ActivityLogger::log(
            module: 'Reports',
            actionType: 'export',
            description: "User {$user->name} exported Branch Performance Report to CSV.",
            properties: ['format' => 'CSV']
        );

        $branchIds = $this->getAuthorizedBranchIds($user);
        $branchesQuery = Branch::where('is_active', true);
        if ($branchIds !== null) {
            $branchesQuery->whereIn('id', $branchIds);
        }
        $branches = $branchesQuery->with(['storageLocations', 'users'])->get();

        $filename = 'branch_performance_report_' . date('Y_m_d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($branches, $request) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'Branch Name',
                'Branch Code',
                'Invoices Count',
                'Total Sales Revenue (ETB)',
                'Collected Payments (ETB)',
                'Outstanding Balance (ETB)',
                'Inventory Units on Hand',
                'Inventory Valuation (ETB)',
                'Active Staff Count',
            ]);

            foreach ($branches as $branch) {
                $salesQuery = Sale::where('branch_id', $branch->id);
                $salesQuery = $this->applyDateRange($salesQuery, $request);

                $revenue = (clone $salesQuery)->sum('total');
                $paid = (clone $salesQuery)->sum('paid_amount');
                $ordersCount = (clone $salesQuery)->count();
                $outstanding = $revenue - $paid;

                $balances = InventoryBalance::whereHas('storageLocation', fn ($q) => $q->where('branch_id', $branch->id))
                    ->with('product')
                    ->get();

                $stockValue = 0;
                $stockUnits = 0;
                foreach ($balances as $b) {
                    $stockValue += ($b->quantity * ($b->product->purchase_cost ?? 0));
                    $stockUnits += $b->quantity;
                }

                fputcsv($handle, [
                    $branch->name,
                    $branch->code,
                    $ordersCount,
                    number_format($revenue, 2, '.', ''),
                    number_format($paid, 2, '.', ''),
                    number_format($outstanding, 2, '.', ''),
                    number_format($stockUnits, 2, '.', ''),
                    number_format($stockValue, 2, '.', ''),
                    $branch->users->count(),
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Stock Transfers and Logistics Reports
     */
    public function transfers(Request $request)
    {
        $user = auth()->user();
        abort_unless(
            $user->hasRole('Super Admin') || $user->hasRole('Manager') || $user->hasRole('Auditor'),
            403,
            'Unauthorized access to transfer reports.'
        );

        $branchIds = $this->getAuthorizedBranchIds($user);

        $query = StockTransfer::with(['fromLocation.branch', 'toLocation.branch', 'user', 'items.product'])->latest('id');

        if ($branchIds !== null) {
            $query->where(function ($q) use ($branchIds) {
                $q->whereHas('fromLocation', fn ($l) => $l->whereIn('branch_id', $branchIds))
                  ->orWhereHas('toLocation', fn ($l) => $l->whereIn('branch_id', $branchIds));
            });
        }

        $query = $this->applyDateRange($query, $request, 'dispatch_date');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $totalTransfers = (clone $query)->count();
        $transfers = $query->paginate(25)->withQueryString();

        return view('reports.transfers', compact('transfers', 'totalTransfers'));
    }

    /**
     * Export Stock Transfers Report to CSV
     */
    public function exportTransfers(Request $request): StreamedResponse
    {
        $user = auth()->user();
        abort_unless($user->hasRole('Super Admin') || $user->hasRole('Manager') || $user->hasRole('Auditor'), 403);

        ActivityLogger::log(
            module: 'Reports',
            actionType: 'export',
            description: "User {$user->name} exported Stock Transfers Report to CSV.",
            properties: ['format' => 'CSV']
        );

        $branchIds = $this->getAuthorizedBranchIds($user);

        $query = StockTransfer::with(['fromLocation.branch', 'toLocation.branch', 'user', 'items.product'])->latest('id');

        if ($branchIds !== null) {
            $query->where(function ($q) use ($branchIds) {
                $q->whereHas('fromLocation', fn ($l) => $l->whereIn('branch_id', $branchIds))
                  ->orWhereHas('toLocation', fn ($l) => $l->whereIn('branch_id', $branchIds));
            });
        }

        $query = $this->applyDateRange($query, $request, 'dispatch_date');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $filename = 'stock_transfers_report_' . date('Y_m_d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'Transfer Reference',
                'Dispatch Date',
                'From Location',
                'From Branch',
                'To Location',
                'To Branch',
                'Initiated By',
                'Status',
                'Items Count',
                'Items Summary',
                'Notes',
            ]);

            $query->chunk(200, function ($rows) use ($handle) {
                foreach ($rows as $t) {
                    $itemsSummary = $t->items->map(fn ($i) => ($i->product?->name ?? 'Item') . ' (' . number_format($i->quantity, 2) . ')')->implode('; ');

                    fputcsv($handle, [
                        $t->reference,
                        $t->dispatch_date ? $t->dispatch_date : '',
                        $t->fromLocation?->name ?? '—',
                        $t->fromLocation?->branch?->name ?? '—',
                        $t->toLocation?->name ?? '—',
                        $t->toLocation?->branch?->name ?? '—',
                        $t->user?->name ?? '—',
                        ucfirst($t->status),
                        $t->items->count(),
                        $itemsSummary,
                        $t->notes ?? '',
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }

    /* ─────────────────────────── HELPER METHODS ─────────────────────────── */

    protected function getAuthorizedBranchIds($user): ?\Illuminate\Support\Collection
    {
        if ($user->hasRole('Super Admin')) {
            return null; // Unrestricted access
        }

        return $user->authorizedBranchIds();
    }

    protected function applyDateRange($query, Request $request, string $column = 'created_at')
    {
        $period = $request->input('period');
        if ($period === 'today') {
            $query->whereDate($column, Carbon::today());
        } elseif ($period === 'this_week') {
            $query->whereBetween($column, [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($period === 'this_month') {
            $query->whereBetween($column, [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
        } elseif ($period === 'this_year') {
            $query->whereBetween($column, [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()]);
        } elseif ($request->filled('from') || $request->filled('to')) {
            if ($request->filled('from')) {
                $query->where($column, '>=', Carbon::parse($request->from)->startOfDay());
            }
            if ($request->filled('to')) {
                $query->where($column, '<=', Carbon::parse($request->to)->endOfDay());
            }
        }
        return $query;
    }

    protected function applySalesFilters($query, Request $request)
    {
        $query = $this->applyDateRange($query, $request, 'created_at');

        if ($request->filled('branch_id')) {
            $query->where('branch_id', (int) $request->branch_id);
        }

        if ($request->filled('seller_id')) {
            $query->where('user_id', (int) $request->seller_id);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('reference', 'like', $term)
                  ->orWhere('customer_name', 'like', $term)
                  ->orWhere('customer_phone', 'like', $term);
            });
        }

        return $query;
    }

    protected function applyProductionFilters($query, Request $request)
    {
        $query = $this->applyDateRange($query, $request, 'planned_date');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', (int) $request->product_id);
        }

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('reference', 'like', $term)
                  ->orWhere('production_notes', 'like', $term);
            });
        }

        return $query;
    }

    /**
     * Export User Performance Report to CSV.
     */
    public function exportUserPerformance(Request $request)
    {
        $user = auth()->user();
        abort_unless($user && ($user->hasRole('Super Admin') || $user->hasRole('Manager') || $user->hasRole('Auditor')), 403);

        ActivityLogger::log(
            module: 'Reports',
            actionType: 'export',
            description: "User {$user->name} exported User Performance Report to CSV.",
            properties: ['format' => 'CSV']
        );

        $branchIds = $this->getAuthorizedBranchIds($user);

        // Fetch internal users
        $usersQuery = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['Super Admin', 'Manager', 'Seller', 'Production Officer', 'Auditor']);
        })->with(['roles', 'branches']);

        if ($branchIds !== null) {
            $usersQuery->where(function ($uq) use ($branchIds) {
                $uq->whereHas('branches', fn ($q) => $q->whereIn('branches.id', $branchIds))
                   ->orWhereHas('roles', fn ($rq) => $rq->where('name', 'Super Admin'));
            });
        }

        $users = $usersQuery->get();

        $filename = 'user_performance_report_' . date('Y_m_d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($users, $branchIds) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM for Excel

            fputcsv($handle, [
                'Staff Member',
                'Email',
                'Role',
                'Branch Assignments',
                'Sales Count',
                'Sales Volume (ETB)',
                'Mfg Orders Created',
                'Completed Mfg Output (Units)',
                'System Activities Logged',
                'Account Status',
            ]);

            foreach ($users as $u) {
                $salesQuery = Sale::where('user_id', $u->id);
                $mfgQuery = ManufacturingOrder::where('user_id', $u->id);

                if ($branchIds !== null) {
                    $salesQuery->whereIn('branch_id', $branchIds);
                }

                $salesCount = (clone $salesQuery)->count();
                $salesVol   = (clone $salesQuery)->sum('total');
                $mfgCount   = (clone $mfgQuery)->count();
                $mfgGoodQty = (clone $mfgQuery)->where('status', 'completed')->sum('good_quantity');
                $actCount   = \Spatie\Activitylog\Models\Activity::where('causer_id', $u->id)->count();

                $branchNames = $u->branches->pluck('name')->join(', ') ?: ($u->hasRole('Super Admin') ? 'All Branches (Global)' : 'None');

                fputcsv($handle, [
                    $u->name,
                    $u->email,
                    $u->roles->pluck('name')->join(', ') ?: 'Staff',
                    $branchNames,
                    $salesCount,
                    number_format($salesVol, 2, '.', ''),
                    $mfgCount,
                    number_format($mfgGoodQty, 2, '.', ''),
                    $actCount,
                    $u->is_active ? 'Active' : 'Inactive',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}

