@extends('layouts.app')
@section('title', 'Manufacturing Staff Dashboard')

@section('content')
<div class="space-y-6">

    <!-- Header & Quick Actions -->
    <div class="page-header flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-bold text-white tracking-tight">Manufacturing Operations Dashboard</h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-900/50 text-primary-300 border border-primary-700/50">
                    {{ auth()->user()->roles->first()->name ?? 'Production Staff' }}
                </span>
            </div>
            <p class="text-dark-400 text-sm mt-1">Real-time production orders, raw material stock deductions, and finished product registration.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('manufacturing-orders.index') }}" class="btn-secondary text-sm">
                <svg class="w-4 h-4 mr-1.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
                All Orders
            </a>
            <a href="{{ route('manufacturing-orders.create') }}" class="btn-primary text-sm shadow-lg shadow-primary-600/20">
                <svg class="w-4 h-4 mr-1.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                New Production Order
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-950/60 border border-emerald-800/50 text-emerald-300 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-950/60 border border-rose-800/50 text-rose-300 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- KPI Metric Cards Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Active Orders -->
        <div class="stat-card">
            <div class="stat-icon bg-amber-900/40 border border-amber-700/30">
                <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <div>
                <p class="stat-value text-amber-300">{{ $inProgressOrders }}</p>
                <p class="stat-label">In Production</p>
                <span class="text-xs text-dark-400">{{ $pendingOrders }} pending/approved</span>
            </div>
        </div>

        <!-- Finished Output Produced -->
        <div class="stat-card">
            <div class="stat-icon bg-emerald-900/40 border border-emerald-700/30">
                <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="stat-value text-emerald-300">{{ number_format($totalFinishedProduced, 0) }}</p>
                <p class="stat-label">Accepted Finished Goods</p>
                <span class="text-xs text-dark-400">{{ $completedOrders }} completed batches</span>
            </div>
        </div>

        <!-- Low Stock Alert -->
        <div class="stat-card">
            <div class="stat-icon bg-rose-900/40 border border-rose-700/30">
                <svg class="w-6 h-6 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <p class="stat-value text-rose-400">{{ $lowStockMaterials->count() }}</p>
                <p class="stat-label">Low Stock Raw Materials</p>
                <span class="text-xs text-dark-400">of {{ $totalRawMaterialsCount }} raw materials</span>
            </div>
        </div>

        <!-- Efficiency -->
        <div class="stat-card">
            <div class="stat-icon bg-blue-900/40 border border-blue-700/30">
                <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <div>
                <p class="stat-value text-blue-300">{{ $avgEfficiency }}%</p>
                <p class="stat-label">Yield Efficiency</p>
                <span class="text-xs text-dark-400">{{ number_format($totalRejected, 0) }} rejected units</span>
            </div>
        </div>
    </div>

    <!-- Low Stock Materials Alert (if any) -->
    @if($lowStockMaterials->count() > 0)
    <div class="card border-rose-900/50 bg-rose-950/20">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <span class="p-1.5 rounded-lg bg-rose-900/50 text-rose-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </span>
                <h3 class="text-base font-semibold text-rose-300">Raw Material Low Stock Warnings</h3>
            </div>
            <span class="text-xs text-rose-400 font-medium">Insufficient for planned manufacturing batches</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($lowStockMaterials as $mat)
                <div class="p-3 rounded-lg bg-dark-900/80 border border-rose-900/30 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-white">{{ $mat->name }}</p>
                        <p class="text-xs text-dark-400">SKU: {{ $mat->sku }} • Min: {{ $mat->minimum_stock_level }} {{ $mat->unit?->code ?? 'units' }}</p>
                    </div>
                    <div class="text-right">
                        <span class="px-2 py-1 rounded bg-rose-900/60 text-rose-200 text-xs font-bold font-mono">
                            {{ $mat->quantity }} {{ $mat->unit?->code ?? 'units' }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Main Content: Active Orders & Raw Material Inventory -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        <!-- Active Manufacturing Orders (2 cols) -->
        <div class="card xl:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-semibold text-white">Active Production & Orders</h2>
                    <p class="text-xs text-dark-400">Orders requiring material issuance, production monitoring, or completion.</p>
                </div>
                <a href="{{ route('manufacturing-orders.index') }}" class="btn-secondary text-xs px-3 py-1.5">View All</a>
            </div>

            @if($activeOrders->isEmpty())
                <div class="p-8 text-center text-dark-400">
                    <svg class="w-12 h-12 mx-auto mb-3 text-dark-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="text-sm font-medium">No active production orders right now.</p>
                    <a href="{{ route('manufacturing-orders.create') }}" class="btn-primary text-xs mt-3 inline-block">Create New Order</a>
                </div>
            @else
                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Order Ref</th>
                                <th>Manufactured Product</th>
                                <th>Planned Qty</th>
                                <th>Status</th>
                                <th>Stock Status</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($activeOrders as $order)
                                <tr>
                                    <td>
                                        <a href="{{ route('manufacturing-orders.show', $order) }}" class="font-bold text-primary-400 hover:underline">
                                            {{ $order->reference }}
                                        </a>
                                        <div class="text-[11px] text-dark-400">
                                            {{ $order->planned_date?->format('M d, Y') ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="font-medium text-white">{{ $order->product->name }}</div>
                                        <div class="text-[11px] text-dark-400">{{ $order->storageLocation->name ?? 'Default Location' }}</div>
                                    </td>
                                    <td class="font-mono text-sm">
                                        {{ $order->planned_quantity }} {{ $order->unit?->code ?? $order->product->unit?->code ?? 'units' }}
                                    </td>
                                    <td>
                                        @php
                                            $st = strtolower($order->status);
                                            $badgeClass = match($st) {
                                                'draft'            => 'bg-dark-800 text-dark-300 border border-dark-700',
                                                'pending', 'pending_approval' => 'bg-amber-950/60 text-amber-300 border border-amber-800/60',
                                                'approved'         => 'bg-blue-950/60 text-blue-300 border border-blue-800/60',
                                                'materials_issued' => 'bg-indigo-950/60 text-indigo-300 border border-indigo-800/60',
                                                'in_progress'      => 'bg-purple-950/60 text-purple-300 border border-purple-800/60',
                                                'completed'        => 'bg-emerald-950/60 text-emerald-300 border border-emerald-800/60',
                                                default            => 'bg-dark-800 text-dark-300',
                                            };
                                        @endphp
                                        <span class="px-2 py-0.5 rounded text-xs font-medium {{ $badgeClass }}">
                                            {{ ucwords(str_replace('_', ' ', $order->status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($order->is_materials_issued)
                                            <span class="inline-flex items-center text-xs text-emerald-400">
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                Materials Issued
                                            </span>
                                        @else
                                            <span class="inline-flex items-center text-xs text-amber-400">
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                Awaiting Issuance
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('manufacturing-orders.show', $order) }}" class="btn-secondary text-xs px-2.5 py-1">
                                            Manage
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Raw Materials Available (1 col) -->
        <div class="card">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-semibold text-white">Raw Material Stock</h2>
                    <p class="text-xs text-dark-400">Current available inventory for production.</p>
                </div>
            </div>

            <div class="space-y-3">
                @forelse($rawMaterialsList as $mat)
                    <div class="p-3 rounded-lg bg-dark-900/60 border border-dark-800 flex items-center justify-between hover:border-dark-700 transition">
                        <div>
                            <p class="text-sm font-medium text-white">{{ $mat->name }}</p>
                            <p class="text-xs text-dark-400">{{ $mat->category?->name ?? 'Raw Material' }} • SKU: {{ $mat->sku }}</p>
                        </div>
                        <div class="text-right">
                            <span class="font-mono text-sm font-semibold {{ $mat->quantity <= ($mat->minimum_stock_level ?: 0) ? 'text-rose-400' : 'text-emerald-400' }}">
                                {{ number_format($mat->quantity, 2) }}
                            </span>
                            <span class="text-xs text-dark-400 block">{{ $mat->unit?->code ?? 'units' }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-dark-500 text-center py-4">No raw materials registered.</p>
                @endforelse
            </div>

            <div class="mt-4 pt-3 border-t border-dark-800">
                <a href="{{ route('products.index') }}?type=raw_material" class="text-xs text-primary-400 hover:underline flex items-center justify-center">
                    View Complete Inventory & Stock Receipts →
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Production History & Stock Movement Audit -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

        <!-- Recent Completed Orders -->
        <div class="card">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-semibold text-white">Recent Completed Production</h2>
                    <p class="text-xs text-dark-400">Finished goods registered and production history.</p>
                </div>
            </div>

            @if($recentCompleted->isEmpty())
                <p class="text-xs text-dark-500 py-6 text-center">No completed production batches recorded yet.</p>
            @else
                <div class="space-y-3">
                    @foreach($recentCompleted as $comp)
                        <div class="p-3.5 rounded-lg bg-dark-900/60 border border-dark-800 flex items-center justify-between">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-sm text-white">{{ $comp->product->name }}</span>
                                    <span class="text-xs text-primary-400 font-mono">#{{ $comp->reference }}</span>
                                </div>
                                <p class="text-xs text-dark-400 mt-0.5">
                                    Completed {{ $comp->actual_completion_date?->diffForHumans() ?? 'recently' }} •
                                    Efficiency: <span class="text-emerald-400 font-medium">{{ $comp->calculateEfficiency() }}%</span>
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="px-2 py-0.5 rounded bg-emerald-950 text-emerald-300 text-xs font-bold font-mono">
                                    +{{ $comp->good_quantity }} {{ $comp->unit?->code ?? $comp->product->unit?->code ?? 'units' }}
                                </span>
                                @if($comp->rejected_quantity > 0)
                                    <span class="block text-[11px] text-rose-400 mt-0.5">
                                        {{ $comp->rejected_quantity }} rejected
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Stock Movement History for Manufacturing -->
        <div class="card">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-semibold text-white">Manufacturing Stock Movements</h2>
                    <p class="text-xs text-dark-400">Traceable raw material deductions and finished product additions.</p>
                </div>
            </div>

            @if($recentStockMovements->isEmpty())
                <p class="text-xs text-dark-500 py-6 text-center">No manufacturing stock movements recorded yet.</p>
            @else
                <div class="table-wrapper">
                    <table class="table text-xs">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Type</th>
                                <th>Qty</th>
                                <th>Location</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentStockMovements as $move)
                                <tr>
                                    <td class="font-medium text-white">{{ $move->product->name ?? 'Product' }}</td>
                                    <td>
                                        @if($move->type === 'out')
                                            <span class="px-1.5 py-0.5 rounded bg-rose-950/60 text-rose-300 text-[10px] font-bold">Deduction (Out)</span>
                                        @else
                                            <span class="px-1.5 py-0.5 rounded bg-emerald-950/60 text-emerald-300 text-[10px] font-bold">Addition (In)</span>
                                        @endif
                                    </td>
                                    <td class="font-mono font-semibold {{ $move->type === 'out' ? 'text-rose-400' : 'text-emerald-400' }}">
                                        {{ $move->type === 'out' ? '-' : '+' }}{{ $move->quantity }}
                                    </td>
                                    <td class="text-dark-400">{{ $move->storageLocation->name ?? 'Warehouse' }}</td>
                                    <td class="text-dark-400">{{ $move->created_at->format('M d, H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>

</div>
@endsection
