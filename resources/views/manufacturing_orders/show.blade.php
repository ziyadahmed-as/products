@extends('layouts.app')
@section('title', 'Manufacturing Order #' . $order->reference)

@section('content')
<div class="space-y-6">

    <!-- Header & Navigation -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ route('manufacturing-orders.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight flex items-center gap-2">
                    Order <span class="text-primary-600 dark:text-primary-400 font-mono">#{{ $order->reference }}</span>
                </h1>
                @php
                    $st = strtolower($order->status);
                    $badgeClass = match($st) {
                        'draft'            => 'bg-gray-100 dark:bg-dark-800 text-gray-700 dark:text-dark-300 border border-gray-200 dark:border-dark-700',
                        'pending', 'pending_approval' => 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60',
                        'approved'         => 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800/60',
                        'materials_issued' => 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800/60',
                        'in_progress'      => 'bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800/60',
                        'completed'        => 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60',
                        'cancelled'        => 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800/60',
                        default            => 'bg-gray-100 dark:bg-dark-800 text-gray-700 dark:text-dark-300',
                    };
                @endphp
                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $badgeClass }}">
                    {{ ucwords(str_replace('_', ' ', $order->status)) }}
                </span>
            </div>
            <p class="text-gray-500 dark:text-dark-400 text-sm mt-1">
                Created on {{ $order->created_at->format('M d, Y H:i') }} by {{ $order->user->name ?? 'System' }}
            </p>
        </div>

        <div class="flex items-center gap-2">
            @if(in_array($st, ['draft', 'pending']))
                <a href="{{ route('manufacturing-orders.edit', $order) }}" class="btn-secondary text-xs px-3 py-2">
                    Edit Order
                </a>
            @endif
            <a href="{{ route('manufacturing-orders.create') }}" class="btn-secondary text-xs px-3 py-2">
                + New Order
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800/50 text-emerald-800 dark:text-emerald-300 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800/50 text-rose-800 dark:text-rose-300 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Lifecycle Stepper -->
    @php
        $steps = [
            'draft'            => 'Draft',
            'pending_approval' => 'Pending Approval',
            'approved'         => 'Approved',
            'materials_issued' => 'Materials Issued',
            'in_progress'      => 'In Production',
            'completed'        => 'Completed',
        ];

        $statusIndex = match($st) {
            'draft'            => 0,
            'pending', 'pending_approval' => 1,
            'approved'         => 2,
            'materials_issued' => 3,
            'in_progress'      => 4,
            'completed'        => 5,
            'cancelled'        => -1,
            default            => 0,
        };
    @endphp

    <div class="card p-4">
        @if($st === 'cancelled')
            <div class="p-3 rounded-lg bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/50 flex items-center gap-3 text-rose-800 dark:text-rose-300 text-sm">
                <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                <div>
                    <span class="font-bold">This production order was cancelled.</span>
                    @if($order->is_materials_issued)
                        <span class="text-xs text-rose-600 dark:text-rose-400 block mt-0.5">All issued raw materials were securely returned to inventory.</span>
                    @endif
                </div>
            </div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2">
                @php $i = 0; @endphp
                @foreach($steps as $stepKey => $stepLabel)
                    @php
                        $isCurrent = ($statusIndex === $i);
                        $isPassed = ($statusIndex > $i);
                        $i++;
                    @endphp
                    <div class="p-2.5 rounded-lg border text-center transition {{ $isPassed ? 'bg-emerald-50 dark:bg-emerald-950/30 border-emerald-200 dark:border-emerald-800/40 text-emerald-700 dark:text-emerald-300' : ($isCurrent ? 'bg-primary-50 dark:bg-primary-950/50 border-primary-500 dark:border-primary-600/60 text-primary-900 dark:text-white ring-1 ring-primary-500/50 font-semibold' : 'bg-gray-50 dark:bg-dark-900/40 border-gray-200 dark:border-dark-800 text-gray-400 dark:text-dark-500') }}">
                        <div class="flex items-center justify-center gap-1.5 mb-1">
                            @if($isPassed)
                                <svg class="w-4 h-4 text-emerald-500 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            @elseif($isCurrent)
                                <span class="w-2 h-2 rounded-full bg-primary-600 dark:bg-primary-400 animate-pulse"></span>
                            @else
                                <span class="w-2 h-2 rounded-full bg-gray-300 dark:bg-dark-700"></span>
                            @endif
                            <span class="text-[11px] font-bold uppercase tracking-wider">{{ $stepLabel }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Lifecycle Action Bar -->
    <div class="card p-4 bg-gray-50 dark:bg-dark-900/80 border-gray-200 dark:border-primary-900/40 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Current Action Required:</h3>
            <p class="text-xs text-gray-500 dark:text-dark-400 mt-0.5">
                @if($st === 'draft')
                    Order is in Draft. Submit for approval or approve to prepare material issuance.
                @elseif(in_array($st, ['pending', 'pending_approval']))
                    Awaiting authorization. Manager or Production Lead must approve order.
                @elseif($st === 'approved')
                    Order is approved. Verify stock and issue raw materials to deduct inventory.
                @elseif($st === 'materials_issued')
                    Raw materials are issued and deducted. Begin production operations.
                @elseif($st === 'in_progress')
                    Production is running. Record actual consumption and register finished products upon completion.
                @elseif($st === 'completed')
                    Order completed. Finished goods are registered into active saleable inventory.
                @elseif($st === 'cancelled')
                    Order cancelled.
                @endif
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Submit for Approval -->
            @if($st === 'draft')
                <form method="POST" action="{{ route('manufacturing-orders.submit-approval', $order) }}">
                    @csrf
                    <button type="submit" class="btn-secondary text-xs px-3 py-2">
                        Submit For Approval
                    </button>
                </form>
                <form method="POST" action="{{ route('manufacturing-orders.approve', $order) }}">
                    @csrf
                    <button type="submit" class="btn-primary text-xs px-4 py-2 shadow-sm">
                        Approve Directly
                    </button>
                </form>
            @endif

            <!-- Approve -->
            @if(in_array($st, ['pending', 'pending_approval']))
                <form method="POST" action="{{ route('manufacturing-orders.approve', $order) }}">
                    @csrf
                    <button type="submit" class="btn-primary text-xs px-4 py-2 shadow-sm">
                        Approve Order
                    </button>
                </form>
            @endif

            <!-- Issue Materials -->
            @if($st === 'approved' && !$order->is_materials_issued)
                <form method="POST" action="{{ route('manufacturing-orders.issue-materials', $order) }}">
                    @csrf
                    <button type="submit" class="btn-primary text-xs px-4 py-2 shadow-sm flex items-center gap-1.5"
                            {{ !$allSufficient ? 'onclick="return confirm(\'Some raw materials show low stock. Attempt deduction anyway?\');"' : '' }}>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                        Issue Materials & Deduct Stock
                    </button>
                </form>
            @endif

            <!-- Start Production -->
            @if($st === 'materials_issued' || ($st === 'approved' && $order->is_materials_issued))
                <form method="POST" action="{{ route('manufacturing-orders.start-production', $order) }}">
                    @csrf
                    <button type="submit" class="btn-primary text-xs px-4 py-2 shadow-sm flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/></svg>
                        Start Production
                    </button>
                </form>
            @endif

            <!-- Complete Production Modal Trigger -->
            @if(in_array($st, ['in_progress', 'materials_issued']) && !$order->is_finished_registered)
                <button type="button" onclick="document.getElementById('complete-modal').classList.remove('hidden')"
                        class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold shadow-sm flex items-center gap-1.5 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Complete Production & Register Finished Product
                </button>
            @endif

            <!-- Cancel Order -->
            @if(!in_array($st, ['completed', 'cancelled']))
                <button type="button" onclick="document.getElementById('cancel-modal').classList.remove('hidden')"
                        class="btn-danger text-xs px-3 py-2">
                    Cancel Order
                </button>
            @endif
        </div>
    </div>

    <!-- Order Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Output Product & Target -->
        <div class="card space-y-3">
            <h2 class="text-xs font-bold text-gray-500 dark:text-dark-400 uppercase tracking-wider">Manufactured Product Output</h2>
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-base font-bold text-gray-900 dark:text-white">{{ $order->product->name }}</p>
                    <p class="text-xs text-gray-500 dark:text-dark-400">SKU: {{ $order->product->sku }} • {{ $order->product->category->name ?? 'Manufactured' }}</p>
                </div>
                <span class="px-2 py-0.5 rounded text-[11px] bg-gray-100 dark:bg-dark-800 text-gray-700 dark:text-dark-300 font-mono">
                    {{ $order->product->type }}
                </span>
            </div>

            <div class="pt-3 border-t border-gray-200 dark:border-dark-800 grid grid-cols-2 gap-3 text-xs">
                <div>
                    <span class="text-gray-400 dark:text-dark-500 block">Planned Quantity</span>
                    <span class="font-mono font-semibold text-gray-900 dark:text-white text-sm">
                        {{ $order->planned_quantity }} {{ $order->unit?->code ?? $order->product->unit?->code ?? 'units' }}
                    </span>
                </div>
                <div>
                    <span class="text-gray-400 dark:text-dark-500 block">Current In-Stock Total</span>
                    <span class="font-mono font-semibold text-emerald-600 dark:text-emerald-400 text-sm">
                        {{ $order->product->quantity }} {{ $order->product->unit?->code ?? 'units' }}
                    </span>
                </div>
            </div>

            @if($order->status === 'completed')
                <div class="p-3 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/40 text-xs space-y-1">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-dark-400">Accepted Good Output:</span>
                        <span class="font-mono font-bold text-emerald-700 dark:text-emerald-300">+{{ $order->good_quantity }} {{ $order->unit?->code ?? 'units' }}</span>
                    </div>
                    @if($order->rejected_quantity > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-dark-400">Rejected / Damaged:</span>
                            <span class="font-mono text-rose-600 dark:text-rose-400">{{ $order->rejected_quantity }} {{ $order->unit?->code ?? 'units' }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-dark-400">Efficiency Yield:</span>
                        <span class="font-mono text-gray-900 dark:text-white font-semibold">{{ $order->calculateEfficiency() }}%</span>
                    </div>
                </div>
            @endif
        </div>

        <!-- Location & Scheduling -->
        <div class="card space-y-3">
            <h2 class="text-xs font-bold text-gray-500 dark:text-dark-400 uppercase tracking-wider">Storage & Timeline</h2>
            <div>
                <span class="text-gray-400 dark:text-dark-500 text-xs block">Storage Location</span>
                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $order->storageLocation->name ?? 'Default Warehouse' }}</p>
                <p class="text-xs text-gray-500 dark:text-dark-400">Branch: {{ $order->storageLocation->branch->name ?? 'Main' }}</p>
            </div>

            <div class="pt-3 border-t border-gray-200 dark:border-dark-800 space-y-2 text-xs">
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-dark-400">Planned Date:</span>
                    <span class="text-gray-900 dark:text-white font-medium">{{ $order->planned_date?->format('M d, Y') ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-dark-400">Actual Start:</span>
                    <span class="text-gray-900 dark:text-white font-medium">{{ $order->actual_start_date?->format('M d, Y H:i') ?? '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-dark-400">Completed Date:</span>
                    <span class="text-gray-900 dark:text-white font-medium">{{ $order->actual_completion_date?->format('M d, Y H:i') ?? '—' }}</span>
                </div>
            </div>
        </div>

        <!-- Assignment & Costs -->
        <div class="card space-y-3">
            <h2 class="text-xs font-bold text-gray-500 dark:text-dark-400 uppercase tracking-wider">Assignment & Costs</h2>
            <div>
                <span class="text-gray-400 dark:text-dark-500 text-xs block">Assigned Staff / Team</span>
                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $order->assignedUser->name ?? 'Unassigned' }}</p>
                <p class="text-xs text-gray-500 dark:text-dark-400">{{ $order->assignedUser->email ?? '' }}</p>
            </div>

            <div class="pt-3 border-t border-gray-200 dark:border-dark-800 space-y-2 text-xs">
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-dark-400">Raw Material Cost:</span>
                    <span class="font-mono text-gray-900 dark:text-white">Br {{ number_format($order->calculateMaterialCost(), 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-dark-400">Additional Costs:</span>
                    <span class="font-mono text-gray-900 dark:text-white">Br {{ number_format($order->additional_costs, 2) }}</span>
                </div>
                <div class="flex justify-between font-bold border-t border-gray-200 dark:border-dark-800 pt-1 text-sm">
                    <span class="text-gray-900 dark:text-white">Total Order Cost:</span>
                    <span class="font-mono text-primary-600 dark:text-primary-400">Br {{ number_format($order->total_cost ?: ($order->calculateMaterialCost() + $order->additional_costs), 2) }}</span>
                </div>
            </div>
        </div>

    </div>

    <!-- Raw Material Requirements & Deduction Tracking -->
    <div class="card space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-gray-200 dark:border-dark-800 pb-3">
            <div>
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Raw Material Inventory Consumption & Deduction</h2>
                <p class="text-xs text-gray-500 dark:text-dark-400">
                    Live tracking of required, issued, consumed, returned, and wasted materials.
                </p>
            </div>
            <div>
                @if($order->is_materials_issued)
                    <span class="px-2.5 py-1 rounded bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60 text-xs font-bold flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-500 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Stock Deducted from Inventory
                    </span>
                @else
                    <span class="px-2.5 py-1 rounded bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60 text-xs font-semibold flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-amber-500 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Stock Deduction Pending Issuance
                    </span>
                @endif
            </div>
        </div>

        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Raw Material</th>
                        <th>Required Qty</th>
                        <th>Available Stock</th>
                        <th>Issued Qty</th>
                        <th>Actual Consumed</th>
                        <th>Returned Unused</th>
                        <th>Wasted</th>
                        <th>Est. Cost</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->materialIssues as $issue)
                        @php
                            $availInfo = $materialAvailability[$issue->id] ?? null;
                            $isSuff = $availInfo['is_sufficient'] ?? true;
                            $uCost = $issue->unit_cost ?: ($issue->product->purchase_cost ?? 0);
                        @endphp
                        <tr>
                            <td>
                                <div class="font-medium text-gray-900 dark:text-white">{{ $issue->product->name }}</div>
                                <div class="text-[11px] text-gray-500 dark:text-dark-400">SKU: {{ $issue->product->sku }}</div>
                            </td>
                            <td class="font-mono text-sm">
                                {{ $issue->quantity_planned }} {{ $issue->unit?->code ?? $issue->product->unit?->code ?? 'units' }}
                            </td>
                            <td>
                                @if(!$order->is_materials_issued)
                                    <div class="font-mono text-xs {{ $isSuff ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400 font-bold' }}">
                                        {{ $availInfo['available_location'] ?? 0 }} in location
                                    </div>
                                    <div class="text-[10px] text-gray-400 dark:text-dark-500">
                                        ({{ $availInfo['available_total'] ?? 0 }} total system stock)
                                    </div>
                                    @if(!$isSuff)
                                        <span class="text-[10px] text-rose-600 dark:text-rose-400 font-semibold block">⚠️ Insufficient Stock</span>
                                    @endif
                                @else
                                    <span class="text-xs text-gray-400 dark:text-dark-400">—</span>
                                @endif
                            </td>
                            <td class="font-mono text-sm {{ $issue->quantity_issued > 0 ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-dark-500' }}">
                                {{ $issue->quantity_issued }}
                            </td>
                            <td class="font-mono text-sm {{ $issue->quantity_consumed > 0 ? 'text-emerald-600 dark:text-emerald-400 font-semibold' : 'text-gray-400 dark:text-dark-500' }}">
                                {{ $issue->quantity_consumed }}
                            </td>
                            <td class="font-mono text-sm {{ $issue->quantity_returned > 0 ? 'text-blue-600 dark:text-blue-400' : 'text-gray-400 dark:text-dark-500' }}">
                                {{ $issue->quantity_returned }}
                            </td>
                            <td class="font-mono text-sm {{ $issue->quantity_wasted > 0 ? 'text-rose-600 dark:text-rose-400 font-bold' : 'text-gray-400 dark:text-dark-500' }}">
                                {{ $issue->quantity_wasted }}
                                @if($issue->wastage_reason)
                                    <span class="block text-[10px] text-gray-500 dark:text-dark-400 font-sans">({{ $issue->wastage_reason }})</span>
                                @endif
                            </td>
                            <td class="font-mono text-xs text-gray-600 dark:text-dark-300">
                                Br {{ number_format($issue->quantity_consumed * $uCost, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Stock Movement Audit Log for This Order -->
    <div class="card space-y-4">
        <div class="flex items-center justify-between border-b border-gray-200 dark:border-dark-800 pb-3">
            <div>
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Stock Movement Audit Trail</h2>
                <p class="text-xs text-gray-500 dark:text-dark-400">
                    Traceable ledger of raw material deductions, returns, and finished-good registrations for this order.
                </p>
            </div>
            <span class="text-xs text-gray-500 dark:text-dark-400 font-mono">{{ $order->stockMovements->count() }} movements recorded</span>
        </div>

        @if($order->stockMovements->isEmpty())
            <p class="text-xs text-gray-400 dark:text-dark-500 py-6 text-center">No inventory stock movements recorded yet for this order.</p>
        @else
            <div class="table-wrapper">
                <table class="table text-xs">
                    <thead>
                        <tr>
                            <th>Movement Type</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Storage Location</th>
                            <th>Unit Cost</th>
                            <th>Responsible User</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->stockMovements as $move)
                            <tr>
                                <td>
                                    @if($move->type === 'out')
                                        <span class="px-2 py-0.5 rounded bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 font-semibold border border-rose-200 dark:border-rose-800/40">
                                            Deduction (Out)
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 font-semibold border border-emerald-200 dark:border-emerald-800/40">
                                            Registration (In)
                                        </span>
                                    @endif
                                </td>
                                <td class="font-medium text-gray-900 dark:text-white">
                                    {{ $move->product->name }} (SKU: {{ $move->product->sku }})
                                </td>
                                <td class="font-mono font-bold {{ $move->type === 'out' ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                    {{ $move->type === 'out' ? '-' : '+' }}{{ $move->quantity }}
                                </td>
                                <td class="text-gray-600 dark:text-dark-300">
                                    {{ $move->storageLocation->name ?? 'Default' }}
                                </td>
                                <td class="font-mono text-gray-600 dark:text-dark-300">
                                    Br {{ number_format($move->unit_cost, 2) }}
                                </td>
                                <td class="text-gray-600 dark:text-dark-300">
                                    {{ $move->user->name ?? 'System' }}
                                </td>
                                <td class="text-gray-500 dark:text-dark-400">
                                    {{ $move->created_at->format('M d, Y H:i:s') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @if($order->production_notes)
        <div class="card space-y-2">
            <h3 class="text-xs font-bold text-gray-500 dark:text-dark-400 uppercase tracking-wider">Production Notes</h3>
            <p class="text-sm text-gray-700 dark:text-dark-200 whitespace-pre-line">{{ $order->production_notes }}</p>
        </div>
    @endif

</div>

<!-- Complete Production Modal -->
<div id="complete-modal" class="fixed inset-0 z-50 bg-black/60 dark:bg-black/80 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="card max-w-2xl w-full p-6 space-y-5 max-h-[90vh] overflow-y-auto border-gray-200 dark:border-primary-700/50 shadow-2xl">
        <div class="flex items-center justify-between border-b border-gray-200 dark:border-dark-800 pb-3">
            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Complete Production & Register Finished Product</h3>
                <p class="text-xs text-gray-500 dark:text-dark-400">Order #{{ $order->reference }} — Output: {{ $order->product->name }}</p>
            </div>
            <button type="button" onclick="document.getElementById('complete-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-700 dark:hover:text-white">
                ✕
            </button>
        </div>

        <form method="POST" action="{{ route('manufacturing-orders.complete', $order) }}" class="space-y-4">
            @csrf

            <div class="p-3 rounded-lg bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/40 text-xs text-emerald-800 dark:text-emerald-300">
                <strong>Important:</strong> Only verified accepted good quantities will be registered into saleable inventory. Rejected or damaged units will be recorded in production history.
            </div>

            <!-- Output Quantities -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-dark-300 mb-1">
                        Accepted Good Quantity <span class="text-emerald-600 dark:text-emerald-400">*</span>
                    </label>
                    <input type="number" step="any" min="0" name="good_quantity" required
                           value="{{ old('good_quantity', $order->planned_quantity) }}"
                           class="form-input text-sm font-mono">
                    <span class="text-[11px] text-gray-400 dark:text-dark-500 mt-0.5 block">Quantity to add to finished products inventory.</span>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-dark-300 mb-1">
                        Rejected / Damaged Quantity
                    </label>
                    <input type="number" step="any" min="0" name="rejected_quantity"
                           value="{{ old('rejected_quantity', 0) }}"
                           class="form-input text-sm font-mono">
                    <span class="text-[11px] text-gray-400 dark:text-dark-500 mt-0.5 block">Not added to saleable stock.</span>
                </div>
            </div>

            <!-- Additional Costs -->
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-dark-300 mb-1">
                    Additional Production Costs (Labor, Overhead)
                </label>
                <input type="number" step="any" min="0" name="additional_costs"
                       value="{{ old('additional_costs', $order->additional_costs) }}"
                       class="form-input text-sm font-mono">
            </div>

            <!-- Raw Material Consumption & Return adjustments -->
            @if($order->materialIssues->count() > 0)
                <div class="pt-3 border-t border-gray-200 dark:border-dark-800 space-y-3">
                    <h4 class="text-xs font-bold text-gray-700 dark:text-dark-300 uppercase tracking-wider">Raw Material Actuals & Returns</h4>
                    <p class="text-[11px] text-gray-500 dark:text-dark-400">If any issued materials were not used, specify returned quantity to restore them to inventory.</p>

                    <div class="space-y-3">
                        @foreach($order->materialIssues as $issue)
                            <div class="p-3 rounded-lg bg-gray-50 dark:bg-dark-900/60 border border-gray-200 dark:border-dark-800 space-y-2">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $issue->product->name }}</span>
                                    <span class="text-gray-500 dark:text-dark-400">Issued: <strong class="text-gray-900 dark:text-white">{{ $issue->quantity_issued }}</strong> {{ $issue->unit?->code ?? 'units' }}</span>
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    <div>
                                        <label class="text-[10px] text-gray-500 dark:text-dark-400 block mb-0.5">Consumed</label>
                                        <input type="number" step="any" min="0" max="{{ $issue->quantity_issued }}"
                                               name="materials[{{ $issue->id }}][quantity_consumed]"
                                               value="{{ $issue->quantity_issued }}"
                                               class="form-input text-xs font-mono py-1">
                                    </div>
                                    <div>
                                        <label class="text-[10px] text-gray-500 dark:text-dark-400 block mb-0.5">Returned Unused</label>
                                        <input type="number" step="any" min="0" max="{{ $issue->quantity_issued }}"
                                               name="materials[{{ $issue->id }}][quantity_returned]"
                                               value="0"
                                               class="form-input text-xs font-mono py-1">
                                    </div>
                                    <div>
                                        <label class="text-[10px] text-gray-500 dark:text-dark-400 block mb-0.5">Wasted</label>
                                        <input type="number" step="any" min="0"
                                               name="materials[{{ $issue->id }}][quantity_wasted]"
                                               value="0"
                                               class="form-input text-xs font-mono py-1">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-dark-300 mb-1">
                    Quality & Production Notes
                </label>
                <textarea name="production_notes" rows="2" placeholder="Quality verification notes, batch variance explanation..."
                          class="form-input text-sm"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-200 dark:border-dark-800">
                <button type="button" onclick="document.getElementById('complete-modal').classList.add('hidden')" class="btn-secondary text-xs px-3 py-2">
                    Cancel
                </button>
                <button type="submit" class="btn-primary text-xs px-5 py-2 shadow-sm">
                    Confirm Completion & Register Inventory
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Cancel Order Modal -->
<div id="cancel-modal" class="fixed inset-0 z-50 bg-black/60 dark:bg-black/80 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="card max-w-md w-full p-6 space-y-4 border-gray-200 dark:border-rose-800/50 shadow-2xl">
        <h3 class="text-base font-bold text-gray-900 dark:text-white">Cancel Manufacturing Order</h3>
        <p class="text-xs text-gray-500 dark:text-dark-400">
            Are you sure you want to cancel Order #{{ $order->reference }}?
            @if($order->is_materials_issued)
                <span class="text-rose-600 dark:text-rose-400 font-semibold block mt-1">
                    All previously issued raw materials will be automatically returned to inventory and logged in stock history.
                </span>
            @endif
        </p>

        <form method="POST" action="{{ route('manufacturing-orders.cancel', $order) }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-dark-300 mb-1">Cancellation Reason</label>
                <textarea name="cancel_reason" required rows="2" placeholder="Reason for cancellation..."
                          class="form-input text-xs"></textarea>
            </div>
            <div class="flex items-center justify-end gap-3">
                <button type="button" onclick="document.getElementById('cancel-modal').classList.add('hidden')" class="btn-secondary text-xs px-3 py-2">
                    Close
                </button>
                <button type="submit" class="btn-danger text-xs px-4 py-2">
                    Confirm Cancellation
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
