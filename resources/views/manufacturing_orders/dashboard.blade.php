@extends('layouts.app')
@section('title', 'Manufacturing Operations Dashboard')

@section('content')
<div class="space-y-6">

    <!-- Top Greeting & Quick Launch Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white border border-slate-200/80 rounded-2xl p-5 sm:p-6 shadow-xs">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                    Manufacturing Operations Floor
                </h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                    {{ auth()->user()->roles->first()?->name ?? 'Production Officer' }}
                </span>
            </div>
            <p class="text-slate-500 text-sm mt-1">
                Real-time production orders, raw material deductions, yield efficiency, and finished goods inventory.
            </p>
        </div>

        <!-- Quick Primary Actions -->
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('manufacturing-orders.create') }}" class="btn-primary text-sm shadow-xs flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>New Production Order</span>
            </a>
            <a href="{{ route('stock-receipts.create') }}" class="btn-secondary text-sm flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span>Receive Stock</span>
            </a>
            <a href="{{ route('products.create', ['type' => 'raw_material']) }}" class="btn-secondary text-sm flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Add Raw Material</span>
            </a>
        </div>
    </div>

    <!-- Production Reports & Export Toolbar -->
    <div class="card p-4 sm:p-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wide">Production Intelligence & Export Center</h3>
                    <p class="text-xs text-slate-500">Download production batch records, raw material logs, and batch yield metrics</p>
                </div>
            </div>
            <a href="{{ route('manufacturing-orders.index') }}" class="btn-secondary text-xs py-1.5 px-3 flex items-center gap-1.5 w-fit">
                <span>All Orders ({{ $totalOrders }})</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        <!-- Export Shortcuts Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 pt-3">
            <a href="{{ route('reports.production.export') }}" class="p-2.5 rounded-xl border border-slate-200/80 hover:border-purple-300 hover:bg-purple-50/50 transition flex items-center justify-between group">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-2 h-2 rounded-full bg-purple-500 flex-shrink-0"></span>
                    <span class="text-xs font-medium text-slate-700 group-hover:text-purple-700 truncate">Production Output CSV</span>
                </div>
                <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-purple-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </a>

            <a href="{{ route('reports.inventory.export') }}" class="p-2.5 rounded-xl border border-slate-200/80 hover:border-emerald-300 hover:bg-emerald-50/50 transition flex items-center justify-between group">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 flex-shrink-0"></span>
                    <span class="text-xs font-medium text-slate-700 group-hover:text-emerald-700 truncate">Materials Stock CSV</span>
                </div>
                <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </a>

            <a href="{{ route('stock-receipts.index') }}" class="p-2.5 rounded-xl border border-slate-200/80 hover:border-blue-300 hover:bg-blue-50/50 transition flex items-center justify-between group">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-2 h-2 rounded-full bg-blue-500 flex-shrink-0"></span>
                    <span class="text-xs font-medium text-slate-700 group-hover:text-blue-700 truncate">Stock Receipts</span>
                </div>
                <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </a>

            <a href="{{ route('products.index', ['type' => 'raw_material']) }}" class="p-2.5 rounded-xl border border-slate-200/80 hover:border-amber-300 hover:bg-amber-50/50 transition flex items-center justify-between group">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-2 h-2 rounded-full bg-amber-500 flex-shrink-0"></span>
                    <span class="text-xs font-medium text-slate-700 group-hover:text-amber-700 truncate">Raw Materials List</span>
                </div>
                <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </a>
        </div>
    </div>

    <!-- Core KPI Stat Cards Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Active Orders -->
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <span class="stat-label">In Production</span>
                <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            </div>
            <div class="stat-value text-slate-900">{{ $inProgressOrders }} Active</div>
            <div class="flex items-center gap-1.5 mt-2 text-xs">
                <span class="font-medium text-purple-700 bg-purple-50 px-1.5 py-0.5 rounded">{{ $pendingOrders }} pending</span>
                <span class="text-slate-500">{{ $totalOrders }} total batches</span>
            </div>
        </div>

        <!-- Finished Output Produced -->
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <span class="stat-label">Accepted Finished Units</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="stat-value text-slate-900">{{ number_format($totalFinishedProduced, 0) }}</div>
            <div class="flex items-center gap-1.5 mt-2 text-xs">
                <span class="font-medium text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded">{{ $completedOrders }} completed</span>
                <span class="text-slate-500">batches to stock</span>
            </div>
        </div>

        <!-- Low Stock Alert -->
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <span class="stat-label">Low Stock Raw Materials</span>
                <div class="w-9 h-9 rounded-xl {{ $lowStockMaterials->count() > 0 ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600' }} flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
            </div>
            <div class="stat-value {{ $lowStockMaterials->count() > 0 ? 'text-amber-600' : 'text-slate-900' }}">{{ $lowStockMaterials->count() }}</div>
            <div class="flex items-center gap-1.5 mt-2 text-xs">
                @if($lowStockMaterials->count() > 0)
                    <span class="font-medium text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded">Action required</span>
                @else
                    <span class="font-medium text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded">Optimal levels</span>
                @endif
                <span class="text-slate-500">of {{ $totalRawMaterialsCount }} items</span>
            </div>
        </div>

        <!-- Efficiency -->
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <span class="stat-label">Yield Efficiency</span>
                <div class="w-9 h-9 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
            </div>
            <div class="stat-value text-slate-900">{{ $avgEfficiency }}%</div>
            <div class="flex items-center gap-1.5 mt-2 text-xs">
                <span class="font-medium text-slate-700 bg-slate-100 px-1.5 py-0.5 rounded">{{ number_format($totalRejected, 0) }} scrap</span>
                <span class="text-slate-500">rejected units</span>
            </div>
        </div>
    </div>

    <!-- Low Stock Materials Alert (if any) -->
    @if($lowStockMaterials->count() > 0)
    <div class="card border-amber-200 bg-amber-50/50 p-4">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <span class="p-1 rounded-lg bg-amber-100 text-amber-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </span>
                <h3 class="text-sm font-bold text-amber-900">Raw Material Low Stock Warnings</h3>
            </div>
            <span class="text-xs text-amber-800 font-medium">May affect planned manufacturing batches</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($lowStockMaterials as $mat)
                <div class="p-3 rounded-lg bg-white border border-amber-200/80 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-900">{{ $mat->name }}</p>
                        <p class="text-[11px] text-slate-500">SKU: {{ $mat->sku }} • Min: {{ $mat->minimum_stock_level }} {{ $mat->unit?->code ?? 'units' }}</p>
                    </div>
                    <div class="text-right">
                        <span class="badge badge-red font-mono text-xs">
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
        <div class="card xl:col-span-2 p-0 overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Active Production Batches</h2>
                    <p class="text-xs text-slate-500">Orders requiring material issuance, production monitoring, or completion.</p>
                </div>
                <a href="{{ route('manufacturing-orders.index') }}" class="btn-secondary text-xs py-1.5 px-3">View All</a>
            </div>

            @if($activeOrders->isEmpty())
                <div class="p-8 text-center text-slate-500">
                    <p class="text-sm font-medium">No active production orders right now.</p>
                    <a href="{{ route('manufacturing-orders.create') }}" class="btn-primary text-xs mt-3 inline-block">Create New Order</a>
                </div>
            @else
                <div class="table-wrapper border-0 rounded-none shadow-none">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Order Ref</th>
                                <th>Product</th>
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
                                        <a href="{{ route('manufacturing-orders.show', $order) }}" class="font-bold text-sky-700 text-xs hover:underline">
                                            {{ $order->reference }}
                                        </a>
                                        <div class="text-[11px] text-slate-500">
                                            {{ $order->planned_date?->format('M d, Y') ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="font-medium text-slate-900 text-xs">{{ $order->product->name }}</div>
                                        <div class="text-[11px] text-slate-500">{{ $order->storageLocation->name ?? 'Floor' }}</div>
                                    </td>
                                    <td class="font-mono text-xs font-semibold text-slate-800">
                                        {{ $order->planned_quantity }} {{ $order->unit?->code ?? $order->product->unit?->code ?? 'units' }}
                                    </td>
                                    <td>
                                        <span class="badge {{ $order->status === 'in_progress' ? 'badge-blue' : ($order->status === 'approved' ? 'badge-green' : 'badge-yellow') }}">
                                            {{ ucwords(str_replace('_', ' ', $order->status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($order->is_materials_issued)
                                            <span class="inline-flex items-center text-xs font-medium text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded">
                                                Materials Issued
                                            </span>
                                        @else
                                            <span class="inline-flex items-center text-xs font-medium text-amber-700 bg-amber-50 px-2 py-0.5 rounded">
                                                Awaiting Issuance
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('manufacturing-orders.show', $order) }}" class="text-xs font-semibold text-sky-600 hover:text-sky-700 hover:underline">
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
        <div class="card p-5">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Raw Material Stock</h2>
                    <p class="text-xs text-slate-500">Available component inventory for production</p>
                </div>
            </div>

            <div class="space-y-2.5">
                @forelse($rawMaterialsList as $mat)
                    <div class="p-2.5 rounded-xl border border-slate-100 bg-slate-50/60 flex items-center justify-between hover:border-slate-200 transition">
                        <div>
                            <p class="text-xs font-semibold text-slate-900">{{ $mat->name }}</p>
                            <p class="text-[11px] text-slate-500">{{ $mat->category?->name ?? 'Raw Material' }} • SKU: {{ $mat->sku }}</p>
                        </div>
                        <div class="text-right">
                            <span class="font-mono text-xs font-bold {{ $mat->quantity <= ($mat->minimum_stock_level ?: 0) ? 'text-rose-600' : 'text-emerald-600' }}">
                                {{ number_format($mat->quantity, 2) }}
                            </span>
                            <span class="text-[10px] text-slate-500 block">{{ $mat->unit?->code ?? 'units' }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-500 text-center py-4">No raw materials registered.</p>
                @endforelse
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 text-center">
                <a href="{{ route('products.index') }}?type=raw_material" class="text-xs text-sky-600 font-semibold hover:underline">
                    View Complete Inventory & Stock Receipts →
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Production History & Stock Movement Audit -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

        <!-- Recent Completed Orders -->
        <div class="card p-0 overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Recent Completed Production</h2>
                    <p class="text-xs text-slate-500">Finished goods registered to inventory</p>
                </div>
            </div>

            @if($recentCompleted->isEmpty())
                <p class="text-xs text-slate-500 py-6 text-center">No completed production batches recorded yet.</p>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach($recentCompleted as $comp)
                        <div class="p-3.5 sm:px-5 flex items-center justify-between hover:bg-slate-50 transition">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-xs text-slate-900">{{ $comp->product->name }}</span>
                                    <span class="text-xs text-sky-700 font-mono">#{{ $comp->reference }}</span>
                                </div>
                                <p class="text-[11px] text-slate-500 mt-0.5">
                                    Completed {{ $comp->actual_completion_date?->diffForHumans() ?? 'recently' }} •
                                    Yield: <span class="text-emerald-600 font-semibold">{{ $comp->calculateEfficiency() }}%</span>
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="badge badge-green text-xs font-mono font-bold">
                                    +{{ $comp->good_quantity }} {{ $comp->unit?->code ?? $comp->product->unit?->code ?? 'units' }}
                                </span>
                                @if($comp->rejected_quantity > 0)
                                    <span class="block text-[11px] text-rose-600 mt-0.5">
                                        {{ $comp->rejected_quantity }} scrap
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Stock Movement History for Manufacturing -->
        <div class="card p-0 overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Manufacturing Stock Movements</h2>
                    <p class="text-xs text-slate-500">Traceable raw material deductions and finished product additions</p>
                </div>
            </div>

            @if($recentStockMovements->isEmpty())
                <p class="text-xs text-slate-500 py-6 text-center">No manufacturing stock movements recorded yet.</p>
            @else
                <div class="table-wrapper border-0 rounded-none shadow-none">
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
                                    <td class="font-medium text-slate-900">{{ $move->product->name ?? 'Product' }}</td>
                                    <td>
                                        @if($move->type === 'out')
                                            <span class="badge badge-red text-[10px]">Deduction (Out)</span>
                                        @else
                                            <span class="badge badge-green text-[10px]">Addition (In)</span>
                                        @endif
                                    </td>
                                    <td class="font-mono font-bold {{ $move->type === 'out' ? 'text-rose-600' : 'text-emerald-600' }}">
                                        {{ $move->type === 'out' ? '-' : '+' }}{{ $move->quantity }}
                                    </td>
                                    <td class="text-slate-500">{{ $move->storageLocation->name ?? 'Warehouse' }}</td>
                                    <td class="text-slate-500">{{ $move->created_at->format('M d, H:i') }}</td>
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
