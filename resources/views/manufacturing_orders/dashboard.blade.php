@extends('layouts.app')
@section('title', 'Manufacturing Staff Dashboard')

@section('content')
<div class="space-y-6">

    <!-- Top Greeting & Quick Launch Bar -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                    Manufacturing Operations Dashboard
                </h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-500/15 text-purple-300 border border-purple-500/30">
                    {{ auth()->user()->roles->first()->name ?? 'Production Staff' }}
                </span>
            </div>
            <p class="text-dark-400 text-sm mt-1">
                Real-time production orders, raw material stock deductions, yield efficiency, and finished goods registration.
            </p>
        </div>

        <!-- Quick Primary Actions -->
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('products.create', ['type' => 'raw_material']) }}" class="btn-secondary flex items-center gap-2 text-sm !bg-dark-900 border-dark-700 hover:border-amber-500/40">
                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Add Raw Material</span>
            </a>
            <a href="{{ route('stock-receipts.create') }}" class="btn-secondary flex items-center gap-2 text-sm !bg-dark-900 border-dark-700 hover:border-emerald-500/40">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span>Receive Stock</span>
            </a>
            <a href="{{ route('manufacturing-orders.create') }}" class="btn-primary flex items-center gap-2 text-sm shadow-lg shadow-purple-600/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>New Production Order</span>
            </a>
        </div>
    </div>

    <!-- Production Reports & Export Toolbar -->
    <div class="card bg-gradient-to-r from-dark-900 via-dark-900/95 to-dark-900 border border-dark-800 rounded-2xl p-4 sm:p-5">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-3 border-b border-dark-800/80">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-white tracking-wide uppercase">Production Intelligence & Export Hub</h3>
                    <p class="text-xs text-dark-400">Download production batches, raw material logs, and batch yield reports</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('manufacturing-orders.index') }}" class="btn-secondary text-xs flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                    <span>All Orders ({{ $totalOrders }})</span>
                </a>
            </div>
        </div>

        <!-- Export Shortcuts Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 pt-3">
            <a href="{{ route('reports.production.export') }}" class="p-2.5 rounded-xl bg-dark-950/70 border border-dark-800 hover:border-purple-500/40 hover:bg-dark-800/50 transition flex items-center justify-between group">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-2 h-2 rounded-full bg-purple-400 flex-shrink-0"></span>
                    <span class="text-xs font-medium text-dark-300 group-hover:text-white truncate">Production Output CSV</span>
                </div>
                <svg class="w-3.5 h-3.5 text-dark-500 group-hover:text-purple-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </a>

            <a href="{{ route('reports.inventory.export') }}" class="p-2.5 rounded-xl bg-dark-950/70 border border-dark-800 hover:border-emerald-500/40 hover:bg-dark-800/50 transition flex items-center justify-between group">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 flex-shrink-0"></span>
                    <span class="text-xs font-medium text-dark-300 group-hover:text-white truncate">Materials Stock CSV</span>
                </div>
                <svg class="w-3.5 h-3.5 text-dark-500 group-hover:text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </a>

            <a href="{{ route('stock-receipts.index') }}" class="p-2.5 rounded-xl bg-dark-950/70 border border-dark-800 hover:border-blue-500/40 hover:bg-dark-800/50 transition flex items-center justify-between group">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-2 h-2 rounded-full bg-blue-400 flex-shrink-0"></span>
                    <span class="text-xs font-medium text-dark-300 group-hover:text-white truncate">Stock Receipts</span>
                </div>
                <svg class="w-3.5 h-3.5 text-dark-500 group-hover:text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </a>

            <a href="{{ route('products.index', ['type' => 'raw_material']) }}" class="p-2.5 rounded-xl bg-dark-950/70 border border-dark-800 hover:border-amber-500/40 hover:bg-dark-800/50 transition flex items-center justify-between group">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-2 h-2 rounded-full bg-amber-400 flex-shrink-0"></span>
                    <span class="text-xs font-medium text-dark-300 group-hover:text-white truncate">Raw Materials Catalog</span>
                </div>
                <svg class="w-3.5 h-3.5 text-dark-500 group-hover:text-amber-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </a>
        </div>
    </div>

    <!-- Core KPI Stat Cards Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <!-- Active Orders -->
        <div class="stat-card">
            <div class="stat-icon bg-purple-900/40">
                <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <div>
                <p class="stat-value text-purple-400">{{ $inProgressOrders }} Active</p>
                <p class="stat-label">In Production</p>
                <p class="text-[11px] text-dark-400 mt-1">
                    {{ $pendingOrders }} pending/approved • {{ $totalOrders }} total batches
                </p>
            </div>
        </div>

        <!-- Finished Output Produced -->
        <div class="stat-card">
            <div class="stat-icon bg-emerald-900/40">
                <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="stat-value text-emerald-400">{{ number_format($totalFinishedProduced, 0) }}</p>
                <p class="stat-label">Accepted Finished Units</p>
                <p class="text-[11px] text-dark-400 mt-1">
                    {{ $completedOrders }} completed batches registered to stock
                </p>
            </div>
        </div>

        <!-- Low Stock Alert -->
        <div class="stat-card">
            <div class="stat-icon bg-amber-900/40">
                <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <p class="stat-value {{ $lowStockMaterials->count() > 0 ? 'text-rose-400' : 'text-amber-400' }}">
                        {{ $lowStockMaterials->count() }}
                    </p>
                    @if($lowStockMaterials->count() > 0)
                        <span class="badge-red text-[10px] px-1.5 py-0.5">Low Stock</span>
                    @else
                        <span class="badge-green text-[10px] px-1.5 py-0.5">Optimal</span>
                    @endif
                </div>
                <p class="stat-label">Low Stock Raw Materials</p>
                <p class="text-[11px] text-dark-400 mt-1">
                    of {{ $totalRawMaterialsCount }} raw materials in inventory
                </p>
            </div>
        </div>

        <!-- Efficiency -->
        <div class="stat-card">
            <div class="stat-icon bg-cyan-900/40">
                <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <div>
                <p class="stat-value text-cyan-400">{{ $avgEfficiency }}%</p>
                <p class="stat-label">Production Yield Efficiency</p>
                <p class="text-[11px] text-dark-400 mt-1">
                    {{ number_format($totalRejected, 0) }} total scrap / rejected units
                </p>
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
