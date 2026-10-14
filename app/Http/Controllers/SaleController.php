<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleLine;
use App\Models\StorageLocation;
use App\Services\SaleService;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    /**
     * Display a listing of all sales (scoped to user's authorized branches).
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Sale::with(['user', 'branch'])->latest();

        // Scope by role
        if ($user->hasRole('Seller')) {
            $query->where('user_id', $user->id);
        } elseif (!$user->hasRole('Super Admin') && !$user->hasRole('Auditor')) {
            $branchIds = $user->authorizedBranchIds();
            if ($branchIds->isNotEmpty()) {
                $query->whereIn('branch_id', $branchIds);
            }
        }

        // Filters
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('reference', 'like', '%' . $request->search . '%')
                  ->orWhere('customer_name', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $sales = $query->paginate(20)->withQueryString();

        // Summary (global for super admin, scoped for others)
        $sumQuery = Sale::query();
        if ($user->hasRole('Seller')) {
            $sumQuery->where('user_id', $user->id);
        } elseif (!$user->hasRole('Super Admin') && !$user->hasRole('Auditor')) {
            $branchIds = $user->authorizedBranchIds();
            $sumQuery->whereIn('branch_id', $branchIds);
        }

        $summary = [
            'total_revenue' => (clone $sumQuery)->sum('total'),
            'total_count'   => (clone $sumQuery)->count(),
            'unpaid'        => (clone $sumQuery)->where('payment_status', '!=', 'Paid')->sum(DB::raw('total - paid_amount')),
            'today'         => (clone $sumQuery)->whereDate('created_at', today())->sum('total'),
        ];

        return view('sales.index', compact('sales', 'summary'));
    }

    /**
     * Show the POS / quick sale creation form.
     */
    public function create()
    {
        $user = auth()->user();

        // Sellers must have an assigned branch
        $defaultBranch = null;
        if ($user->hasRole('Seller')) {
            $defaultBranch = $user->branches()->where('is_active', true)->first();
        }

        // Branches available for managers/admins
        $branches = [];
        if ($user->hasRole('Super Admin')) {
            $branches = Branch::where('is_active', true)->orderBy('name')->get();
        } elseif ($user->hasRole('Manager')) {
            $branches = $user->branches()->where('is_active', true)->orderBy('name')->get();
        }

        // Storage locations
        $locations = [];
        if ($defaultBranch) {
            $locations = StorageLocation::where('branch_id', $defaultBranch->id)
                ->where('is_active', true)->get();
        } elseif ($branches instanceof \Illuminate\Support\Collection && $branches->isNotEmpty()) {
            $branchIds = $branches->pluck('id');
            $locations = StorageLocation::whereIn('branch_id', $branchIds)
                ->where('is_active', true)->with('branch')->get();
        }

        return view('sales.create', compact('defaultBranch', 'branches', 'locations'));
    }

    /**
     * Store a quick sale (POS transaction).
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'branch_id'          => 'nullable|integer|exists:branches,id',
            'storage_location_id'=> 'nullable|integer|exists:storage_locations,id',
            'customer_name'      => 'nullable|string|max:200',
            'customer_phone'     => 'nullable|string|max:50',
            'order_notes'        => 'nullable|string|max:1000',
            'payment_status'     => 'required|in:Unpaid,Partial,Paid',
            'paid_amount'        => 'nullable|numeric|min:0',
            'discount'           => 'nullable|numeric|min:0',
            'tax'                => 'nullable|numeric|min:0',
            'subtotal'           => 'nullable|numeric|min:0',
            'total'              => 'nullable|numeric|min:0',
            'lines'              => 'required|array|min:1',
            'lines.*.product_id' => 'required|integer|exists:products,id',
            'lines.*.quantity'   => 'required|numeric|min:0.01',
            'lines.*.unit_price' => 'required|numeric|min:0',
        ]);

        try {
            $saleService = new SaleService();

            // Resolve branch
            $branchId = $request->branch_id ? (int) $request->branch_id : null;
            $branch   = $saleService->resolveAuthorizedBranch($user, $branchId);

            // Resolve storage location
            $locationId = $request->storage_location_id ? (int) $request->storage_location_id : null;
            $location   = $saleService->resolveStorageLocation($branch, $locationId);

            // Validate stock before committing
            $saleService->validateBranchStock($branch, $validated['lines']);

            DB::transaction(function () use ($validated, $user, $branch, $location, $saleService) {
                // Calculate totals from submitted lines
                $subtotal = collect($validated['lines'])->sum(fn ($l) => (float) $l['unit_price'] * (float) $l['quantity']);
                $discount = (float) ($validated['discount'] ?? 0);
                $tax      = (float) ($validated['tax'] ?? 0);
                $total    = max(0, $subtotal - $discount + $tax);

                $paymentStatus = $validated['payment_status'];
                $paidAmount    = (float) ($validated['paid_amount'] ?? 0);

                if ($paymentStatus === 'Paid') {
                    $paidAmount = $total;
                } elseif ($paymentStatus === 'Unpaid') {
                    $paidAmount = 0;
                } else {
                    $paidAmount = min($paidAmount, $total);
                }

                // Generate reference
                $reference = 'SL-' . strtoupper(date('ymd')) . '-' . str_pad(
                    Sale::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT
                );

                $sale = Sale::create([
                    'reference'           => $reference,
                    'type'                => 'quick_sale',
                    'user_id'             => $user->id,
                    'branch_id'           => $branch->id,
                    'storage_location_id' => $location->id,
                    'customer_name'       => $validated['customer_name'] ?? null,
                    'customer_phone'      => $validated['customer_phone'] ?? null,
                    'order_notes'         => $validated['order_notes'] ?? null,
                    'subtotal'            => $subtotal,
                    'discount'            => $discount,
                    'tax'                 => $tax,
                    'total'               => $total,
                    'paid_amount'         => $paidAmount,
                    'payment_status'      => $paymentStatus,
                    'status'              => 'confirmed',
                    'is_stock_deducted'   => false,
                ]);

                // Create sale lines
                foreach ($validated['lines'] as $line) {
                    $product = Product::find($line['product_id']);
                    SaleLine::create([
                        'sale_id'    => $sale->id,
                        'product_id' => $line['product_id'],
                        'quantity'   => $line['quantity'],
                        'unit_price' => $line['unit_price'],
                        'unit_cost'  => $product?->purchase_cost ?? 0,
                        'total'      => (float) $line['unit_price'] * (float) $line['quantity'],
                    ]);
                }

                // Load lines for deduction
                $sale->load('lines');

                // Deduct stock
                $saleService->deductBranchStock($branch, $sale, $user);
                $sale->update(['is_stock_deducted' => true]);

                // Log the activity
                ActivityLogger::log(
                    module: 'Sales',
                    actionType: 'create',
                    description: "Sale {$reference} created by {$user->name} at {$branch->name}. Total: Br" . number_format($total, 2) . " ({$paymentStatus}).",
                    subject: $sale,
                    properties: [
                        'reference'   => $reference,
                        'branch_id'   => $branch->id,
                        'branch_name' => $branch->name,
                        'total'       => $total,
                        'payment_status' => $paymentStatus,
                    ]
                );

                session(['_last_sale_id' => $sale->id, '_last_sale_ref' => $reference]);
            });

            $lastSaleId = session('_last_sale_id');
            return redirect()->route('sales.create')
                ->with('success', "Sale " . session('_last_sale_ref') . " completed successfully! Stock deducted from {$branch->name}.");

        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Sale creation failed: ' . $e->getMessage());
            return back()->withInput()->with('error', 'An unexpected error occurred. Please try again.');
        }
    }

    /**
     * Show a specific sale's details.
     */
    public function show(Sale $sale)
    {
        $user = auth()->user();

        // Authorization
        if ($user->hasRole('Seller') && $sale->user_id !== $user->id) {
            abort(403, 'You do not have permission to view this sale.');
        }

        $sale->load(['lines.product.unit', 'user', 'branch', 'storageLocation', 'payments']);

        return view('sales.show', compact('sale'));
    }

    /**
     * Show the form for editing (not used in POS flow).
     */
    public function edit(Sale $sale)
    {
        abort(403, 'Sales cannot be edited after confirmation.');
    }

    /**
     * Update — reserved for payment/status updates only.
     */
    public function update(Request $request, Sale $sale)
    {
        abort(403, 'Sales cannot be modified after confirmation.');
    }

    /**
     * Remove — prevent deletion; use returns/voids instead.
     */
    public function destroy(Sale $sale)
    {
        abort(403, 'Sales cannot be deleted. Create a Sale Return instead.');
    }
}
