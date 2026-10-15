@extends('layouts.app')
@section('title', 'Admin Dashboard & Reports')

@section('content')
<div class="space-y-6">

    <!-- Top Greeting & Quick Launch Bar -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                    Executive Dashboard
                </h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary-500/15 text-primary-300 border border-primary-500/30">
                    {{ auth()->user()->roles->first()->name ?? 'Administrator' }}
                </span>
            </div>
            <p class="text-dark-400 text-sm mt-1">
                Real-time operational overview, raw material inventory, production metrics, and team performance.
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
            <a href="{{ route('manufacturing-orders.create') }}" class="btn-secondary flex items-center gap-2 text-sm !bg-dark-900 border-dark-700 hover:border-purple-500/40">
                <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                </svg>
                <span>New Mfg Order</span>
            </a>
            <a href="{{ route('sales.create') }}" class="btn-primary flex items-center gap-2 text-sm shadow-lg shadow-primary-600/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Quick Sale (POS)</span>
            </a>
        </div>
    </div>

    <!-- Executive Reports & Export Toolbar -->
    <div class="card bg-gradient-to-r from-dark-900 via-dark-900/95 to-dark-900 border border-dark-800 rounded-2xl p-4 sm:p-5">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-3 border-b border-dark-800/80">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-primary-500/10 text-primary-400 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-white tracking-wide uppercase">Reports & Analytics Hub</h3>
                    <p class="text-xs text-dark-400">Generate executive insights and export CSV datasets for operational records</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('reports.index') }}" class="btn-secondary text-xs flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    Full Reports Suite
                </a>
            </div>
        </div>

        <!-- Export Shortcuts Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5 pt-3">
            <a href="{{ route('reports.sales.export') }}" class="p-2.5 rounded-xl bg-dark-950/70 border border-dark-800 hover:border-cyan-500/40 hover:bg-dark-800/50 transition flex items-center justify-between group">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-2 h-2 rounded-full bg-cyan-400 flex-shrink-0"></span>
                    <span class="text-xs font-medium text-dark-300 group-hover:text-white truncate">Sales CSV</span>
                </div>
                <svg class="w-3.5 h-3.5 text-dark-500 group-hover:text-cyan-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </a>

            <a href="{{ route('reports.inventory.export') }}" class="p-2.5 rounded-xl bg-dark-950/70 border border-dark-800 hover:border-emerald-500/40 hover:bg-dark-800/50 transition flex items-center justify-between group">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 flex-shrink-0"></span>
                    <span class="text-xs font-medium text-dark-300 group-hover:text-white truncate">Stock CSV</span>
                </div>
                <svg class="w-3.5 h-3.5 text-dark-500 group-hover:text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </a>

            <a href="{{ route('reports.production.export') }}" class="p-2.5 rounded-xl bg-dark-950/70 border border-dark-800 hover:border-purple-500/40 hover:bg-dark-800/50 transition flex items-center justify-between group">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-2 h-2 rounded-full bg-purple-400 flex-shrink-0"></span>
                    <span class="text-xs font-medium text-dark-300 group-hover:text-white truncate">Mfg Output CSV</span>
                </div>
                <svg class="w-3.5 h-3.5 text-dark-500 group-hover:text-purple-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </a>

            <a href="{{ route('reports.branches.export') }}" class="p-2.5 rounded-xl bg-dark-950/70 border border-dark-800 hover:border-blue-500/40 hover:bg-dark-800/50 transition flex items-center justify-between group">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-2 h-2 rounded-full bg-blue-400 flex-shrink-0"></span>
                    <span class="text-xs font-medium text-dark-300 group-hover:text-white truncate">Branches CSV</span>
                </div>
                <svg class="w-3.5 h-3.5 text-dark-500 group-hover:text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </a>

            <a href="{{ route('reports.users.export') }}" class="p-2.5 rounded-xl bg-dark-950/70 border border-dark-800 hover:border-amber-500/40 hover:bg-dark-800/50 transition flex items-center justify-between group">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-2 h-2 rounded-full bg-amber-400 flex-shrink-0"></span>
                    <span class="text-xs font-medium text-dark-300 group-hover:text-white truncate">Staff Perf CSV</span>
                </div>
                <svg class="w-3.5 h-3.5 text-dark-500 group-hover:text-amber-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </a>

            <a href="{{ route('reports.activity.export') }}" class="p-2.5 rounded-xl bg-dark-950/70 border border-dark-800 hover:border-rose-500/40 hover:bg-dark-800/50 transition flex items-center justify-between group">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-2 h-2 rounded-full bg-rose-400 flex-shrink-0"></span>
                    <span class="text-xs font-medium text-dark-300 group-hover:text-white truncate">Audit Trail CSV</span>
                </div>
                <svg class="w-3.5 h-3.5 text-dark-500 group-hover:text-rose-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </a>
        </div>
    </div>

    <!-- Core KPI Stat Cards Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

        <!-- Revenue & Sales Card -->
        <div class="stat-card">
            <div class="stat-icon bg-emerald-900/40">
                <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="stat-value text-emerald-400">Br{{ number_format($stats['today_sales'], 2) }}</p>
                <p class="stat-label">Today's Revenue</p>
                <p class="text-[11px] text-dark-400 mt-1">
                    Month: Br{{ number_format($stats['month_sales'], 2) }} • Total: Br{{ number_format($stats['total_revenue'], 2) }}
                </p>
            </div>
        </div>

        <!-- Inventory Valuation Card -->
        <div class="stat-card">
            <div class="stat-icon bg-primary-900/40">
                <svg class="w-6 h-6 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <div>
                <p class="stat-value text-primary-400">Br{{ number_format($stats['total_inventory_valuation'], 2) }}</p>
                <p class="stat-label">Total Stock Valuation</p>
                <p class="text-[11px] text-dark-400 mt-1">
                    Raw: Br{{ number_format($stats['raw_materials_valuation'], 2) }} • FG: Br{{ number_format($stats['finished_goods_valuation'], 2) }}
                </p>
            </div>
        </div>

        <!-- Manufacturing Production Card -->
        <div class="stat-card">
            <div class="stat-icon bg-purple-900/40">
                <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h16a2 2 0 012 2v10a2 2 0 01-2 2h-2"/>
                </svg>
            </div>
            <div>
                <p class="stat-value text-purple-400">{{ $stats['active_orders'] }} Active</p>
                <p class="stat-label">Production Orders</p>
                <p class="text-[11px] text-dark-400 mt-1">
                    Output: {{ number_format($stats['completed_production'], 0) }} units • Scrap: {{ $stats['scrap_rate'] }}%
                </p>
            </div>
        </div>

        <!-- Raw Materials Health Card -->
        <div class="stat-card">
            <div class="stat-icon bg-amber-900/40">
                <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <p class="stat-value text-amber-400">{{ $stats['raw_materials_count'] }}</p>
                    @if($stats['low_stock_raw_materials'] > 0)
                        <span class="badge-red text-[10px] px-1.5 py-0.5">{{ $stats['low_stock_raw_materials'] }} Low</span>
                    @endif
                </div>
                <p class="stat-label">Raw Material Types</p>
                <p class="text-[11px] text-dark-400 mt-1">
                    In Stock: {{ number_format($stats['raw_materials_stock'], 1) }} total units
                </p>
            </div>
        </div>

    </div>

    <!-- ── RAW MATERIAL & PRODUCTION WORKFLOW FEED ── -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

        <!-- Raw Materials Inventory Feed -->
        <div class="card bg-dark-900/90 border border-dark-800 rounded-2xl p-5">
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-dark-800">
                <div>
                    <h2 class="text-base font-bold text-white flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                        Raw Materials for Production
                    </h2>
                    <p class="text-xs text-dark-400 mt-0.5">Current stock availability & procurement levels for manufacturing</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('products.create', ['type' => 'raw_material']) }}" class="btn-primary !px-2.5 !py-1 text-xs">
                        + Register
                    </a>
                    <a href="{{ route('products.index', ['type' => 'raw_material']) }}" class="btn-secondary !px-2.5 !py-1 text-xs">
                        View All
                    </a>
                </div>
            </div>

            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Material Name</th>
                            <th>SKU</th>
                            <th class="text-right">In Stock</th>
                            <th class="text-right">Unit Cost</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rawMaterialsList as $rm)
                        <tr>
                            <td>
                                <div class="font-medium text-white text-sm">{{ $rm->name }}</div>
                                <div class="text-[11px] text-dark-400">{{ $rm->category->name ?? 'Raw Material' }}</div>
                            </td>
                            <td class="font-mono text-xs text-dark-300">{{ $rm->sku }}</td>
                            <td class="text-right">
                                <span class="font-bold text-white text-sm">{{ number_format($rm->quantity, 2) }}</span>
                                <span class="text-[11px] text-dark-400">{{ $rm->unit->code ?? 'unit' }}</span>
                            </td>
                            <td class="text-right text-xs text-dark-300">
                                Br{{ number_format($rm->purchase_cost ?? 0, 2) }}
                            </td>
                            <td class="text-center">
                                @if($rm->quantity <= 0)
                                    <span class="badge-red">Out of Stock</span>
                                @elseif($rm->minimum_stock_level > 0 && $rm->quantity <= $rm->minimum_stock_level)
                                    <span class="badge-yellow">Low Stock</span>
                                @else
                                    <span class="badge-green">Available</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-6 text-dark-500">
                                No raw materials registered yet.
                                <a href="{{ route('products.create', ['type' => 'raw_material']) }}" class="text-primary-400 hover:underline block mt-1">Register first raw material</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Active Manufacturing Orders Feed -->
        <div class="card bg-dark-900/90 border border-dark-800 rounded-2xl p-5">
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-dark-800">
                <div>
                    <h2 class="text-base font-bold text-white flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-purple-400"></span>
                        Active Production Orders
                    </h2>
                    <p class="text-xs text-dark-400 mt-0.5">Manufacturing floor progress and finished product output</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('manufacturing-orders.create') }}" class="btn-primary !px-2.5 !py-1 text-xs">
                        + New Order
                    </a>
                    <a href="{{ route('manufacturing-orders.index') }}" class="btn-secondary !px-2.5 !py-1 text-xs">
                        View All
                    </a>
                </div>
            </div>

            <div class="space-y-3">
                @forelse($openOrders as $order)
                <div class="p-3.5 rounded-xl bg-dark-950/70 border border-dark-800 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <a href="{{ route('manufacturing-orders.show', $order) }}" class="text-sm font-semibold text-white hover:text-primary-400 transition truncate block">
                                {{ $order->product->name ?? 'Production Item' }}
                            </a>
                            <p class="text-xs text-dark-400 mt-0.5">
                                Ref: <span class="font-mono text-dark-300">{{ $order->reference }}</span> • Target: <span class="font-semibold text-white">{{ number_format($order->planned_quantity, 0) }}</span> {{ $order->product->unit->code ?? 'units' }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        @if($order->status === 'in_progress')
                            <span class="badge-blue">In Progress</span>
                        @elseif($order->status === 'materials_issued')
                            <span class="badge-purple">Materials Issued</span>
                        @elseif($order->status === 'approved')
                            <span class="badge-green">Approved</span>
                        @else
                            <span class="badge-yellow">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
                        @endif
                        <a href="{{ route('manufacturing-orders.show', $order) }}" class="btn-icon !p-1.5" title="Open Order Details">
                            <svg class="w-4 h-4 text-dark-400 hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>
                @empty
                <div class="text-center py-8">
                    <p class="text-sm text-dark-500">No active manufacturing orders.</p>
                    <a href="{{ route('manufacturing-orders.create') }}" class="text-primary-400 text-xs hover:underline mt-1 inline-block">Create new production batch</a>
                </div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- ── STAFF & USER PERFORMANCE REPORT SECTION ── -->
    <div class="card bg-dark-900/90 border border-dark-800 rounded-2xl p-5 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 mb-4 border-b border-dark-800">
            <div>
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full bg-cyan-400"></div>
                    <h2 class="text-base sm:text-lg font-bold text-white">
                        User & Staff Performance Report
                    </h2>
                </div>
                <p class="text-xs text-dark-400 mt-1">
                    Multi-factor evaluation of staff sales volume, manufacturing contributions, and operational audit trails
                </p>
            </div>
            <div class="flex items-center gap-2.5">
                <a href="{{ route('reports.users.export') }}" class="btn-primary flex items-center gap-2 text-xs !bg-cyan-600 hover:!bg-cyan-500">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    <span>Export User Report (CSV)</span>
                </a>
                <a href="{{ route('reports.activity') }}" class="btn-secondary text-xs flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    <span>Audit Log</span>
                </a>
            </div>
        </div>

        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Team Member</th>
                        <th>Role</th>
                        <th>Assigned Scope</th>
                        <th class="text-right">Sales Volume</th>
                        <th class="text-right">Mfg Orders</th>
                        <th class="text-right">Completed Units</th>
                        <th class="text-center">Audit Actions</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-800/80">
                    @forelse($userPerformances as $perf)
                    <tr>
                        <td>
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-primary-700/60 text-white font-bold text-xs flex items-center justify-center flex-shrink-0">
                                    {{ strtoupper(substr($perf['user']->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="font-semibold text-white text-sm truncate">{{ $perf['user']->name }}</p>
                                    <p class="text-xs text-dark-400 truncate">{{ $perf['user']->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($perf['role'] === 'Super Admin')
                                <span class="badge-red">{{ $perf['role'] }}</span>
                            @elseif($perf['role'] === 'Manager')
                                <span class="badge-yellow">{{ $perf['role'] }}</span>
                            @elseif($perf['role'] === 'Seller')
                                <span class="badge-green">{{ $perf['role'] }}</span>
                            @elseif($perf['role'] === 'Production Officer')
                                <span class="badge-purple">{{ $perf['role'] }}</span>
                            @else
                                <span class="badge-blue">{{ $perf['role'] }}</span>
                            @endif
                        </td>
                        <td class="text-xs text-dark-300">
                            {{ $perf['branches'] }}
                        </td>
                        <td class="text-right">
                            <span class="font-bold text-white text-sm block">Br{{ number_format($perf['sales_volume'], 2) }}</span>
                            <span class="text-[11px] text-dark-400">{{ $perf['sales_count'] }} orders</span>
                        </td>
                        <td class="text-right font-medium text-dark-200 text-sm">
                            {{ $perf['mfg_count'] }}
                        </td>
                        <td class="text-right">
                            <span class="font-semibold text-purple-300 text-sm">{{ number_format($perf['mfg_output'], 0) }}</span>
                        </td>
                        <td class="text-center">
                            <span class="px-2 py-0.5 rounded-lg bg-dark-800 text-dark-300 text-xs font-mono font-medium">
                                {{ $perf['activities'] }}
                            </span>
                            @if($perf['last_action'])
                                <span class="text-[10px] text-dark-500 block mt-0.5">
                                    {{ \Carbon\Carbon::parse($perf['last_action'])->diffForHumans() }}
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($perf['status'] === 'Top Performer')
                                <span class="badge-green">Top Performer</span>
                            @elseif($perf['status'] === 'Active')
                                <span class="badge-blue">Active</span>
                            @else
                                <span class="badge-gray">Idle / New</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-8 text-dark-500">
                            No internal users found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ── RECENT TRANSACTIONS TABLE ── -->
    <div class="card bg-dark-900/90 border border-dark-800 rounded-2xl p-5">
        <div class="flex items-center justify-between pb-4 mb-4 border-b border-dark-800">
            <div>
                <h2 class="text-base font-bold text-white">Recent Sales Transactions</h2>
                <p class="text-xs text-dark-400 mt-0.5">Live transaction log across all store registers</p>
            </div>
            <a href="{{ route('sales.index') }}" class="btn-secondary text-xs">
                View All Sales History
            </a>
        </div>

        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Customer</th>
                        <th>Cashier</th>
                        <th>Branch</th>
                        <th class="text-right">Amount</th>
                        <th class="text-center">Payment</th>
                        <th>Date & Time</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentSales as $sale)
                    <tr>
                        <td class="font-mono text-primary-400 font-medium">{{ $sale->reference }}</td>
                        <td>{{ $sale->customer_name ?: 'Walk-in Customer' }}</td>
                        <td class="text-xs text-dark-300">{{ $sale->user->name ?? '—' }}</td>
                        <td class="text-xs text-dark-400">{{ $sale->branch->name ?? 'Primary' }}</td>
                        <td class="text-right font-bold text-white">Br{{ number_format($sale->total, 2) }}</td>
                        <td class="text-center">
                            @if($sale->payment_status === 'Paid')
                                <span class="badge-green">Paid</span>
                            @elseif($sale->payment_status === 'Partial')
                                <span class="badge-yellow">Partial</span>
                            @else
                                <span class="badge-red">Unpaid</span>
                            @endif
                        </td>
                        <td class="text-dark-400 text-xs">{{ $sale->created_at->format('d M Y, H:i') }}</td>
                        <td class="text-right">
                            <a href="{{ route('sales.show', $sale) }}" class="btn-icon !p-1.5" title="View Sale & Print">
                                <svg class="w-4 h-4 text-dark-400 hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-8 text-dark-500">
                            No recent sales records.
                            <a href="{{ route('sales.create') }}" class="text-primary-400 text-xs hover:underline block mt-1">Initiate a quick sale</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
