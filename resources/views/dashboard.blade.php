@extends('layouts.app')
@section('title', !empty($isSeller) && $isSeller ? 'Seller Dashboard & POS' : 'Admin Dashboard')

@section('content')
@if(!empty($isSeller) && $isSeller)
    @include('partials._seller_dashboard')
@else
<div class="space-y-6">

    <!-- Dashboard Title matching Image 1 -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-1">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Dashboard</h1>
            <p class="text-xs text-slate-500 dark:text-dark-400 mt-0.5">Welcome back, {{ auth()->user()->name }}! Real-time operations, inventory, and sales performance.</p>
        </div>
        <div class="hidden sm:flex items-center gap-2 text-xs text-slate-500 dark:text-dark-400 font-medium">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-100 dark:bg-dark-800 border border-slate-200 dark:border-dark-700">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                {{ auth()->user()->roles->first()?->name ?? 'Administrator' }}
            </span>
        </div>
    </div>

    <!-- Core KPI Summary Cards Row (6 Cards — 2 rows × 3 cols, Screenshot Match) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

        <!-- 1. Revenue (Selected Period) — green bottom accent matching screenshot -->
        <div class="bg-white dark:bg-dark-900 rounded-2xl border border-slate-200/80 dark:border-dark-800 p-5 shadow-xs" style="border-bottom: 3px solid #10b981;">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-black dark:text-white uppercase tracking-wide">Revenue (Selected Period)</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-black dark:text-white mb-2">Br {{ number_format($stats['selected_revenue'] ?? $stats['month_sales'], 2) }}</div>
            <div class="flex items-center gap-1.5 text-xs">
                @php $trend = $stats['revenue_trend'] ?? 0; @endphp
                @if($trend >= 0)
                    <span class="inline-flex items-center gap-1 font-semibold text-emerald-700 bg-emerald-50 dark:bg-emerald-950/70 dark:text-emerald-400 px-2 py-0.5 rounded-full">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                        {{ $trend }}% vs previous period
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 font-semibold text-rose-700 bg-rose-50 dark:bg-rose-950/70 dark:text-rose-400 px-2 py-0.5 rounded-full">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                        {{ abs($trend) }}% vs previous period
                    </span>
                @endif
            </div>
        </div>

        <!-- 2. Orders Today -->
        <div class="bg-white dark:bg-dark-900 rounded-2xl border border-slate-200/80 dark:border-dark-800 p-5 shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-black dark:text-white uppercase tracking-wide">Orders Today</span>
                <div class="w-9 h-9 rounded-xl bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-black dark:text-white mb-2">{{ $stats['orders_today'] ?? $stats['today_sales_count'] }}</div>
            <div class="flex items-center gap-1.5 text-xs">
                <span class="inline-flex items-center gap-1 text-amber-700 bg-amber-50 dark:bg-amber-950/70 dark:text-amber-400 px-2 py-0.5 rounded-full font-medium">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ $stats['pending_sales_orders'] ?? 0 }} pending approval
                </span>
            </div>
        </div>

        <!-- 3. Pending Orders -->
        <div class="bg-white dark:bg-dark-900 rounded-2xl border border-slate-200/80 dark:border-dark-800 p-5 shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-black dark:text-white uppercase tracking-wide">Pending Orders</span>
                <div class="w-9 h-9 rounded-xl {{ ($stats['pending_sales_orders'] ?? 0) > 0 ? 'bg-amber-50 dark:bg-amber-950/60 text-amber-500' : 'bg-slate-100 dark:bg-dark-800 text-slate-500 dark:text-dark-400' }} flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl font-bold {{ ($stats['pending_sales_orders'] ?? 0) > 0 ? 'text-amber-500' : 'text-black dark:text-white' }} mb-2">{{ $stats['pending_sales_orders'] ?? 0 }}</div>
            <div class="text-xs text-black dark:text-white">Awaiting processing across all time ≡</div>
        </div>

        <!-- 4. Low Stock Alerts -->
        <div class="bg-white dark:bg-dark-900 rounded-2xl border border-slate-200/80 dark:border-dark-800 p-5 shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-black dark:text-white uppercase tracking-wide">Low Stock Alerts</span>
                <div class="w-9 h-9 rounded-xl {{ $stats['low_stock'] > 0 ? 'bg-amber-50 dark:bg-amber-950/60 text-amber-500' : 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-500' }} flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl font-bold {{ $stats['low_stock'] > 0 ? 'text-amber-500' : 'text-black dark:text-white' }} mb-2">{{ $stats['low_stock'] }}</div>
            <div class="text-xs text-black dark:text-white">
                @if($stats['low_stock'] > 0)
                    <span class="text-amber-500 font-medium">Action required</span> · {{ $stats['low_stock_raw_materials'] }} raw materials
                @else
                    All stock levels healthy ✔
                @endif
            </div>
        </div>

        <!-- 5. Active Products -->
        <div class="bg-white dark:bg-dark-900 rounded-2xl border border-slate-200/80 dark:border-dark-800 p-5 shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-black dark:text-white uppercase tracking-wide">Active Products</span>
                <div class="w-9 h-9 rounded-xl bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-black dark:text-white mb-2">{{ $stats['total_products'] }}</div>
            <div class="text-xs text-black dark:text-white">Listed in storefront 📦</div>
        </div>

        <!-- 6. Production Orders -->
        <div class="bg-white dark:bg-dark-900 rounded-2xl border border-slate-200/80 dark:border-dark-800 p-5 shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-black dark:text-white uppercase tracking-wide">Production Orders</span>
                <div class="w-9 h-9 rounded-xl {{ $stats['active_production_orders'] > 0 ? 'bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400' : 'bg-slate-100 dark:bg-dark-800 text-slate-500 dark:text-dark-400' }} flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-black dark:text-white mb-2">{{ $stats['active_production_orders'] }}</div>
            <div class="text-xs text-black dark:text-white">{{ $stats['completed_production_period'] ?? 0 }} completed in selected period ⚙️</div>
        </div>

    </div>

    <!-- Interactive Charts Row (Revenue Trend + Orders by Type) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Revenue Trend Line Chart (2 cols) -->
        <div class="card lg:col-span-2 bg-white dark:bg-dark-900 border border-slate-200/80 dark:border-dark-800">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 mb-4 border-b border-slate-100 dark:border-dark-800">
                <div>
                    <h2 class="text-base font-bold text-black dark:text-white">Revenue Trend (14 Days)</h2>
                    <p class="text-xs text-black dark:text-white">Daily confirmed sales volume across active branch registers</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                        Month: Br {{ number_format($stats['month_sales'], 2) }}
                    </span>
                </div>
            </div>
            <div class="relative w-full h-72">
                <canvas id="revenueTrendChart"></canvas>
            </div>
        </div>

        <!-- Orders by Type Doughnut Chart (1 col) -->
        <div class="card bg-white dark:bg-dark-900 border border-slate-200/80 dark:border-dark-800">
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100 dark:border-dark-800">
                <div>
                    <h2 class="text-base font-bold text-black dark:text-white">Orders by Type</h2>
                    <p class="text-xs text-black dark:text-white">Online, POS &amp; Direct breakdown</p>
                </div>
                <span class="text-xs font-semibold text-black dark:text-white bg-slate-100 dark:bg-dark-800 px-2 py-0.5 rounded border border-slate-200 dark:border-dark-700">Distribution</span>
            </div>
            <div class="relative w-full h-52 flex items-center justify-center">
                <canvas id="ordersByTypeChart"></canvas>
            </div>
            <div class="mt-4 grid grid-cols-3 divide-x divide-slate-100 dark:divide-dark-800 text-center">
                <div class="px-2">
                    <div class="text-[11px] text-black dark:text-white font-medium mb-0.5">Online</div>
                    <div class="text-base font-bold text-sky-600 dark:text-sky-400">{{ $ordersByTypeChart['data'][0] ?? 0 }}</div>
                </div>
                <div class="px-2">
                    <div class="text-[11px] text-black dark:text-white font-medium mb-0.5">POS</div>
                    <div class="text-base font-bold text-emerald-600 dark:text-emerald-400">{{ $ordersByTypeChart['data'][1] ?? 0 }}</div>
                </div>
                <div class="px-2">
                    <div class="text-[11px] text-black dark:text-white font-medium mb-0.5">Direct</div>
                    <div class="text-base font-bold text-amber-600 dark:text-amber-400">{{ $ordersByTypeChart['data'][2] ?? 0 }}</div>
                </div>
            </div>
        </div>

    </div>

    <!-- 1-Click Reports & CSV Export Hub -->
    <div class="card p-4 sm:p-5 bg-white dark:bg-dark-900 border border-slate-200/80 dark:border-dark-800">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100 dark:border-dark-800">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-black dark:text-white uppercase tracking-wide">Quick Reports &amp; Data Exports</h3>
                    <p class="text-xs text-black dark:text-white">Download formatted CSV ledgers or open executive audit analytics</p>
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
            <a href="{{ route('reports.sales.export') }}" class="p-2.5 rounded-xl border border-slate-200/80 dark:border-dark-700 bg-white dark:bg-dark-800 hover:border-sky-300 dark:hover:border-sky-500 hover:bg-sky-50/50 dark:hover:bg-dark-700 transition flex items-center justify-between group">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-2 h-2 rounded-full bg-sky-500 flex-shrink-0"></span>
                    <span class="text-xs font-medium text-black dark:text-white group-hover:text-sky-600 dark:group-hover:text-sky-400 truncate">Sales CSV</span>
                </div>
                <svg class="w-3.5 h-3.5 text-black dark:text-white group-hover:text-sky-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </a>

            <a href="{{ route('reports.inventory.export') }}" class="p-2.5 rounded-xl border border-slate-200/80 dark:border-dark-700 bg-white dark:bg-dark-800 hover:border-emerald-300 dark:hover:border-emerald-500 hover:bg-emerald-50/50 dark:hover:bg-dark-700 transition flex items-center justify-between group">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 flex-shrink-0"></span>
                    <span class="text-xs font-medium text-black dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 truncate">Stock CSV</span>
                </div>
                <svg class="w-3.5 h-3.5 text-black dark:text-white group-hover:text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </a>

            <a href="{{ route('reports.production.export') }}" class="p-2.5 rounded-xl border border-slate-200/80 dark:border-dark-700 bg-white dark:bg-dark-800 hover:border-purple-300 dark:hover:border-purple-500 hover:bg-purple-50/50 dark:hover:bg-dark-700 transition flex items-center justify-between group">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-2 h-2 rounded-full bg-purple-500 flex-shrink-0"></span>
                    <span class="text-xs font-medium text-black dark:text-white group-hover:text-purple-600 dark:group-hover:text-purple-400 truncate">Mfg CSV</span>
                </div>
                <svg class="w-3.5 h-3.5 text-black dark:text-white group-hover:text-purple-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </a>

            <a href="{{ route('reports.branches.export') }}" class="p-2.5 rounded-xl border border-slate-200/80 dark:border-dark-700 bg-white dark:bg-dark-800 hover:border-blue-300 dark:hover:border-blue-500 hover:bg-blue-50/50 dark:hover:bg-dark-700 transition flex items-center justify-between group">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-2 h-2 rounded-full bg-blue-500 flex-shrink-0"></span>
                    <span class="text-xs font-medium text-black dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 truncate">Branches CSV</span>
                </div>
                <svg class="w-3.5 h-3.5 text-black dark:text-white group-hover:text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </a>

            <a href="{{ route('reports.users.export') }}" class="p-2.5 rounded-xl border border-slate-200/80 dark:border-dark-700 bg-white dark:bg-dark-800 hover:border-amber-300 dark:hover:border-amber-500 hover:bg-amber-50/50 dark:hover:bg-dark-700 transition flex items-center justify-between group">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-2 h-2 rounded-full bg-amber-500 flex-shrink-0"></span>
                    <span class="text-xs font-medium text-black dark:text-white group-hover:text-amber-600 dark:group-hover:text-amber-400 truncate">Staff Perf CSV</span>
                </div>
                <svg class="w-3.5 h-3.5 text-black dark:text-white group-hover:text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </a>

            <a href="{{ route('reports.activity.export') }}" class="p-2.5 rounded-xl border border-slate-200/80 dark:border-dark-700 bg-white dark:bg-dark-800 hover:border-rose-300 dark:hover:border-rose-500 hover:bg-rose-50/50 dark:hover:bg-dark-700 transition flex items-center justify-between group">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-2 h-2 rounded-full bg-rose-500 flex-shrink-0"></span>
                    <span class="text-xs font-medium text-black dark:text-white group-hover:text-rose-600 dark:group-hover:text-rose-400 truncate">Audit Trail CSV</span>
                </div>
                <svg class="w-3.5 h-3.5 text-black dark:text-white group-hover:text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </a>
        </div>
    </div>

    <!-- Staff & User Performance Table (Real Data) -->
    @hasanyrole('Super Admin|Manager|Admin')
    <div class="card p-0 overflow-hidden bg-white dark:bg-dark-900 border border-slate-200/80 dark:border-dark-800">
        <div class="p-5 border-b border-slate-100 dark:border-dark-800 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h2 class="text-base font-bold text-black dark:text-white">Staff &amp; Operational Team Performance</h2>
                <p class="text-xs text-black dark:text-white">Personnel production records, sales metrics, and activity tracking</p>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 dark:bg-dark-800 text-black dark:text-white">
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
                                <div class="w-8 h-8 rounded-full bg-sky-100 dark:bg-sky-900/60 text-sky-800 dark:text-sky-300 font-bold flex items-center justify-center text-xs flex-shrink-0">
                                    {{ strtoupper(substr($perf['user']->name ?? 'U', 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-semibold text-black dark:text-white text-sm truncate">{{ $perf['user']->name }}</div>
                                    <div class="text-xs text-black dark:text-white truncate">{{ $perf['user']->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge {{ $perf['role'] === 'Super Admin' ? 'badge-purple' : ($perf['role'] === 'Manager' ? 'badge-blue' : ($perf['role'] === 'Seller' ? 'badge-green' : 'badge-gray')) }}">
                                {{ $perf['role'] }}
                            </span>
                        </td>
                        <td>
                            <span class="text-xs font-medium text-black dark:text-white">{{ $perf['branches'] }}</span>
                        </td>
                        <td>
                            <div class="font-semibold text-black dark:text-white text-sm">Br {{ number_format($perf['sales_volume'], 2) }}</div>
                            <div class="text-xs text-black dark:text-white">{{ $perf['sales_count'] }} transactions</div>
                        </td>
                        <td>
                            <div class="font-semibold text-black dark:text-white text-sm">{{ number_format($perf['mfg_output']) }} units</div>
                            <div class="text-xs text-black dark:text-white">{{ $perf['mfg_count'] }} orders</div>
                        </td>
                        <td>
                            <span class="font-mono text-xs font-semibold text-black dark:text-white bg-slate-100 dark:bg-dark-800 px-2 py-0.5 rounded">{{ $perf['activities'] }}</span>
                        </td>
                        <td>
                            <span class="text-xs text-black dark:text-white">
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
                        <td colspan="8" class="text-center py-6 text-black dark:text-white">No staff performance records available.</td>
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
        <div class="card p-0 overflow-hidden bg-white dark:bg-dark-900 border border-slate-200/80 dark:border-dark-800">
            <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-dark-800 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-black dark:text-white">Recent Sales Transactions</h2>
                    <p class="text-xs text-black dark:text-white">Live transaction log across store registers</p>
                </div>
                <a href="{{ route('sales.index') }}" class="text-xs font-semibold text-sky-600 dark:text-sky-400 hover:text-sky-700 hover:underline">
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
                                <span class="font-mono text-xs font-bold text-sky-600 dark:text-sky-400">#{{ $sale->reference ?? $sale->id }}</span>
                            </td>
                            <td>
                                <span class="text-xs font-medium text-black dark:text-white">{{ $sale->user?->name ?? 'Guest Cashier' }}</span>
                            </td>
                            <td>
                                <span class="text-xs text-black dark:text-white">{{ $sale->branch?->name ?? 'Main Store' }}</span>
                            </td>
                            <td>
                                <span class="font-bold text-black dark:text-white text-xs">Br {{ number_format($sale->total, 2) }}</span>
                            </td>
                            <td>
                                <span class="text-xs text-black dark:text-white">{{ $sale->created_at->format('M d, H:i') }}</span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('invoice.show', $sale) }}" class="text-xs font-semibold text-sky-600 dark:text-sky-400 hover:text-sky-700 hover:underline">
                                    Invoice
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-6 text-black dark:text-white">
                                No sales recorded yet.
                                @hasanyrole('Super Admin|Manager|Seller')
                                <a href="{{ route('sales.create') }}" class="text-sky-600 dark:text-sky-400 font-semibold hover:underline block mt-1">Initiate a quick sale</a>
                                @endhasanyrole
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Open Manufacturing Orders -->
        <div class="card p-0 overflow-hidden bg-white dark:bg-dark-900 border border-slate-200/80 dark:border-dark-800">
            <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-dark-800 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-black dark:text-white">Active Production Batches</h2>
                    <p class="text-xs text-black dark:text-white">Orders currently on the manufacturing floor</p>
                </div>
                <a href="{{ route('manufacturing-orders.index') }}" class="text-xs font-semibold text-purple-600 dark:text-purple-400 hover:text-purple-700 hover:underline">
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
                                <span class="font-mono text-xs font-bold text-purple-600 dark:text-purple-400">#{{ $order->order_number ?? $order->id }}</span>
                            </td>
                            <td>
                                <div class="font-medium text-black dark:text-white text-xs truncate max-w-[150px]">{{ $order->product?->name ?? 'Product' }}</div>
                                <div class="text-[11px] text-black dark:text-white">{{ $order->storageLocation?->name ?? 'Floor' }}</div>
                            </td>
                            <td>
                                <span class="text-xs font-semibold text-black dark:text-white">{{ number_format($order->planned_quantity) }}</span>
                                <span class="text-[11px] text-black dark:text-white">{{ $order->product?->unit?->code ?? 'units' }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $order->status === 'in_progress' ? 'badge-blue' : ($order->status === 'approved' ? 'badge-green' : 'badge-yellow') }}">
                                    {{ str_replace('_', ' ', $order->status) }}
                                </span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('manufacturing-orders.show', $order) }}" class="text-xs font-semibold text-purple-600 dark:text-purple-400 hover:text-purple-700 hover:underline">
                                    Manage
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-6 text-black dark:text-white">
                                No active production orders in progress.
                                @hasanyrole('Super Admin|Manager|Production Officer')
                                <a href="{{ route('manufacturing-orders.create') }}" class="text-purple-600 dark:text-purple-400 font-semibold hover:underline block mt-1">Create production batch</a>
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
        <div class="card p-0 overflow-hidden bg-white dark:bg-dark-900 border border-slate-200/80 dark:border-dark-800">
            <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-dark-800 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-black dark:text-white">Low Stock Reorder Alerts</h2>
                    <p class="text-xs text-black dark:text-white">Items reaching or falling below safety stock threshold</p>
                </div>
                <a href="{{ route('inventory.index') }}" class="text-xs font-semibold text-sky-600 dark:text-sky-400 hover:text-sky-700 hover:underline">
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
                                <div class="font-semibold text-black dark:text-white text-xs">{{ $item->product?->name ?? 'Item' }}</div>
                                <div class="text-[11px] text-black dark:text-white font-mono">{{ $item->product?->sku ?? '' }}</div>
                            </td>
                            <td>
                                <span class="text-xs text-black dark:text-white">{{ $item->storageLocation?->name ?? 'Default' }}</span>
                            </td>
                            <td>
                                <span class="font-bold text-rose-600 dark:text-rose-400 text-xs">{{ number_format($item->quantity, 2) }}</span>
                            </td>
                            <td>
                                <span class="text-xs text-black dark:text-white">{{ number_format($item->product?->minimum_stock_level ?? 0, 2) }}</span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('stock-receipts.create') }}" class="btn-secondary text-[11px] py-1 px-2.5">
                                    Restock
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-6 text-black dark:text-white">
                                <span class="text-emerald-600 dark:text-emerald-400 font-semibold">All inventory levels are healthy!</span>
                                <p class="text-xs text-black dark:text-white mt-0.5">No products currently below minimum stock threshold.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Audit Activities Log Feed -->
        <div class="card p-0 overflow-hidden bg-white dark:bg-dark-900 border border-slate-200/80 dark:border-dark-800">
            <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-dark-800 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-black dark:text-white">Recent System Activities &amp; Audit</h2>
                    <p class="text-xs text-black dark:text-white">Real-time log of administrative and operational actions</p>
                </div>
                @hasanyrole('Super Admin|Auditor')
                <a href="{{ route('reports.activity') }}" class="text-xs font-semibold text-sky-600 dark:text-sky-400 hover:text-sky-700 hover:underline">
                    Full Audit Trail
                </a>
                @endhasanyrole
            </div>

            <div class="divide-y divide-slate-100 dark:divide-dark-800 max-h-96 overflow-y-auto">
                @forelse($recentActivities as $act)
                <div class="p-3.5 sm:px-5 flex items-center justify-between gap-3 hover:bg-slate-50 dark:hover:bg-dark-800 transition">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-dark-800 text-black dark:text-white flex items-center justify-center font-bold text-xs flex-shrink-0">
                            {{ strtoupper(substr($act->causer?->name ?? 'S', 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-black dark:text-white truncate">
                                {{ $act->causer?->name ?? 'System Event' }}
                                <span class="font-normal text-black dark:text-white">— {{ $act->description }}</span>
                            </p>
                            <p class="text-[11px] text-black dark:text-white truncate">
                                Log: {{ $act->log_name ?? 'default' }}
                                @if($act->subject_type)
                                    · {{ class_basename($act->subject_type) }} #{{ $act->subject_id }}
                                @endif
                            </p>
                        </div>
                    </div>
                    <span class="text-[11px] text-black dark:text-white flex-shrink-0 whitespace-nowrap">
                        {{ $act->created_at->diffForHumans() }}
                    </span>
                </div>
                @empty
                <div class="text-center py-8 text-black dark:text-white">
                    <p class="text-sm">No activity logs recorded yet today.</p>
                </div>
                @endforelse
            </div>
        </div>

    </div>

</div>

@push('scripts')
<script>
function isDarkMode() {
    return document.documentElement.classList.contains('dark');
}

function getTickColor() {
    return isDarkMode() ? '#ffffff' : '#64748b';
}

function getGridColor() {
    return isDarkMode() ? '#1e293b' : '#f1f5f9';
}

function buildCharts() {
    // 1. Revenue Trajectory Chart (Line / Area)
    const revCtx = document.getElementById('revenueTrendChart');
    if (revCtx) {
        const revLabels = @json($revenueChart['labels'] ?? []);
        const revData   = @json($revenueChart['data'] ?? []);

        const revChart = new Chart(revCtx, {
            type: 'line',
            data: {
                labels: revLabels,
                datasets: [{
                    label: 'Revenue (Br)',
                    data: revData,
                    borderColor: '#06b6d4',
                    backgroundColor: function(context) {
                        const chart = context.chart;
                        const {ctx, chartArea} = chart;
                        if (!chartArea) return null;
                        const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                        gradient.addColorStop(0, 'rgba(6, 182, 212, 0.22)');
                        gradient.addColorStop(1, 'rgba(6, 182, 212, 0.01)');
                        return gradient;
                    },
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#06b6d4',
                    pointBorderColor: isDarkMode() ? '#0f172a' : '#ffffff',
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
                        backgroundColor: isDarkMode() ? '#1e293b' : '#0f172a',
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
                        ticks: { color: getTickColor(), font: { size: 11 } }
                    },
                    y: {
                        border: { dash: [4, 4] },
                        grid: { color: getGridColor() },
                        ticks: {
                            color: getTickColor(),
                            font: { size: 11 },
                            callback: function(value) {
                                return 'Br ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Update chart colors when theme changes
        window.addEventListener('themechanged', function() {
            revChart.options.scales.x.ticks.color = getTickColor();
            revChart.options.scales.y.ticks.color = getTickColor();
            revChart.options.scales.y.grid.color = getGridColor();
            revChart.options.plugins.tooltip.backgroundColor = isDarkMode() ? '#1e293b' : '#0f172a';
            revChart.data.datasets[0].pointBorderColor = isDarkMode() ? '#0f172a' : '#ffffff';
            revChart.update();
        });
    }

    // 2. Orders by Type Doughnut Chart
    const ordersByTypeCtx = document.getElementById('ordersByTypeChart');
    if (ordersByTypeCtx) {
        const ordersLabels = @json($ordersByTypeChart['labels'] ?? ['Online', 'POS', 'Direct']);
        const ordersData   = @json($ordersByTypeChart['data'] ?? [0, 0, 0]);

        const doughnutChart = new Chart(ordersByTypeCtx, {
            type: 'doughnut',
            data: {
                labels: ordersLabels,
                datasets: [{
                    data: ordersData,
                    backgroundColor: ['#0ea5e9', '#10b981', '#f59e0b'],
                    borderWidth: 3,
                    borderColor: isDarkMode() ? '#0f172a' : '#ffffff',
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: isDarkMode() ? '#1e293b' : '#0f172a',
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const pct = total > 0 ? Math.round((context.raw / total) * 100) : 0;
                                return ' ' + context.label + ': ' + context.raw + ' orders (' + pct + '%)';
                            }
                        }
                    }
                }
            }
        });

        window.addEventListener('themechanged', function() {
            doughnutChart.data.datasets[0].borderColor = isDarkMode() ? '#0f172a' : '#ffffff';
            doughnutChart.options.plugins.tooltip.backgroundColor = isDarkMode() ? '#1e293b' : '#0f172a';
            doughnutChart.update();
        });
    }
}

document.addEventListener('DOMContentLoaded', buildCharts);
</script>
@endpush
@endif
@endsection
