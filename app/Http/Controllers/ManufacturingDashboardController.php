<?php

namespace App\Http\Controllers;

use App\Models\InventoryBalance;
use App\Models\ManufacturingOrder;
use App\Models\MaterialIssue;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class ManufacturingDashboardController extends Controller
{
    public function index(Request $request)
    {
        // Total & Status Counts
        $totalOrders = ManufacturingOrder::count();
        $pendingOrders = ManufacturingOrder::whereIn('status', ['draft', 'pending', 'pending_approval', 'planned', 'approved'])->count();
        $inProgressOrders = ManufacturingOrder::whereIn('status', ['materials_issued', 'in_progress', 'In Progress'])->count();
        $completedOrders = ManufacturingOrder::where('status', 'completed')->count();
        $cancelledOrders = ManufacturingOrder::where('status', 'cancelled')->count();

        // Production output totals
        $totalFinishedProduced = (float) ManufacturingOrder::where('status', 'completed')->sum('good_quantity');
        $totalRejected = (float) ManufacturingOrder::where('status', 'completed')->sum('rejected_quantity');
        $totalWastedMaterials = (float) MaterialIssue::sum('quantity_wasted');

        // Efficiency
        $completedList = ManufacturingOrder::where('status', 'completed')->where('planned_quantity', '>', 0)->get();
        $avgEfficiency = $completedList->count() > 0
            ? round($completedList->avg(fn ($o) => $o->calculateEfficiency()), 1)
            : 100.0;

        // Raw Materials Count & Alert
        $rawMaterialsQuery = Product::where('type', 'raw_material')->where('is_active', true);
        $totalRawMaterialsCount = $rawMaterialsQuery->count();

        // Raw Materials below minimum stock level
        $lowStockMaterials = Product::where('type', 'raw_material')
            ->where('is_active', true)
            ->where('minimum_stock_level', '>', 0)
            ->whereColumn('quantity', '<=', 'minimum_stock_level')
            ->with(['unit', 'category'])
            ->get();

        // Active production orders needing materials
        $activeOrders = ManufacturingOrder::with(['product.unit', 'assignedUser', 'storageLocation', 'materialIssues.product'])
            ->whereIn('status', ['draft', 'pending_approval', 'approved', 'materials_issued', 'in_progress', 'In Progress', 'Pending'])
            ->latest()
            ->limit(10)
            ->get();

        // Recent completed manufacturing history
        $recentCompleted = ManufacturingOrder::with(['product.unit', 'assignedUser', 'materialIssues.product'])
            ->where('status', 'completed')
            ->latest('actual_completion_date')
            ->limit(5)
            ->get();

        // Raw materials overview with current quantities
        $rawMaterialsList = Product::where('type', 'raw_material')
            ->where('is_active', true)
            ->with(['unit', 'category'])
            ->orderBy('name')
            ->paginate(10);

        // Recent stock movements for manufacturing
        $recentStockMovements = StockMovement::where('reference_type', ManufacturingOrder::class)
            ->with(['product', 'storageLocation', 'user'])
            ->latest()
            ->limit(10)
            ->get();

        return view('manufacturing_orders.dashboard', compact(
            'totalOrders',
            'pendingOrders',
            'inProgressOrders',
            'completedOrders',
            'cancelledOrders',
            'totalFinishedProduced',
            'totalRejected',
            'totalWastedMaterials',
            'avgEfficiency',
            'totalRawMaterialsCount',
            'lowStockMaterials',
            'activeOrders',
            'recentCompleted',
            'rawMaterialsList',
            'recentStockMovements'
        ));
    }
}
