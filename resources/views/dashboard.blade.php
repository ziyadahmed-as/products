@extends('layouts.app')
@section('title', !empty($isSeller) && $isSeller ? 'Seller Dashboard & POS' : 'Admin Dashboard')

@section('content')
@if(!empty($isSeller) && $isSeller)
    @include('partials._seller_dashboard')
@else
<div class="space-y-6">

    <!-- Top Greeting & Quick Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white border border-slate-200/80 rounded-2xl p-5 sm:p-6 shadow-xs">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                    Welcome back, {{ auth()->user()->name }}!
                </h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-sky-50 text-sky-700 border border-sky-200">
                    {{ auth()->user()->roles->first()?->name ?? 'Administrator' }}
                </span>
            </div>
            <p class="text-slate-500 text-sm mt-1">
                Real-time operational dashboard, raw materials inventory, live sales transactions, and staff performance.
            </p>
        </div>

        <!-- Quick Launch Action Buttons -->
        <div class="flex flex-wrap items-center gap-2">
            @hasanyrole('Super Admin|Manager|Seller')
            <a href="{{ route('sales.create') }}" class="btn-primary text-sm shadow-xs flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Quick Sale (POS)</span>
            </a>
            @endhasanyrole

            @hasanyrole('Super Admin|Manager|Production Officer')
            <a href="{{ route('manufacturing-orders.create') }}" class="btn-secondary text-sm flex items-center gap-2">
                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                </svg>
                <span>New Mfg Order</span>
            </a>
            @endhasanyrole

            @hasanyrole('Super Admin|Manager|Production Officer|Auditor')
            <a href="{{ route('stock-receipts.create') }}" class="btn-secondary text-sm flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span>Receive Stock</span>
            </a>
            @endhasanyrole

            @hasanyrole('Super Admin|Manager')
            <a href="{{ route('products.create', ['type' => 'raw_material']) }}" class="btn-secondary text-sm flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Add Product</span>
            </a>
            @endhasanyrole
        </div>
    </div>

    <!-- Core KPI Summary Cards Row (6 Cards Grid) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">

        <!-- 1. Total Revenue -->
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <span class="stat-label">Total Revenue</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="stat-value text-slate-900">Br {{ number_format($stats['total_revenue'], 2) }}</div>
            <div class="flex items-center gap-1.5 mt-2 text-xs">
                <span class="font-medium text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded">Br {{ number_format($stats['today_sales'], 2) }}</span>
                <span class="text-slate-600">today</span>
            </div>
        </div>

        <!-- 2. Active Mfg Orders -->
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <span class="stat-label">Active Mfg Orders</span>
                <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                    </svg>
                </div>
            </div>
            <div class="stat-value text-slate-900">{{ $stats['active_orders'] }}</div>
            <div class="flex items-center gap-1.5 mt-2 text-xs">
                <span class="font-medium text-purple-700 bg-purple-50 px-1.5 py-0.5 rounded">{{ $stats['in_progress_orders'] }} in progress</span>
                <span class="text-slate-600">{{ $stats['pending_orders'] }} pending</span>
            </div>
        </div>

        <!-- 3. Low Stock Alerts -->
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <span class="stat-label">Low Stock Alerts</span>
                <div class="w-9 h-9 rounded-xl {{ $stats['low_stock'] > 0 ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600' }} flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
            <div class="stat-value {{ $stats['low_stock'] > 0 ? 'text-amber-600' : 'text-slate-900' }}">{{ $stats['low_stock'] }}</div>
            <div class="flex items-center gap-1.5 mt-2 text-xs">
                @if($stats['low_stock'] > 0)
                    <span class="font-medium text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded">Action required</span>
                    <span class="text-slate-600">{{ $stats['low_stock_raw_materials'] }} raw mats</span>
                @else
                    <span class="font-medium text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded">All stock healthy</span>
                @endif
            </div>
        </div>

        <!-- 4. Inventory Valuation -->
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <span class="stat-label">Inventory Valuation</span>
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
            </div>
            <div class="stat-value text-slate-900">Br {{ number_format($stats['total_inventory_valuation'], 0) }}</div>
            <div class="flex items-center gap-1.5 mt-2 text-xs">
                <span class="font-medium text-blue-700 bg-blue-50 px-1.5 py-0.5 rounded">Raw Br {{ number_format($stats['raw_materials_valuation'], 0) }}</span>
            </div>
        </div>

        <!-- 5. Production Yield & Scrap -->
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <span class="stat-label">Mfg Completed</span>
                <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="stat-value text-slate-900">{{ number_format($stats['completed_production']) }}</div>
            <div class="flex items-center gap-1.5 mt-2 text-xs">
                <span class="font-medium {{ $stats['scrap_rate'] > 5 ? 'text-rose-700 bg-rose-50' : 'text-slate-700 bg-slate-100' }} px-1.5 py-0.5 rounded">Scrap {{ $stats['scrap_rate'] }}%</span>
                <span class="text-slate-600">units</span>
            </div>
        </div>

        <!-- 6. Active Products -->
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <span class="stat-label">Active Catalog</span>
                <div class="w-9 h-9 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
            </div>
            <div class="stat-value text-slate-900">{{ $stats['total_products'] }}</div>
            <div class="flex items-center gap-1.5 mt-2 text-xs">
                <span class="font-medium text-sky-700 bg-sky-50 px-1.5 py-0.5 rounded">{{ $stats['raw_materials_count'] }} raw mats</span>
                <span class="text-slate-600">items</span>
            </div>
        </div>

    </div>

    <!-- Interactive Charts Row (2 Charts: Revenue Trend & Category Distribution) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Revenue Trend Line Chart (2 cols) -->
        <div class="card lg:col-span-2">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 mb-4 border-b border-slate-100">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Revenue Trend (14 Days)</h2>
                    <p class="text-xs text-slate-600">Daily confirmed sales volume across active branch registers</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                        Month: Br {{ number_format($stats['month_sales'], 2) }}
                    </span>
                </div>
            </div>

            <!-- Chart Canvas -->
            <div class="relative w-full h-72">
                <canvas id="revenueTrendChart"></canvas>
            </div>
        </div>

        <!-- Category / Order Breakdown Donut Chart (1 col) -->
        <div class="card">
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Sales &amp; Product Share</h2>
                    <p class="text-xs text-slate-600">Revenue contribution by category</p>
                </div>
                <span class="text-xs font-semibold text-slate-600 bg-slate-100 px-2 py-0.5 rounded">Distribution</span>
            </div>

            <!-- Donut Chart Canvas -->
            <div class="relative w-full h-72 flex items-center justify-center">
                <canvas id="categoryDistributionChart"></canvas>
            </div>
        </div>

    </div>

    <!-- 1-Click Reports & CSV Export Hub -->
    <div class="card p-4 sm:p-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wide">Quick Reports &amp; Data Exports</h3>
                    <p class="text-xs text-slate-600">Download formatted CSV ledgers or open executive audit analytics</p>
                </div>
            </div>
            @hasanyrole('Super Admin|Manager|Auditor')
            <a href="{{ route('reports.index') }}" class="btn-secondary text-xs py-1.5 px-3 flex items-center gap-1.5 w-fit">
                <span>All Reports Suite</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
            @endhasanyrole
        </div>

        <!-- 6 Export Buttons Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5 pt-3">
            <a href="{{ route('reports.sales.export') }}" class="p-2.5 rounded-xl border border-slate-200/80 hover:border-sky-300 hover:bg-sky-50/50 transition flex items-center justify-between group">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-2 h-2 rounded-full bg-sky-500 flex-shrink-0"></span>
                    <span class="text-xs font-medium text-slate-700 group-hover:text-sky-700 truncate">Sales CSV</span>
                </div>
                <svg class="w-3.5 h-3.5 text-slate-600 group-hover:text-sky-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </a>

            <a href="{{ route('reports.inventory.export') }}" class="p-2.5 rounded-xl border border-slate-200/80 hover:border-emerald-300 hover:bg-emerald-50/50 transition flex items-center justify-between group">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 flex-shrink-0"></span>
                    <span class="text-xs font-medium text-slate-700 group-hover:text-emerald-700 truncate">Stock CSV</span>
                </div>
                <svg class="w-3.5 h-3.5 text-slate-600 group-hover:text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </a>

            <a href="{{ route('reports.production.export') }}" class="p-2.5 rounded-xl border border-slate-200/80 hover:border-purple-300 hover:bg-purple-50/50 transition flex items-center justify-between group">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-2 h-2 rounded-full bg-purple-500 flex-shrink-0"></span>
                    <span class="text-xs font-medium text-slate-700 group-hover:text-purple-700 truncate">Mfg CSV</span>
                </div>
                <svg class="w-3.5 h-3.5 text-slate-600 group-hover:text-purple-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </a>

            <a href="{{ route('reports.branches.export') }}" class="p-2.5 rounded-xl border border-slate-200/80 hover:border-blue-300 hover:bg-blue-50/50 transition flex items-center justify-between group">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-2 h-2 rounded-full bg-blue-500 flex-shrink-0"></span>
                    <span class="text-xs font-medium text-slate-700 group-hover:text-blue-700 truncate">Branches CSV</span>
                </div>
                <svg class="w-3.5 h-3.5 text-slate-600 group-hover:text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </a>

            <a href="{{ route('reports.users.export') }}" class="p-2.5 rounded-xl border border-slate-200/80 hover:border-amber-300 hover:bg-amber-50/50 transition flex items-center justify-between group">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-2 h-2 rounded-full bg-amber-500 flex-shrink-0"></span>
                    <span class="text-xs font-medium text-slate-700 group-hover:text-amber-700 truncate">Staff Perf CSV</span>
                </div>
                <svg class="w-3.5 h-3.5 text-slate-600 group-hover:text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </a>

            <a href="{{ route('reports.activity.export') }}" class="p-2.5 rounded-xl border border-slate-200/80 hover:border-rose-300 hover:bg-rose-50/50 transition flex items-center justify-between group">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-2 h-2 rounded-full bg-rose-500 flex-shrink-0"></span>
                    <span class="text-xs font-medium text-slate-700 group-hover:text-rose-700 truncate">Audit Trail CSV</span>
                </div>
                <svg class="w-3.5 h-3.5 text-slate-600 group-hover:text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </a>
        </div>
    </div>

    <!-- Staff & User Performance Table (Real Data) -->
    @hasanyrole('Super Admin|Manager|Admin')
    <div class="card p-0 overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h2 class="text-base font-bold text-slate-900">Staff &amp; Operational Team Performance</h2>
                <p class="text-xs text-slate-600">Personnel production records, sales metrics, and activity tracking</p>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-700">
                {{ count($userPerformances) }} Staff Members
            </span>
        </div>

        <div class="table-wrapper border-0 rounded-none shadow-none">
            <table class="table">
                <thead>
                    <tr>
                        <th>User / Employee</th>
                        <th>Role</th>
                        <th>Assigned Branch</th>
                        <th>Sales Output</th>
                        <th>Mfg Orders</th>
                        <th>Activity Count</th>
                        <th>Last Active</th>
                        <th class="text-right">Performance Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($userPerformances as $perf)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-sky-100 text-sky-800 font-bold flex items-center justify-center text-xs flex-shrink-0">
                                    {{ strtoupper(substr($perf['user']->name ?? 'U', 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-semibold text-slate-900 text-sm truncate">{{ $perf['user']->name }}</div>
                                    <div class="text-xs text-slate-600 truncate">{{ $perf['user']->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge {{ $perf['role'] === 'Super Admin' ? 'badge-purple' : ($perf['role'] === 'Manager' ? 'badge-blue' : ($perf['role'] === 'Seller' ? 'badge-green' : 'badge-gray')) }}">
                                {{ $perf['role'] }}
                            </span>
                        </td>
                        <td>
                            <span class="text-xs font-medium text-slate-600">{{ $perf['branches'] }}</span>
                        </td>
                        <td>
                            <div class="font-semibold text-slate-900 text-sm">Br {{ number_format($perf['sales_volume'], 2) }}</div>
                            <div class="text-xs text-slate-600">{{ $perf['sales_count'] }} transactions</div>
                        </td>
                        <td>
                            <div class="font-semibold text-slate-900 text-sm">{{ number_format($perf['mfg_output']) }} units</div>
                            <div class="text-xs text-slate-600">{{ $perf['mfg_count'] }} orders</div>
                        </td>
                        <td>
                            <span class="font-mono text-xs font-semibold text-slate-700 bg-slate-100 px-2 py-0.5 rounded">{{ $perf['activities'] }}</span>
                        </td>
                        <td>
                            <span class="text-xs text-slate-600">
                                {{ $perf['last_action'] ? \Carbon\Carbon::parse($perf['last_action'])->diffForHumans() : 'No recent logs' }}
                            </span>
                        </td>
                        <td class="text-right">
                            <span class="badge {{ $perf['status'] === 'Top Performer' ? 'badge-green' : ($perf['status'] === 'Active' ? 'badge-blue' : 'badge-gray') }}">
                                {{ $perf['status'] }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-6 text-slate-600">No staff performance records available.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endhasanyrole

    <!-- Live Transactions & Open Manufacturing Orders (2-Column Grid) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Recent Sales Transactions -->
        <div class="card p-0 overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Recent Sales Transactions</h2>
                    <p class="text-xs text-slate-600">Live transaction log across store registers</p>
                </div>
                <a href="{{ route('sales.index') }}" class="text-xs font-semibold text-sky-600 hover:text-sky-700 hover:underline">
                    View All Sales
                </a>
            </div>

            <div class="table-wrapper border-0 rounded-none shadow-none">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Cashier / User</th>
                            <th>Branch</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentSales as $sale)
                        <tr>
                            <td>
                                <span class="font-mono text-xs font-bold text-sky-700">#{{ $sale->reference ?? $sale->id }}</span>
                            </td>
                            <td>
                                <span class="text-xs font-medium text-slate-800">{{ $sale->user?->name ?? 'Guest Cashier' }}</span>
                            </td>
                            <td>
                                <span class="text-xs text-slate-600">{{ $sale->branch?->name ?? 'Main Store' }}</span>
                            </td>
                            <td>
                                <span class="font-bold text-slate-900 text-xs">Br {{ number_format($sale->total, 2) }}</span>
                            </td>
                            <td>
                                <span class="text-xs text-slate-600">{{ $sale->created_at->format('M d, H:i') }}</span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('invoice.show', $sale) }}" class="text-xs font-semibold text-sky-600 hover:text-sky-700 hover:underline">
                                    Invoice
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-6 text-slate-600">
                                No sales recorded yet.
                                @hasanyrole('Super Admin|Manager|Seller')
                                <a href="{{ route('sales.create') }}" class="text-sky-600 font-semibold hover:underline block mt-1">Initiate a quick sale</a>
                                @endhasanyrole
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Open Manufacturing Orders -->
        <div class="card p-0 overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Active Production Batches</h2>
                    <p class="text-xs text-slate-600">Orders currently on the manufacturing floor</p>
                </div>
                <a href="{{ route('manufacturing-orders.index') }}" class="text-xs font-semibold text-purple-600 hover:text-purple-700 hover:underline">
                    View All Orders
                </a>
            </div>

            <div class="table-wrapper border-0 rounded-none shadow-none">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($openOrders as $order)
                        <tr>
                            <td>
                                <span class="font-mono text-xs font-bold text-purple-700">#{{ $order->order_number ?? $order->id }}</span>
                            </td>
                            <td>
                                <div class="font-medium text-slate-900 text-xs truncate max-w-[150px]">{{ $order->product?->name ?? 'Product' }}</div>
                                <div class="text-[11px] text-slate-600">{{ $order->storageLocation?->name ?? 'Floor' }}</div>
                            </td>
                            <td>
                                <span class="text-xs font-semibold text-slate-800">{{ number_format($order->planned_quantity) }}</span>
                                <span class="text-[11px] text-slate-600">{{ $order->product?->unit?->code ?? 'units' }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $order->status === 'in_progress' ? 'badge-blue' : ($order->status === 'approved' ? 'badge-green' : 'badge-yellow') }}">
                                    {{ str_replace('_', ' ', $order->status) }}
                                </span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('manufacturing-orders.show', $order) }}" class="text-xs font-semibold text-purple-600 hover:text-purple-700 hover:underline">
                                    Manage
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-6 text-slate-600">
                                No active production orders in progress.
                                @hasanyrole('Super Admin|Manager|Production Officer')
                                <a href="{{ route('manufacturing-orders.create') }}" class="text-purple-600 font-semibold hover:underline block mt-1">Create production batch</a>
                                @endhasanyrole
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Low Stock Inventory Alerts & Live Audit Logs (2-Column Grid) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Low Stock Items Warning -->
        <div class="card p-0 overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Low Stock Reorder Alerts</h2>
                    <p class="text-xs text-slate-600">Items reaching or falling below safety stock threshold</p>
                </div>
                <a href="{{ route('inventory.index') }}" class="text-xs font-semibold text-sky-600 hover:text-sky-700 hover:underline">
                    Stock Balances
                </a>
            </div>

            <div class="table-wrapper border-0 rounded-none shadow-none">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Location</th>
                            <th>Current Qty</th>
                            <th>Safety Min</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lowStockItems as $item)
                        <tr>
                            <td>
                                <div class="font-semibold text-slate-900 text-xs">{{ $item->product?->name ?? 'Item' }}</div>
                                <div class="text-[11px] text-slate-600 font-mono">{{ $item->product?->sku ?? '' }}</div>
                            </td>
                            <td>
                                <span class="text-xs text-slate-600">{{ $item->storageLocation?->name ?? 'Default' }}</span>
                            </td>
                            <td>
                                <span class="font-bold text-rose-600 text-xs">{{ number_format($item->quantity, 2) }}</span>
                            </td>
                            <td>
                                <span class="text-xs text-slate-600">{{ number_format($item->product?->minimum_stock_level ?? 0, 2) }}</span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('stock-receipts.create') }}" class="btn-secondary text-[11px] py-1 px-2.5">
                                    Restock
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-6 text-slate-600">
                                <span class="text-emerald-600 font-semibold">All inventory levels are healthy!</span>
                                <p class="text-xs text-slate-600 mt-0.5">No products currently below minimum stock threshold.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Audit Activities Log Feed -->
        <div class="card p-0 overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Recent System Activities &amp; Audit</h2>
                    <p class="text-xs text-slate-600">Real-time log of administrative and operational actions</p>
                </div>
                @hasanyrole('Super Admin|Auditor')
                <a href="{{ route('reports.activity') }}" class="text-xs font-semibold text-sky-600 hover:text-sky-700 hover:underline">
                    Full Audit Trail
                </a>
                @endhasanyrole
            </div>

            <div class="divide-y divide-slate-100 max-h-96 overflow-y-auto">
                @forelse($recentActivities as $act)
                <div class="p-3.5 sm:px-5 flex items-center justify-between gap-3 hover:bg-slate-50 transition">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-xs flex-shrink-0">
                            {{ strtoupper(substr($act->causer?->name ?? 'S', 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-slate-800 truncate">
                                {{ $act->causer?->name ?? 'System Event' }}
                                <span class="font-normal text-slate-600">— {{ $act->description }}</span>
                            </p>
                            <p class="text-[11px] text-slate-600 truncate">
                                Log: {{ $act->log_name ?? 'default' }}
                                @if($act->subject_type)
                                    · {{ class_basename($act->subject_type) }} #{{ $act->subject_id }}
                                @endif
                            </p>
                        </div>
                    </div>
                    <span class="text-[11px] text-slate-600 flex-shrink-0 whitespace-nowrap">
                        {{ $act->created_at->diffForHumans() }}
                    </span>
                </div>
                @empty
                <div class="text-center py-8 text-slate-600">
                    <p class="text-sm">No activity logs recorded yet today.</p>
                </div>
                @endforelse
            </div>
        </div>

    </div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Revenue Trajectory Chart (Line / Area)
    const revCtx = document.getElementById('revenueTrendChart');
    if (revCtx) {
        const revLabels = @json($revenueChart['labels'] ?? []);
        const revData   = @json($revenueChart['data'] ?? []);

        new Chart(revCtx, {
            type: 'line',
            data: {
                labels: revLabels,
                datasets: [{
                    label: 'Revenue (Br)',
                    data: revData,
                    borderColor: '#0284c7',
                    backgroundColor: function(context) {
                        const chart = context.chart;
                        const {ctx, chartArea} = chart;
                        if (!chartArea) return null;
                        const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                        gradient.addColorStop(0, 'rgba(2, 132, 199, 0.25)');
                        gradient.addColorStop(1, 'rgba(2, 132, 199, 0.01)');
                        return gradient;
                    },
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#0284c7',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleColor: '#f8fafc',
                        bodyColor: '#38bdf8',
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                return ' Revenue: Br ' + Number(context.raw).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#64748b', font: { size: 11 } }
                    },
                    y: {
                        border: { dash: [4, 4] },
                        grid: { color: '#f1f5f9' },
                        ticks: {
                            color: '#64748b',
                            font: { size: 11 },
                            callback: function(value) {
                                return 'Br ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    // 2. Category Share Chart (Donut)
    const catCtx = document.getElementById('categoryDistributionChart');
    if (catCtx) {
        const catLabels = @json($categoryChart['labels'] ?? []);
        const catData   = @json($categoryChart['data'] ?? []);

        new Chart(catCtx, {
            type: 'doughnut',
            data: {
                labels: catLabels,
                datasets: [{
                    data: catData,
                    backgroundColor: [
                        '#0284c7',
                        '#10b981',
                        '#8b5cf6',
                        '#f59e0b',
                        '#06b6d4',
                        '#ec4899',
                        '#64748b'
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#475569',
                            font: { size: 11, family: 'Inter' },
                            padding: 12,
                            boxWidth: 10,
                            boxHeight: 10,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                return ' ' + context.label + ': Br ' + Number(context.raw).toLocaleString('en-US');
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush
@endif
@endsection
