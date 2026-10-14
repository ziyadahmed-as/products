<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ManufacturingOrder;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\StorageLocation;
use App\Models\UnitOfMeasure;
use App\Models\User;
use App\Services\ManufacturingService;
use Exception;
use Illuminate\Http\Request;

class ManufacturingOrderController extends Controller
{
    protected ManufacturingService $service;

    public function __construct(ManufacturingService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of manufacturing orders.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        abort_unless(
            $user && ($user->hasRole('Super Admin') || $user->hasRole('Manager') || $user->hasRole('Production Officer') || $user->hasRole('Auditor')),
            403,
            'Unauthorized access to manufacturing orders.'
        );

        $query = ManufacturingOrder::with(['product.unit', 'storageLocation.branch', 'user', 'assignedUser', 'materialIssues.product'])
            ->latest('id');

        // Branch scoping for managers and production officers
        if (!$user->hasRole('Super Admin') && !$user->hasRole('Auditor')) {
            $branchIds = $user->authorizedBranchIds();
            $query->whereHas('storageLocation', fn ($q) => $q->whereIn('branch_id', $branchIds));
        }

        // Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by Reference or Product
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhereHas('product', function ($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%")
                         ->orWhere('sku', 'like', "%{$search}%");
                  });
            });
        }

        // Date Range
        if ($request->filled('start_date')) {
            $query->whereDate('planned_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('planned_date', '<=', $request->end_date);
        }

        $orders = $query->paginate(15)->withQueryString();

        $baseCountQuery = ManufacturingOrder::query();
        if (!$user->hasRole('Super Admin') && !$user->hasRole('Auditor')) {
            $branchIds = $user->authorizedBranchIds();
            $baseCountQuery->whereHas('storageLocation', fn ($q) => $q->whereIn('branch_id', $branchIds));
        }

        $statusCounts = [
            'all'              => (clone $baseCountQuery)->count(),
            'draft'            => (clone $baseCountQuery)->where('status', 'draft')->count(),
            'pending_approval' => (clone $baseCountQuery)->where('status', 'pending_approval')->count(),
            'approved'         => (clone $baseCountQuery)->where('status', 'approved')->count(),
            'materials_issued' => (clone $baseCountQuery)->where('status', 'materials_issued')->count(),
            'in_progress'      => (clone $baseCountQuery)->whereIn('status', ['in_progress', 'In Progress'])->count(),
            'completed'        => (clone $baseCountQuery)->where('status', 'completed')->count(),
            'cancelled'        => (clone $baseCountQuery)->where('status', 'cancelled')->count(),
        ];

        return view('manufacturing_orders.index', compact('orders', 'statusCounts'));
    }

    /**
     * Show the form for creating a new manufacturing order.
     */
    public function create(Request $request)
    {
        $user = auth()->user();
        abort_unless(
            $user && ($user->hasRole('Super Admin') || $user->hasRole('Manager') || $user->hasRole('Production Officer')),
            403,
            'Only Super Admin, Managers, and Manufacturing Staff can create manufacturing orders.'
        );

        // Eligible finished products
        $products = Product::where('is_active', true)
            ->where(function ($q) {
                $q->whereIn('type', ['manufactured_product', 'resale_product'])
                  ->orWhereHas('recipe');
            })
            ->with(['recipe.materials.rawMaterial', 'unit'])
            ->orderBy('name')
            ->get();

        $rawMaterials = Product::where('is_active', true)
            ->where('type', 'raw_material')
            ->with(['unit', 'inventoryBalances'])
            ->orderBy('name')
            ->get();

        // Scope storage locations to user's permitted branches
        if ($user->hasRole('Super Admin')) {
            $storageLocations = StorageLocation::where('is_active', true)->with('branch')->get();
        } else {
            $storageLocations = StorageLocation::whereIn('branch_id', $user->authorizedBranchIds())->where('is_active', true)->with('branch')->get();
        }

        $recipes = Recipe::where('is_active', true)->with('materials.rawMaterial')->get();
        $units = UnitOfMeasure::orderBy('name')->get();
        $staffMembers = User::where('is_active', true)
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', ['Production Officer', 'Manager', 'Super Admin']);
            })
            ->get();

        if ($staffMembers->isEmpty()) {
            $staffMembers = User::where('is_active', true)->get();
        }

        $selectedProductId = $request->query('product_id');

        return view('manufacturing_orders.create', compact(
            'products',
            'rawMaterials',
            'storageLocations',
            'recipes',
            'units',
            'staffMembers',
            'selectedProductId'
        ));
    }

    /**
     * Store a newly created manufacturing order.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        abort_unless(
            $user && ($user->hasRole('Super Admin') || $user->hasRole('Manager') || $user->hasRole('Production Officer')),
            403,
            'Only Super Admin, Managers, and Manufacturing Staff can create manufacturing orders.'
        );

        $validated = $request->validate([
            'product_id'               => 'required|exists:products,id',
            'recipe_id'                => 'nullable|exists:recipes,id',
            'planned_quantity'         => 'required|numeric|min:0.01',
            'unit_of_measure_id'       => 'nullable|exists:unit_of_measures,id',
            'storage_location_id'      => 'required|exists:storage_locations,id',
            'planned_date'             => 'required|date',
            'start_date'               => 'nullable|date',
            'expected_completion_date' => 'nullable|date|after_or_equal:start_date',
            'assigned_user_id'         => 'nullable|exists:users,id',
            'production_notes'         => 'nullable|string|max:1000',
            'additional_costs'         => 'nullable|numeric|min:0',
            'materials'                => 'nullable|array',
            'materials.*.product_id'   => 'required_with:materials|exists:products,id',
            'materials.*.quantity'     => 'required_with:materials|numeric|min:0.001',
        ]);

        $location = StorageLocation::findOrFail($validated['storage_location_id']);
        if (!$user->hasRole('Super Admin') && !$user->canAccessBranch($location->branch_id)) {
            abort(403, 'You are not authorized to create production orders for this branch storage location.');
        }

        try {
            $order = $this->service->createOrder($validated, $user);

            return redirect()->route('manufacturing-orders.show', $order)
                ->with('success', "Manufacturing Order #{$order->reference} created successfully.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Error creating manufacturing order: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified manufacturing order with details and actions.
     */
    public function show(ManufacturingOrder $manufacturing_order)
    {
        $user = auth()->user();
        abort_unless(
            $user && ($user->hasRole('Super Admin') || $user->hasRole('Manager') || $user->hasRole('Production Officer') || $user->hasRole('Auditor')),
            403,
            'Unauthorized to view this manufacturing order.'
        );

        $order = $manufacturing_order->load([
            'product.unit',
            'product.category',
            'recipe.materials.rawMaterial',
            'storageLocation.branch',
            'unit',
            'user',
            'assignedUser',
            'materialIssues.product.unit',
            'materialIssues.product.inventoryBalances',
            'materialIssues.unit',
            'stockMovements.product',
            'stockMovements.user',
            'stockMovements.storageLocation',
        ]);

        // Branch check for manager / production officer
        $branchId = $order->storageLocation?->branch_id;
        if (!$user->hasRole('Super Admin') && !$user->hasRole('Auditor') && $branchId && !$user->canAccessBranch($branchId)) {
            abort(403, 'You are not authorized to view manufacturing orders for this branch.');
        }

        // Evaluate live stock availability for the materials
        $materialAvailability = [];
        foreach ($order->materialIssues as $issue) {
            $availableInLocation = (float) $issue->product->inventoryBalances
                ->where('storage_location_id', $order->storage_location_id)
                ->sum('quantity');

            $totalStock = (float) $issue->product->quantity;
            $needed = (float) $issue->quantity_planned;

            $materialAvailability[$issue->id] = [
                'available_location' => $availableInLocation,
                'available_total'    => $totalStock,
                'is_sufficient'      => $availableInLocation >= $needed || $totalStock >= $needed,
            ];
        }

        $allSufficient = collect($materialAvailability)->every(fn ($item) => $item['is_sufficient']);

        return view('manufacturing_orders.show', compact('order', 'materialAvailability', 'allSufficient'));
    }

    /**
     * Show edit form for draft orders.
     */
    public function edit(ManufacturingOrder $manufacturing_order)
    {
        $user = auth()->user();
        abort_unless(
            $user && ($user->hasRole('Super Admin') || $user->hasRole('Manager') || $user->hasRole('Production Officer')),
            403,
            'Unauthorized to edit this manufacturing order.'
        );

        if (!in_array(strtolower($manufacturing_order->status), ['draft', 'pending', 'pending_approval'])) {
            return redirect()->route('manufacturing-orders.show', $manufacturing_order)
                ->with('error', "Only draft orders can be edited.");
        }

        $order = $manufacturing_order->load(['materialIssues.product']);
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $rawMaterials = Product::where('is_active', true)->where('type', 'raw_material')->orderBy('name')->get();

        if ($user->hasRole('Super Admin')) {
            $storageLocations = StorageLocation::where('is_active', true)->get();
        } else {
            $storageLocations = StorageLocation::whereIn('branch_id', $user->authorizedBranchIds())->where('is_active', true)->get();
        }

        $recipes = Recipe::where('is_active', true)->get();
        $units = UnitOfMeasure::orderBy('name')->get();
        $staffMembers = User::where('is_active', true)->get();

        return view('manufacturing_orders.edit', compact(
            'order',
            'products',
            'rawMaterials',
            'storageLocations',
            'recipes',
            'units',
            'staffMembers'
        ));
    }

    /**
     * Update order details.
     */
    public function update(Request $request, ManufacturingOrder $manufacturing_order)
    {
        $user = auth()->user();
        abort_unless(
            $user && ($user->hasRole('Super Admin') || $user->hasRole('Manager') || $user->hasRole('Production Officer')),
            403,
            'Unauthorized to update this manufacturing order.'
        );

        if (!in_array(strtolower($manufacturing_order->status), ['draft', 'pending', 'pending_approval'])) {
            return redirect()->route('manufacturing-orders.show', $manufacturing_order)
                ->with('error', "Cannot update order in current status.");
        }

        $validated = $request->validate([
            'planned_quantity'         => 'required|numeric|min:0.01',
            'storage_location_id'      => 'required|exists:storage_locations,id',
            'planned_date'             => 'required|date',
            'start_date'               => 'nullable|date',
            'expected_completion_date' => 'nullable|date|after_or_equal:start_date',
            'assigned_user_id'         => 'nullable|exists:users,id',
            'production_notes'         => 'nullable|string|max:1000',
            'additional_costs'         => 'nullable|numeric|min:0',
        ]);

        $manufacturing_order->update($validated);

        return redirect()->route('manufacturing-orders.show', $manufacturing_order)
            ->with('success', "Order #{$manufacturing_order->reference} updated successfully.");
    }

    /**
     * Delete order if draft.
     */
    public function destroy(ManufacturingOrder $manufacturing_order)
    {
        $user = auth()->user();
        abort_unless(
            $user && ($user->hasRole('Super Admin') || $user->hasRole('Manager')),
            403,
            'Only Super Admin and Managers can delete manufacturing orders.'
        );

        if (!in_array(strtolower($manufacturing_order->status), ['draft', 'pending', 'cancelled'])) {
            return redirect()->route('manufacturing-orders.index')
                ->with('error', "Cannot delete an order that is {$manufacturing_order->status}.");
        }

        $manufacturing_order->materialIssues()->delete();
        $manufacturing_order->delete();

        return redirect()->route('manufacturing-orders.index')
            ->with('success', "Order removed successfully.");
    }

    /**
     * Submit order for approval.
     */
    public function submitApproval(ManufacturingOrder $order)
    {
        $user = auth()->user();
        abort_unless(
            $user && ($user->hasRole('Super Admin') || $user->hasRole('Manager') || $user->hasRole('Production Officer')),
            403,
            'Unauthorized to submit this order for approval.'
        );

        try {
            $this->service->submitForApproval($order, $user);
            return back()->with('success', "Order #{$order->reference} submitted for approval.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Approve order.
     */
    public function approve(ManufacturingOrder $order)
    {
        $user = auth()->user();
        abort_unless(
            $user && ($user->hasRole('Super Admin') || $user->hasRole('Manager')),
            403,
            'Only Super Admin and Store Managers can approve manufacturing orders.'
        );

        try {
            $this->service->approveOrder($order, $user);
            return back()->with('success', "Order #{$order->reference} has been approved.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Issue materials and deduct raw material stock.
     */
    public function issueMaterials(ManufacturingOrder $order)
    {
        $user = auth()->user();
        abort_unless(
            $user && ($user->hasRole('Super Admin') || $user->hasRole('Manager') || $user->hasRole('Production Officer')),
            403,
            'Unauthorized to issue materials for this manufacturing order.'
        );

        $branchId = $order->storageLocation?->branch_id;
        if (!$user->hasRole('Super Admin') && $branchId && !$user->canAccessBranch($branchId)) {
            abort(403, 'You are not authorized to process orders for this branch.');
        }

        try {
            $this->service->issueMaterials($order, $user);
            return back()->with('success', "Raw materials successfully issued and stock deducted for Order #{$order->reference}.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Transition order to In Production.
     */
    public function startProduction(ManufacturingOrder $order)
    {
        $user = auth()->user();
        abort_unless(
            $user && ($user->hasRole('Super Admin') || $user->hasRole('Manager') || $user->hasRole('Production Officer')),
            403,
            'Unauthorized to start production for this order.'
        );

        $branchId = $order->storageLocation?->branch_id;
        if (!$user->hasRole('Super Admin') && $branchId && !$user->canAccessBranch($branchId)) {
            abort(403, 'You are not authorized to process orders for this branch.');
        }

        try {
            $this->service->startProduction($order, $user);
            return back()->with('success', "Production started for Order #{$order->reference}.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Complete production and register finished products.
     */
    public function complete(Request $request, ManufacturingOrder $order)
    {
        $user = auth()->user();
        abort_unless(
            $user && ($user->hasRole('Super Admin') || $user->hasRole('Manager') || $user->hasRole('Production Officer')),
            403,
            'Unauthorized to complete this manufacturing order.'
        );

        $branchId = $order->storageLocation?->branch_id;
        if (!$user->hasRole('Super Admin') && $branchId && !$user->canAccessBranch($branchId)) {
            abort(403, 'You are not authorized to process orders for this branch.');
        }

        $validated = $request->validate([
            'good_quantity'      => 'required|numeric|min:0',
            'rejected_quantity'  => 'nullable|numeric|min:0',
            'additional_costs'   => 'nullable|numeric|min:0',
            'wastage_reason'     => 'nullable|string|max:500',
            'production_notes'   => 'nullable|string|max:1000',
            'materials'          => 'nullable|array',
            'materials.*.quantity_consumed' => 'nullable|numeric|min:0',
            'materials.*.quantity_returned' => 'nullable|numeric|min:0',
            'materials.*.quantity_wasted'   => 'nullable|numeric|min:0',
            'materials.*.wastage_reason'    => 'nullable|string|max:255',
        ]);

        try {
            $this->service->completeProduction($order, $validated, $user);
            return back()->with('success', "Production completed! Accepted finished goods registered in inventory.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel order and revert stock if materials were issued.
     */
    public function cancel(Request $request, ManufacturingOrder $order)
    {
        $user = auth()->user();
        abort_unless(
            $user && ($user->hasRole('Super Admin') || $user->hasRole('Manager') || $user->hasRole('Production Officer')),
            403,
            'Unauthorized to cancel this manufacturing order.'
        );

        $branchId = $order->storageLocation?->branch_id;
        if (!$user->hasRole('Super Admin') && $branchId && !$user->canAccessBranch($branchId)) {
            abort(403, 'You are not authorized to process orders for this branch.');
        }

        $validated = $request->validate([
            'cancel_reason' => 'nullable|string|max:500',
        ]);

        try {
            $this->service->cancelOrder($order, $validated['cancel_reason'] ?? null, $user);
            return back()->with('success', "Order #{$order->reference} cancelled. Any issued raw materials have been returned to inventory.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
