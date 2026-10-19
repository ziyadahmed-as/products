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
            @if(auth()->user()->branches->isNotEmpty())
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-cyan-50 dark:bg-cyan-950/60 text-cyan-700 dark:text-cyan-300 border border-cyan-200 dark:border-cyan-800 font-semibold">
                    <span class="w-1.5 h-1.5 rounded-full bg-cyan-500"></span>
                    Branch: {{ auth()->user()->branches->pluck('name')->join(', ') }}
                </span>
            @endif
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-100 dark:bg-dark-800 border border-slate-200 dark:border-dark-700 text-slate-700 dark:text-slate-200 font-semibold">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                {{ auth()->user()->roles->first()?->name ?? 'Administrator' }}
            </span>
        </div>
    </div>

    <!-- Print-Only Corporate Report Header -->
    <div class="hidden print:block pb-6 mb-6 border-b-2 border-slate-900">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="{{ \App\Models\Setting::getLogoUrl() }}" alt="{{ \App\Models\Setting::getSiteName() }}" class="h-12 w-auto object-contain" />
                <div>
                    <h1 class="text-xl font-black text-slate-900 uppercase tracking-tight">{{ \App\Models\Setting::getSiteName() }}</h1>
                    <p class="text-xs text-slate-600 font-semibold tracking-wider uppercase">Executive Business Operations &amp; Performance Report</p>
                </div>
            </div>
            <div class="text-right text-xs text-slate-600">
                <p><span class="font-bold text-slate-900">Date Generated:</span> {{ now()->format('d M Y, H:i') }}</p>
                <p><span class="font-bold text-slate-900">Reporting Scope:</span> {{ auth()->user()->branches->isNotEmpty() ? auth()->user()->branches->pluck('name')->join(', ') : 'All Branches (Enterprise)' }}</p>
                <p><span class="font-bold text-slate-900">Active Window:</span> {{ $periodReport['label'] ?? 'Current Period' }}</p>
            </div>
        </div>
    </div>

    <!-- Integrated Executive Report & Period Filter Toolbar -->
    <div class="card p-3 sm:p-4 bg-white dark:bg-dark-900 border border-slate-200/80 dark:border-dark-800 print:hidden shadow-xs">
        <form method="GET" action="{{ route('dashboard') }}" class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-1.5 text-xs">
                <div class="flex items-center gap-1.5 mr-2 text-slate-500 dark:text-dark-400 font-bold uppercase tracking-wider text-[11px]">
                    <svg class="w-4 h-4 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>Period:</span>
                </div>
                @php $curP = request('period', 'this_month'); @endphp
                <a href="{{ route('dashboard', ['period' => 'today']) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $curP === 'today' && !request('from') ? 'bg-sky-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-dark-800 text-slate-700 dark:text-dark-200 hover:bg-slate-200 dark:hover:bg-dark-700' }}">
                   Today
                </a>
                <a href="{{ route('dashboard', ['period' => 'this_week']) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $curP === 'this_week' && !request('from') ? 'bg-sky-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-dark-800 text-slate-700 dark:text-dark-200 hover:bg-slate-200 dark:hover:bg-dark-700' }}">
                   This Week
                </a>
                <a href="{{ route('dashboard', ['period' => 'this_month']) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ ($curP === 'this_month' || !$curP) && !request('from') ? 'bg-sky-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-dark-800 text-slate-700 dark:text-dark-200 hover:bg-slate-200 dark:hover:bg-dark-700' }}">
                   This Month
                </a>
                <a href="{{ route('dashboard', ['period' => 'this_year']) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $curP === 'this_year' && !request('from') ? 'bg-sky-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-dark-800 text-slate-700 dark:text-dark-200 hover:bg-slate-200 dark:hover:bg-dark-700' }}">
                   This Year
                </a>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <div class="flex items-center gap-1.5 bg-slate-50 dark:bg-dark-800 p-1 rounded-lg border border-slate-200 dark:border-dark-700">
                    <input type="date" name="from" value="{{ request('from') }}" class="form-input text-xs py-1 px-2 border-0 bg-transparent text-slate-700 dark:text-dark-200 focus:ring-0" title="Start Date">
                    <span class="text-slate-400 dark:text-dark-400 text-xs">to</span>
                    <input type="date" name="to" value="{{ request('to') }}" class="form-input text-xs py-1 px-2 border-0 bg-transparent text-slate-700 dark:text-dark-200 focus:ring-0" title="End Date">
                    <button type="submit" class="btn-primary text-xs px-2.5 py-1 rounded-md shadow-xs">Apply</button>
                    @if(request('period') || request('from') || request('to'))
                        <a href="{{ route('dashboard') }}" class="text-xs text-rose-500 hover:underline px-1">Reset</a>
                    @endif
                </div>

                <div class="flex items-center gap-1.5">
                    <button type="button" onclick="window.print()" class="btn-secondary text-xs py-1.5 px-3 flex items-center gap-1.5 rounded-lg hover:border-sky-300 dark:hover:border-sky-500">
                        <svg class="w-3.5 h-3.5 text-slate-600 dark:text-dark-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        <span>Print Report</span>
                    </button>

                    @hasanyrole('Super Admin|Manager|Auditor')
                    <a href="{{ route('reports.index') }}" class="btn-secondary text-xs py-1.5 px-3 flex items-center gap-1.5 rounded-lg text-sky-600 dark:text-sky-400 hover:bg-sky-50 dark:hover:bg-dark-800" title="Consolidated Business Reports Suite">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <span>Report Suite</span>
                    </a>
                    @endhasanyrole
                </div>
            </div>
        </form>
    </div>


    <!-- Core KPI Summary Cards Row (6 Cards — Professional Executive Design) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

        <!-- 1. Revenue (Selected Period) -->
        <div class="relative overflow-hidden bg-gradient-to-br from-white to-slate-50/70 dark:from-dark-900 dark:to-dark-800/90 rounded-2xl border border-slate-200/80 dark:border-dark-800 p-5 shadow-xs hover:-translate-y-1 hover:shadow-lg transition-all duration-300 ease-out group">
            <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-emerald-500 via-teal-400 to-cyan-400"></div>
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-500 dark:text-dark-400 uppercase tracking-wider">Revenue (Selected Period)</span>
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500/10 to-teal-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center border border-emerald-500/20 group-hover:scale-105 transition-transform duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight mb-2">
                Br {{ number_format($stats['selected_revenue'] ?? $stats['month_sales'], 2) }}
            </div>
            <div class="flex items-center justify-between text-xs pt-1">
                @php $trend = $stats['revenue_trend'] ?? 0; @endphp
                @if($trend >= 0)
                    <span class="inline-flex items-center gap-1 font-semibold text-emerald-700 bg-emerald-50 dark:bg-emerald-950/70 dark:text-emerald-400 px-2.5 py-0.5 rounded-full border border-emerald-200/60 dark:border-emerald-800/40">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                        +{{ $trend }}% vs previous period
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 font-semibold text-rose-700 bg-rose-50 dark:bg-rose-950/70 dark:text-rose-400 px-2.5 py-0.5 rounded-full border border-rose-200/60 dark:border-rose-800/40">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                        {{ abs($trend) }}% vs previous period
                    </span>
                @endif
                <span class="text-slate-400 dark:text-dark-400 font-medium">Month: Br {{ number_format($stats['month_sales'], 0) }}</span>
            </div>
            <div class="h-1.5 w-full bg-slate-100 dark:bg-dark-800 rounded-full overflow-hidden mt-3">
                <div class="h-full bg-gradient-to-r from-emerald-500 to-teal-400 rounded-full" style="width: {{ min(100, max(15, (int)($trend >= 0 ? 50 + $trend : 50 + $trend))) }}%;"></div>
            </div>
        </div>

        <!-- 2. Orders Today -->
        <div class="relative overflow-hidden bg-gradient-to-br from-white to-slate-50/70 dark:from-dark-900 dark:to-dark-800/90 rounded-2xl border border-slate-200/80 dark:border-dark-800 p-5 shadow-xs hover:-translate-y-1 hover:shadow-lg transition-all duration-300 ease-out group">
            <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-sky-500 via-blue-500 to-indigo-500"></div>
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-500 dark:text-dark-400 uppercase tracking-wider">Orders Today</span>
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-sky-500/10 to-blue-500/20 text-sky-600 dark:text-sky-400 flex items-center justify-center border border-sky-500/20 group-hover:scale-105 transition-transform duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
            <div class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight mb-2">
                {{ $stats['orders_today'] ?? $stats['today_sales_count'] }}
            </div>
            <div class="flex items-center justify-between text-xs pt-1">
                <span class="inline-flex items-center gap-1 text-amber-700 bg-amber-50 dark:bg-amber-950/70 dark:text-amber-400 px-2.5 py-0.5 rounded-full font-medium border border-amber-200/60 dark:border-amber-800/40">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ $stats['pending_sales_orders'] ?? 0 }} pending approval
                </span>
                <span class="text-slate-400 dark:text-dark-400 font-medium">Vol: Br {{ number_format($stats['today_sales'], 0) }}</span>
            </div>
            <div class="h-1.5 w-full bg-slate-100 dark:bg-dark-800 rounded-full overflow-hidden mt-3">
                <div class="h-full bg-gradient-to-r from-sky-500 to-blue-500 rounded-full" style="width: {{ min(100, max(20, ($stats['orders_today'] ?? 0) * 8)) }}%;"></div>
            </div>
        </div>

        <!-- 3. Pending Orders -->
        <div class="relative overflow-hidden bg-gradient-to-br from-white to-slate-50/70 dark:from-dark-900 dark:to-dark-800/90 rounded-2xl border border-slate-200/80 dark:border-dark-800 p-5 shadow-xs hover:-translate-y-1 hover:shadow-lg transition-all duration-300 ease-out group">
            <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-amber-500 to-orange-400"></div>
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-500 dark:text-dark-400 uppercase tracking-wider">Pending Orders</span>
                <div class="w-10 h-10 rounded-xl {{ ($stats['pending_sales_orders'] ?? 0) > 0 ? 'bg-gradient-to-br from-amber-500/10 to-orange-500/20 text-amber-500 border border-amber-500/20' : 'bg-slate-100 dark:bg-dark-800 text-slate-400' }} flex items-center justify-center group-hover:scale-105 transition-transform duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                </div>
            </div>
            <div class="text-3xl font-extrabold {{ ($stats['pending_sales_orders'] ?? 0) > 0 ? 'text-amber-500' : 'text-slate-900 dark:text-white' }} tracking-tight mb-2">
                {{ $stats['pending_sales_orders'] ?? 0 }}
            </div>
            <div class="flex items-center justify-between text-xs pt-1">
                <span class="text-slate-500 dark:text-dark-400">Awaiting processing across all time ≡</span>
                <a href="{{ route('sales.index', ['status' => 'pending']) }}" class="text-sky-600 dark:text-sky-400 font-semibold hover:underline">Review &rarr;</a>
            </div>
            <div class="h-1.5 w-full bg-slate-100 dark:bg-dark-800 rounded-full overflow-hidden mt-3">
                <div class="h-full bg-gradient-to-r from-amber-500 to-orange-400 rounded-full" style="width: {{ min(100, max(10, ($stats['pending_sales_orders'] ?? 0) * 15)) }}%;"></div>
            </div>
        </div>

        <!-- 4. Low Stock Alerts -->
        <div class="relative overflow-hidden bg-gradient-to-br from-white to-slate-50/70 dark:from-dark-900 dark:to-dark-800/90 rounded-2xl border border-slate-200/80 dark:border-dark-800 p-5 shadow-xs hover:-translate-y-1 hover:shadow-lg transition-all duration-300 ease-out group">
            <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-rose-500 to-red-400"></div>
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-500 dark:text-dark-400 uppercase tracking-wider">Low Stock Alerts</span>
                <div class="w-10 h-10 rounded-xl {{ $stats['low_stock'] > 0 ? 'bg-gradient-to-br from-rose-500/10 to-red-500/20 text-rose-500 border border-rose-500/20' : 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-500' }} flex items-center justify-center group-hover:scale-105 transition-transform duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
            <div class="text-3xl font-extrabold {{ $stats['low_stock'] > 0 ? 'text-rose-500' : 'text-slate-900 dark:text-white' }} tracking-tight mb-2">
                {{ $stats['low_stock'] }}
            </div>
            <div class="flex items-center justify-between text-xs pt-1">
                @if($stats['low_stock'] > 0)
                    <span class="inline-flex items-center gap-1 text-rose-700 bg-rose-50 dark:bg-rose-950/70 dark:text-rose-400 px-2 py-0.5 rounded-full font-medium border border-rose-200/60">
                        Action required · {{ $stats['low_stock_raw_materials'] }} raw materials
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 text-emerald-700 bg-emerald-50 dark:bg-emerald-950/70 dark:text-emerald-400 px-2 py-0.5 rounded-full font-medium">
                        All stock levels healthy ✔
                    </span>
                @endif
                <a href="{{ route('inventory.index') }}" class="text-sky-600 dark:text-sky-400 font-semibold hover:underline">Restock &rarr;</a>
            </div>
            <div class="h-1.5 w-full bg-slate-100 dark:bg-dark-800 rounded-full overflow-hidden mt-3">
                <div class="h-full bg-gradient-to-r from-rose-500 to-red-400 rounded-full" style="width: {{ min(100, max(5, ($stats['low_stock'] ?? 0) * 12)) }}%;"></div>
            </div>
        </div>

        <!-- 5. Active Products -->
        <div class="relative overflow-hidden bg-gradient-to-br from-white to-slate-50/70 dark:from-dark-900 dark:to-dark-800/90 rounded-2xl border border-slate-200/80 dark:border-dark-800 p-5 shadow-xs hover:-translate-y-1 hover:shadow-lg transition-all duration-300 ease-out group">
            <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-indigo-500 to-violet-500"></div>
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-500 dark:text-dark-400 uppercase tracking-wider">Active Products</span>
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500/10 to-violet-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center border border-indigo-500/20 group-hover:scale-105 transition-transform duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
            </div>
            <div class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight mb-2">
                {{ $stats['total_products'] }}
            </div>
            <div class="flex items-center justify-between text-xs pt-1">
                <span class="text-slate-500 dark:text-dark-400">Listed in storefront 📦</span>
                <a href="{{ route('products.index') }}" class="text-sky-600 dark:text-sky-400 font-semibold hover:underline">Catalog &rarr;</a>
            </div>
            <div class="h-1.5 w-full bg-slate-100 dark:bg-dark-800 rounded-full overflow-hidden mt-3">
                <div class="h-full bg-gradient-to-r from-indigo-500 to-violet-500 rounded-full" style="width: 85%;"></div>
            </div>
        </div>

        <!-- 6. Production Orders -->
        <div class="relative overflow-hidden bg-gradient-to-br from-white to-slate-50/70 dark:from-dark-900 dark:to-dark-800/90 rounded-2xl border border-slate-200/80 dark:border-dark-800 p-5 shadow-xs hover:-translate-y-1 hover:shadow-lg transition-all duration-300 ease-out group">
            <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-purple-500 to-pink-500"></div>
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-500 dark:text-dark-400 uppercase tracking-wider">Production Orders</span>
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500/10 to-pink-500/20 text-purple-600 dark:text-purple-400 flex items-center justify-center border border-purple-500/20 group-hover:scale-105 transition-transform duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
            </div>
            <div class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight mb-2">
                {{ $stats['active_production_orders'] }}
            </div>
            <div class="flex items-center justify-between text-xs pt-1">
                <span class="text-slate-500 dark:text-dark-400">{{ $stats['completed_production_period'] ?? 0 }} completed in selected period ⚙️</span>
                <a href="{{ route('manufacturing-orders.index') }}" class="text-purple-600 dark:text-purple-400 font-semibold hover:underline">Floor &rarr;</a>
            </div>
            <div class="h-1.5 w-full bg-slate-100 dark:bg-dark-800 rounded-full overflow-hidden mt-3">
                <div class="h-full bg-gradient-to-r from-purple-500 to-pink-500 rounded-full" style="width: {{ min(100, max(20, ($stats['active_production_orders'] ?? 0) * 18)) }}%;"></div>
            </div>
        </div>

    </div>

    <!-- Interactive Charts Row (Revenue Trend + Orders by Type) with Professional Pagination -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="chartPaginator()">

        <!-- Revenue Trend Line Chart with Interactive Timeframe Pagination (2 cols) -->
        <div class="card lg:col-span-2 bg-white dark:bg-dark-900 border border-slate-200/80 dark:border-dark-800 p-5 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 mb-4 border-b border-slate-100 dark:border-dark-800">
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">Revenue Trend (14 Days)</h2>
                        <span class="text-[10px] uppercase font-extrabold tracking-wider px-2 py-0.5 rounded-full bg-sky-100 dark:bg-sky-950/70 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-800">
                            Dynamic Analytics
                        </span>
                    </div>
                </div>

                <!-- Professional Chart Pagination & Mode Toggle Controls -->
                <div class="flex flex-wrap items-center gap-2">
                    <!-- Metric Mode Toggle -->
                    <div class="inline-flex p-0.5 bg-slate-100 dark:bg-dark-800 rounded-lg border border-slate-200/80 dark:border-dark-700 text-xs">
                        <button type="button" @click="setMetric('revenue')"
                                :class="metricMode === 'revenue' ? 'bg-white dark:bg-dark-900 text-sky-600 dark:text-sky-400 font-bold shadow-xs' : 'text-slate-600 dark:text-dark-300 hover:text-slate-900 font-medium'"
                                class="px-2.5 py-1 rounded-md transition-all">
                            Revenue (Br)
                        </button>
                        <button type="button" @click="setMetric('orders')"
                                :class="metricMode === 'orders' ? 'bg-white dark:bg-dark-900 text-sky-600 dark:text-sky-400 font-bold shadow-xs' : 'text-slate-600 dark:text-dark-300 hover:text-slate-900 font-medium'"
                                class="px-2.5 py-1 rounded-md transition-all">
                            Orders (#)
                        </button>
                    </div>

                    <!-- Chart Timeframe Pagination Buttons -->
                    <div class="inline-flex items-center gap-1 bg-slate-50 dark:bg-dark-800/80 p-1 rounded-lg border border-slate-200/80 dark:border-dark-700 text-xs">
                        <button type="button" @click="prevPage()" :disabled="currentPage === 0"
                                :class="currentPage === 0 ? 'opacity-40 cursor-not-allowed' : 'hover:bg-white dark:hover:bg-dark-700 text-slate-700 dark:text-dark-200'"
                                class="p-1 rounded transition" title="Previous Timeframe">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </button>

                        <span class="px-2 text-[11px] font-semibold text-slate-700 dark:text-dark-200 select-none whitespace-nowrap" x-text="pages[currentPage].name">
                            Recent 14 Days
                        </span>

                        <button type="button" @click="nextPage()" :disabled="currentPage === pages.length - 1"
                                :class="currentPage === pages.length - 1 ? 'opacity-40 cursor-not-allowed' : 'hover:bg-white dark:hover:bg-dark-700 text-slate-700 dark:text-dark-200'"
                                class="p-1 rounded transition" title="Next Timeframe">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Chart Sub-summary Bar -->
            <div class="flex items-center justify-between text-xs px-1 pb-3 text-slate-500 dark:text-dark-400">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full" :class="metricMode === 'revenue' ? 'bg-cyan-500' : 'bg-indigo-500'"></span>
                    <span class="font-medium">Active Window Total:</span>
                    <span class="font-bold text-slate-900 dark:text-white font-mono" x-text="metricMode === 'revenue' ? 'Br ' + Number(pages[currentPage].total_rev).toLocaleString('en-US', {minimumFractionDigits: 2}) : pages[currentPage].total_count + ' orders'"></span>
                </div>
                <div class="flex items-center gap-1.5 text-[11px]">
                    <span class="text-slate-400">Pagination:</span>
                    <template x-for="(pg, idx) in pages" :key="pg.id">
                        <button type="button" @click="goToPage(idx)"
                                :class="currentPage === idx ? 'bg-sky-600 text-white font-bold' : 'bg-slate-100 dark:bg-dark-800 text-slate-600 dark:text-dark-300 hover:bg-slate-200'"
                                class="w-5 h-5 rounded text-[10px] flex items-center justify-center transition"
                                x-text="idx + 1">
                        </button>
                    </template>
                </div>
            </div>

            <div class="relative w-full h-72">
                <canvas id="revenueTrendChart"></canvas>
            </div>
        </div>

        <!-- Orders by Type Doughnut Chart (1 col) -->
        <div class="card bg-white dark:bg-dark-900 border border-slate-200/80 dark:border-dark-800 p-5 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100 dark:border-dark-800">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">Orders by Type</h2>
                    </div>
                    <span class="text-xs font-semibold text-slate-600 dark:text-dark-300 bg-slate-100 dark:bg-dark-800 px-2.5 py-0.5 rounded-full border border-slate-200 dark:border-dark-700">Distribution</span>
                </div>
                <div class="relative w-full h-52 flex items-center justify-center">
                    <canvas id="ordersByTypeChart"></canvas>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-dark-800 grid grid-cols-3 divide-x divide-slate-100 dark:divide-dark-800 text-center">
                <div class="px-2">
                    <div class="text-[11px] text-slate-500 dark:text-dark-400 font-medium mb-0.5">Online</div>
                    <div class="text-base font-bold text-sky-600 dark:text-sky-400">{{ $ordersByTypeChart['data'][0] ?? 0 }}</div>
                </div>
                <div class="px-2">
                    <div class="text-[11px] text-slate-500 dark:text-dark-400 font-medium mb-0.5">POS</div>
                    <div class="text-base font-bold text-emerald-600 dark:text-emerald-400">{{ $ordersByTypeChart['data'][1] ?? 0 }}</div>
                </div>
                <div class="px-2">
                    <div class="text-[11px] text-slate-500 dark:text-dark-400 font-medium mb-0.5">Direct</div>
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
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wide">Important Metrics &amp; Marketing Reports</h3>
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
                    <span class="text-xs font-medium text-slate-700 dark:text-dark-200 group-hover:text-sky-600 dark:group-hover:text-sky-400 truncate">Sales CSV</span>
                </div>
                <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-sky-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </a>

            <a href="{{ route('reports.inventory.export') }}" class="p-2.5 rounded-xl border border-slate-200/80 dark:border-dark-700 bg-white dark:bg-dark-800 hover:border-emerald-300 dark:hover:border-emerald-500 hover:bg-emerald-50/50 dark:hover:bg-dark-700 transition flex items-center justify-between group">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 flex-shrink-0"></span>
                    <span class="text-xs font-medium text-slate-700 dark:text-dark-200 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 truncate">Stock CSV</span>
                </div>
                <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </a>

            <a href="{{ route('reports.production.export') }}" class="p-2.5 rounded-xl border border-slate-200/80 dark:border-dark-700 bg-white dark:bg-dark-800 hover:border-purple-300 dark:hover:border-purple-500 hover:bg-purple-50/50 dark:hover:bg-dark-700 transition flex items-center justify-between group">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-2 h-2 rounded-full bg-purple-500 flex-shrink-0"></span>
                    <span class="text-xs font-medium text-slate-700 dark:text-dark-200 group-hover:text-purple-600 dark:group-hover:text-purple-400 truncate">Mfg CSV</span>
                </div>
                <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-purple-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </a>

            <a href="{{ route('reports.branches.export') }}" class="p-2.5 rounded-xl border border-slate-200/80 dark:border-dark-700 bg-white dark:bg-dark-800 hover:border-blue-300 dark:hover:border-blue-500 hover:bg-blue-50/50 dark:hover:bg-dark-700 transition flex items-center justify-between group">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-2 h-2 rounded-full bg-blue-500 flex-shrink-0"></span>
                    <span class="text-xs font-medium text-slate-700 dark:text-dark-200 group-hover:text-blue-600 dark:group-hover:text-blue-400 truncate">Branches CSV</span>
                </div>
                <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </a>

            <a href="{{ route('reports.users.export') }}" class="p-2.5 rounded-xl border border-slate-200/80 dark:border-dark-700 bg-white dark:bg-dark-800 hover:border-amber-300 dark:hover:border-amber-500 hover:bg-amber-50/50 dark:hover:bg-dark-700 transition flex items-center justify-between group">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-2 h-2 rounded-full bg-amber-500 flex-shrink-0"></span>
                    <span class="text-xs font-medium text-slate-700 dark:text-dark-200 group-hover:text-amber-600 dark:group-hover:text-amber-400 truncate">Staff Perf CSV</span>
                </div>
                <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </a>

            <a href="{{ route('reports.activity.export') }}" class="p-2.5 rounded-xl border border-slate-200/80 dark:border-dark-700 bg-white dark:bg-dark-800 hover:border-rose-300 dark:hover:border-rose-500 hover:bg-rose-50/50 dark:hover:bg-dark-700 transition flex items-center justify-between group">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-2 h-2 rounded-full bg-rose-500 flex-shrink-0"></span>
                    <span class="text-xs font-medium text-slate-700 dark:text-dark-200 group-hover:text-rose-600 dark:group-hover:text-rose-400 truncate">Audit Trail CSV</span>
                </div>
                <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </a>
        </div>
    </div>

    <!-- Staff & User Performance Table with Professional Search & Pagination -->
    @hasanyrole('Super Admin|Manager|Admin')
    <div class="card p-0 overflow-hidden bg-white dark:bg-dark-900 border border-slate-200/80 dark:border-dark-800 shadow-xs"
         x-data="{
             userSearch: '',
             page: 1,
             perPage: 5,
             items: {{ json_encode($userPerformances) }},
             get filtered() {
                 if (!this.userSearch.trim()) return this.items;
                 const q = this.userSearch.toLowerCase();
                 return this.items.filter(u => 
                     (u.user?.name || '').toLowerCase().includes(q) ||
                     (u.user?.email || '').toLowerCase().includes(q) ||
                     (u.role || '').toLowerCase().includes(q) ||
                     (u.branches || '').toLowerCase().includes(q)
                 );
             },
             get total() { return this.filtered.length; },
             get maxPage() { return Math.max(1, Math.ceil(this.total / this.perPage)); },
             get pagedItems() {
                 const start = (this.page - 1) * this.perPage;
                 return this.filtered.slice(start, start + this.perPage);
             }
         }">
        <div class="p-5 border-b border-slate-100 dark:border-dark-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-sky-500"></span>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Staff &amp; Team Operational Performance</h2>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-slate-100 dark:bg-dark-800 text-slate-700 dark:text-dark-200" x-text="total + ' Members'"></span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="relative w-48 sm:w-64">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input type="text" x-model="userSearch" @input="page = 1" placeholder="Search staff by name, role, branch..."
                           class="form-input text-xs pl-8 pr-3 py-1.5 w-full bg-slate-50 dark:bg-dark-800 border-slate-200 dark:border-dark-700 rounded-lg">
                </div>
                <a href="{{ route('reports.users.export') }}" class="btn-secondary text-xs py-1.5 px-3 flex items-center gap-1.5 rounded-lg shadow-2xs shrink-0" title="Export Staff Performance CSV">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Export</span>
                </a>
            </div>
        </div>

        <div class="table-wrapper border-0 rounded-none shadow-none">
            <table class="table">
                <thead>
                    <tr>
                        <th>User / Team Member</th>
                        <th>Assigned Role</th>
                        <th>Authorized Branch</th>
                        <th>Sales Volume</th>
                        <th>Manufacturing Output</th>
                        <th>Activity Logs</th>
                        <th>Last Activity</th>
                        <th class="text-right">Performance Status</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(perf, idx) in pagedItems" :key="perf.user?.id || idx">
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-dark-800/40 transition">
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-sky-500 to-indigo-600 text-white font-bold flex items-center justify-center text-xs shadow-xs flex-shrink-0"
                                         x-text="(perf.user?.name || 'U').charAt(0).toUpperCase()">
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-semibold text-slate-900 dark:text-white text-sm truncate" x-text="perf.user?.name"></div>
                                        <div class="text-xs text-slate-500 dark:text-dark-400 truncate" x-text="perf.user?.email"></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge"
                                      :class="{
                                          'badge-purple': perf.role === 'Super Admin',
                                          'badge-blue': perf.role === 'Manager',
                                          'badge-green': perf.role === 'Seller',
                                          'badge-yellow': perf.role === 'Production Officer',
                                          'badge-gray': !['Super Admin','Manager','Seller','Production Officer'].includes(perf.role)
                                      }"
                                      x-text="perf.role">
                                </span>
                            </td>
                            <td>
                                <div class="flex items-center gap-1.5 text-xs text-slate-700 dark:text-dark-300 font-medium">
                                    <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    <span class="truncate max-w-[130px]" x-text="perf.branches" :title="perf.branches"></span>
                                </div>
                            </td>
                            <td>
                                <div class="font-bold text-slate-900 dark:text-white text-xs font-mono">
                                    Br <span x-text="Number(perf.sales_volume || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                                </div>
                                <div class="text-[11px] text-slate-500 dark:text-dark-400">
                                    <span x-text="perf.sales_count"></span> sales orders
                                </div>
                            </td>
                            <td>
                                <div class="font-semibold text-slate-900 dark:text-white text-xs">
                                    <span x-text="Number(perf.mfg_output || 0).toLocaleString()"></span> units
                                </div>
                                <div class="text-[11px] text-slate-500 dark:text-dark-400">
                                    <span x-text="perf.mfg_count"></span> production batches
                                </div>
                            </td>
                            <td>
                                <span class="font-mono text-xs font-semibold text-slate-700 dark:text-dark-200 bg-slate-100 dark:bg-dark-800 px-2 py-0.5 rounded border border-slate-200/60 dark:border-dark-700"
                                      x-text="perf.activities + ' events'">
                                </span>
                            </td>
                            <td>
                                <span class="text-xs text-slate-500 dark:text-dark-400" x-text="perf.last_action ? new Date(perf.last_action).toLocaleDateString(undefined, {month:'short', day:'numeric', hour:'2-digit', minute:'2-digit'}) : 'No recent logs'"></span>
                            </td>
                            <td class="text-right">
                                <span class="badge"
                                      :class="{
                                          'badge-green': perf.status === 'Top Performer',
                                          'badge-blue': perf.status === 'Active',
                                          'badge-gray': perf.status !== 'Top Performer' && perf.status !== 'Active'
                                      }">
                                    <span x-text="perf.status"></span>
                                </span>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="filtered.length === 0">
                        <td colspan="8" class="text-center py-8 text-slate-400 dark:text-dark-500">
                            No team members match the search query.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Table Pagination Footer -->
        <div class="p-3.5 border-t border-slate-100 dark:border-dark-800 bg-slate-50/50 dark:bg-dark-800/40 flex items-center justify-between text-xs text-slate-500 dark:text-dark-400">
            <div>
                Showing <span class="font-semibold text-slate-800 dark:text-dark-200" x-text="total === 0 ? 0 : ((page - 1) * perPage) + 1"></span>
                to <span class="font-semibold text-slate-800 dark:text-dark-200" x-text="Math.min(total, page * perPage)"></span>
                of <span class="font-semibold text-slate-800 dark:text-dark-200" x-text="total"></span> staff members
            </div>
            <div class="flex items-center gap-1.5" x-show="total > perPage">
                <button type="button" @click="page = Math.max(1, page - 1)" :disabled="page === 1"
                        :class="page === 1 ? 'opacity-40 cursor-not-allowed' : 'hover:bg-white dark:hover:bg-dark-700 text-slate-700 dark:text-dark-200 shadow-2xs'"
                        class="px-2.5 py-1 rounded-md border border-slate-200 dark:border-dark-700 transition">
                    &larr; Prev
                </button>
                <span class="px-2 font-semibold text-slate-700 dark:text-dark-200">
                    Page <span x-text="page"></span> of <span x-text="maxPage"></span>
                </span>
                <button type="button" @click="page = Math.min(maxPage, page + 1)" :disabled="page >= maxPage"
                        :class="page >= maxPage ? 'opacity-40 cursor-not-allowed' : 'hover:bg-white dark:hover:bg-dark-700 text-slate-700 dark:text-dark-200 shadow-2xs'"
                        class="px-2.5 py-1 rounded-md border border-slate-200 dark:border-dark-700 transition">
                    Next &rarr;
                </button>
            </div>
        </div>
    </div>
    @endhasanyrole

    <!-- Professional Product Catalog & Stock Overview Table -->
    <div class="card p-0 overflow-hidden bg-white dark:bg-dark-900 border border-slate-200/80 dark:border-dark-800 shadow-xs"
         x-data="{
             prodSearch: '',
             prodType: 'all',
             page: 1,
             perPage: 5,
             items: {{ json_encode($productsList->map(fn($p) => [
                 'id' => $p->id,
                 'name' => $p->name,
                 'sku' => $p->sku,
                 'type' => $p->type,
                 'category' => $p->category?->name ?? 'General',
                 'unit' => $p->unit?->code ?? $p->unit?->name ?? 'units',
                 'selling_price' => (float)$p->selling_price,
                 'purchase_cost' => (float)$p->purchase_cost,
                 'quantity' => (float)$p->quantity,
                 'min_stock' => (float)$p->minimum_stock_level,
                 'branches' => $p->branches->pluck('name')->join(', ') ?: 'Primary Branch',
                 'is_low' => $p->minimum_stock_level > 0 && (float)$p->quantity <= (float)$p->minimum_stock_level,
                 'is_out' => (float)$p->quantity <= 0,
             ])) }},
             get filtered() {
                 let list = this.items;
                 if (this.prodType !== 'all') {
                     list = list.filter(p => p.type === this.prodType);
                 }
                 if (!this.prodSearch.trim()) return list;
                 const q = this.prodSearch.toLowerCase();
                 return list.filter(p => 
                     p.name.toLowerCase().includes(q) ||
                     (p.sku || '').toLowerCase().includes(q) ||
                     p.category.toLowerCase().includes(q) ||
                     p.branches.toLowerCase().includes(q)
                 );
             },
             get total() { return this.filtered.length; },
             get maxPage() { return Math.max(1, Math.ceil(this.total / this.perPage)); },
             get pagedItems() {
                 const start = (this.page - 1) * this.perPage;
                 return this.filtered.slice(start, start + this.perPage);
             }
         }">
        <div class="p-5 border-b border-slate-100 dark:border-dark-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Product Catalog &amp; Real-Time Inventory</h2>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-slate-100 dark:bg-dark-800 text-slate-700 dark:text-dark-200" x-text="total + ' Products'"></span>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2.5">
                <!-- Type Filter Pills -->
                <div class="inline-flex rounded-lg p-0.5 bg-slate-100 dark:bg-dark-800 border border-slate-200 dark:border-dark-700 text-xs">
                    <button type="button" @click="prodType = 'all'; page = 1"
                            :class="prodType === 'all' ? 'bg-white dark:bg-dark-700 text-slate-900 dark:text-white shadow-xs font-bold' : 'text-slate-600 dark:text-dark-400 font-medium'"
                            class="px-2.5 py-1 rounded-md transition">All</button>
                    <button type="button" @click="prodType = 'product'; page = 1"
                            :class="prodType === 'product' ? 'bg-white dark:bg-dark-700 text-slate-900 dark:text-white shadow-xs font-bold' : 'text-slate-600 dark:text-dark-400 font-medium'"
                            class="px-2.5 py-1 rounded-md transition">Finished Goods</button>
                    <button type="button" @click="prodType = 'raw_material'; page = 1"
                            :class="prodType === 'raw_material' ? 'bg-white dark:bg-dark-700 text-slate-900 dark:text-white shadow-xs font-bold' : 'text-slate-600 dark:text-dark-400 font-medium'"
                            class="px-2.5 py-1 rounded-md transition">Raw Materials</button>
                </div>

                <!-- Search Input -->
                <div class="relative w-44 sm:w-56">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input type="text" x-model="prodSearch" @input="page = 1" placeholder="Search SKU or name..."
                           class="form-input text-xs pl-8 pr-3 py-1.5 w-full bg-slate-50 dark:bg-dark-800 border-slate-200 dark:border-dark-700 rounded-lg">
                </div>

                @hasanyrole('Super Admin|Manager|Admin')
                <a href="{{ route('filament.admin.resources.products.create') }}" class="btn-primary text-xs py-1.5 px-3 flex items-center gap-1.5 rounded-lg shadow-xs shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>New Product</span>
                </a>
                @endhasanyrole
            </div>
        </div>

        <div class="table-wrapper border-0 rounded-none shadow-none">
            <table class="table">
                <thead>
                    <tr>
                        <th>Product / Catalog Item</th>
                        <th>Classification</th>
                        <th>Registered Unit</th>
                        <th>Pricing (Selling / Cost)</th>
                        <th>Stock on Hand</th>
                        <th>Branch Allocation</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(prod, idx) in pagedItems" :key="prod.id || idx">
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-dark-800/40 transition">
                            <td>
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-dark-800 border border-slate-200 dark:border-dark-700 flex items-center justify-center text-slate-700 dark:text-dark-300 font-bold text-xs shrink-0">
                                        4
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-slate-900 dark:text-white text-xs truncate max-w-[200px]" x-text="prod.name"></div>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="font-mono text-[11px] text-slate-500 dark:text-dark-400 font-semibold" x-text="prod.sku || 'NO-SKU'"></span>
                                            <span class="text-[10px] text-slate-400 dark:text-dark-500">&bull;</span>
                                            <span class="text-[11px] text-slate-500 dark:text-dark-400 truncate max-w-[100px]" x-text="prod.category"></span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge"
                                      :class="prod.type === 'raw_material' ? 'badge-yellow' : 'badge-blue'"
                                      x-text="prod.type === 'raw_material' ? 'Raw Material' : 'Finished Good'">
                                </span>
                            </td>
                            <td>
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-md bg-slate-100 dark:bg-dark-800 text-slate-700 dark:text-dark-300 font-mono" x-text="prod.unit"></span>
                            </td>
                            <td>
                                <div class="font-bold text-slate-900 dark:text-white text-xs font-mono">
                                    Br <span x-text="Number(prod.selling_price || 0).toFixed(2)"></span>
                                </div>
                                <div class="text-[11px] text-slate-400 font-mono">
                                    Cost: Br <span x-text="Number(prod.purchase_cost || 0).toFixed(2)"></span>
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <span class="font-extrabold text-xs font-mono"
                                          :class="prod.is_out ? 'text-rose-600 dark:text-rose-400' : (prod.is_low ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400')"
                                          x-text="Number(prod.quantity).toFixed(2)">
                                    </span>
                                    <span class="text-[11px] text-slate-500 dark:text-dark-400" x-text="prod.unit"></span>
                                    <span class="badge text-[10px] py-0 px-1.5"
                                          :class="prod.is_out ? 'badge-red' : (prod.is_low ? 'badge-yellow' : 'badge-green')"
                                          x-text="prod.is_out ? 'Out of Stock' : (prod.is_low ? 'Low Stock' : 'Healthy')">
                                    </span>
                                </div>
                                <div class="text-[10px] text-slate-400 mt-0.5">
                                    Safety Min: <span x-text="Number(prod.min_stock).toFixed(2)"></span> <span x-text="prod.unit"></span>
                                </div>
                            </td>
                            <td>
                                <span class="text-xs text-slate-600 dark:text-dark-300 font-medium truncate max-w-[140px] block" x-text="prod.branches" :title="prod.branches"></span>
                            </td>
                            <td class="text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a :href="'/sales/create'" class="btn-primary text-[11px] py-1 px-2.5 rounded-lg shadow-2xs" title="Make quick sale">
                                        POS
                                    </a>
                                    <a :href="'/admin/products/' + prod.id + '/edit'" class="btn-secondary text-[11px] py-1 px-2.5 rounded-lg" title="Edit in admin">
                                        Edit
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="filtered.length === 0">
                        <td colspan="7" class="text-center py-8 text-slate-400 dark:text-dark-500">
                            No products match your search or filter.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Table Pagination Footer -->
        <div class="p-3.5 border-t border-slate-100 dark:border-dark-800 bg-slate-50/50 dark:bg-dark-800/40 flex items-center justify-between text-xs text-slate-500 dark:text-dark-400">
            <div>
                Showing <span class="font-semibold text-slate-800 dark:text-dark-200" x-text="total === 0 ? 0 : ((page - 1) * perPage) + 1"></span>
                to <span class="font-semibold text-slate-800 dark:text-dark-200" x-text="Math.min(total, page * perPage)"></span>
                of <span class="font-semibold text-slate-800 dark:text-dark-200" x-text="total"></span> products
            </div>
            <div class="flex items-center gap-1.5" x-show="total > perPage">
                <button type="button" @click="page = Math.max(1, page - 1)" :disabled="page === 1"
                        :class="page === 1 ? 'opacity-40 cursor-not-allowed' : 'hover:bg-white dark:hover:bg-dark-700 text-slate-700 dark:text-dark-200 shadow-2xs'"
                        class="px-2.5 py-1 rounded-md border border-slate-200 dark:border-dark-700 transition">
                    &larr; Prev
                </button>
                <span class="px-2 font-semibold text-slate-700 dark:text-dark-200">
                    Page <span x-text="page"></span> of <span x-text="maxPage"></span>
                </span>
                <button type="button" @click="page = Math.min(maxPage, page + 1)" :disabled="page >= maxPage"
                        :class="page >= maxPage ? 'opacity-40 cursor-not-allowed' : 'hover:bg-white dark:hover:bg-dark-700 text-slate-700 dark:text-dark-200 shadow-2xs'"
                        class="px-2.5 py-1 rounded-md border border-slate-200 dark:border-dark-700 transition">
                    Next &rarr;
                </button>
            </div>
        </div>
    </div>

    <!-- Live Transactions & Open Manufacturing Orders (2-Column Grid with Pagination) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Recent Sales Transactions with Pagination -->
        <div class="card p-0 overflow-hidden bg-white dark:bg-dark-900 border border-slate-200/80 dark:border-dark-800 shadow-xs flex flex-col justify-between"
             x-data="{
                 page: 1,
                 perPage: 5,
                 total: {{ count($recentSales) }},
                 get maxPage() { return Math.max(1, Math.ceil(this.total / this.perPage)); }
             }">
            <div>
                <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-dark-800 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">Recent Sales Transactions</h2>
                    </div>
                    <a href="{{ route('sales.index') }}" class="text-xs font-semibold text-sky-600 dark:text-sky-400 hover:text-sky-700 hover:underline">
                        View All Sales &rarr;
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
                            @forelse($recentSales as $index => $sale)
                            <tr x-show="{{ $index }} >= (page - 1) * perPage && {{ $index }} < page * perPage">
                                <td>
                                    <span class="font-mono text-xs font-bold text-sky-600 dark:text-sky-400">#{{ $sale->reference ?? $sale->id }}</span>
                                </td>
                                <td>
                                    <span class="text-xs font-medium text-slate-800 dark:text-dark-200">{{ $sale->user?->name ?? 'Guest Cashier' }}</span>
                                </td>
                                <td>
                                    <span class="text-xs text-slate-500 dark:text-dark-400">{{ $sale->branch?->name ?? 'Main Store' }}</span>
                                </td>
                                <td>
                                    <span class="font-bold text-slate-900 dark:text-white text-xs">Br {{ number_format($sale->total, 2) }}</span>
                                </td>
                                <td>
                                    <span class="text-xs text-slate-500 dark:text-dark-400">{{ $sale->created_at->format('M d, H:i') }}</span>
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('invoice.show', $sale) }}" class="text-xs font-semibold text-sky-600 dark:text-sky-400 hover:text-sky-700 hover:underline">
                                        Invoice
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-6 text-slate-400 dark:text-dark-500">
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

            <!-- Table Pagination Controls -->
            @if(count($recentSales) > 5)
            <div class="p-3 border-t border-slate-100 dark:border-dark-800 bg-slate-50/50 dark:bg-dark-800/40 flex items-center justify-between text-xs text-slate-500 dark:text-dark-400">
                <div>
                    Page <span class="font-semibold text-slate-800 dark:text-dark-200" x-text="page"></span> of <span class="font-semibold text-slate-800 dark:text-dark-200" x-text="maxPage"></span>
                </div>
                <div class="flex items-center gap-1">
                    <button type="button" @click="page = Math.max(1, page - 1)" :disabled="page === 1"
                            :class="page === 1 ? 'opacity-40 cursor-not-allowed' : 'hover:bg-white dark:hover:bg-dark-700 text-slate-700 dark:text-dark-200 shadow-2xs'"
                            class="px-2 py-0.5 rounded border border-slate-200 dark:border-dark-700 transition">
                        &larr; Prev
                    </button>
                    <button type="button" @click="page = Math.min(maxPage, page + 1)" :disabled="page >= maxPage"
                            :class="page >= maxPage ? 'opacity-40 cursor-not-allowed' : 'hover:bg-white dark:hover:bg-dark-700 text-slate-700 dark:text-dark-200 shadow-2xs'"
                            class="px-2 py-0.5 rounded border border-slate-200 dark:border-dark-700 transition">
                        Next &rarr;
                    </button>
                </div>
            </div>
            @endif
        </div>

        <!-- Open Manufacturing Orders with Pagination -->
        <div class="card p-0 overflow-hidden bg-white dark:bg-dark-900 border border-slate-200/80 dark:border-dark-800 shadow-xs flex flex-col justify-between"
             x-data="{
                 page: 1,
                 perPage: 5,
                 total: {{ count($openOrders) }},
                 get maxPage() { return Math.max(1, Math.ceil(this.total / this.perPage)); }
             }">
            <div>
                <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-dark-800 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">Active Production Batches</h2>
                    </div>
                    <a href="{{ route('manufacturing-orders.index') }}" class="text-xs font-semibold text-purple-600 dark:text-purple-400 hover:text-purple-700 hover:underline">
                        View All Orders &rarr;
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
                            @forelse($openOrders as $index => $order)
                            <tr x-show="{{ $index }} >= (page - 1) * perPage && {{ $index }} < page * perPage">
                                <td>
                                    <span class="font-mono text-xs font-bold text-purple-600 dark:text-purple-400">#{{ $order->order_number ?? $order->id }}</span>
                                </td>
                                <td>
                                    <div class="font-medium text-slate-900 dark:text-white text-xs truncate max-w-[150px]">{{ $order->product?->name ?? 'Product' }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $order->storageLocation?->name ?? 'Floor' }}</div>
                                </td>
                                <td>
                                    <span class="text-xs font-semibold text-slate-800 dark:text-dark-200">{{ number_format($order->planned_quantity) }}</span>
                                    <span class="text-[11px] text-slate-400">{{ $order->product?->unit?->code ?? 'units' }}</span>
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
                                <td colspan="5" class="text-center py-6 text-slate-400 dark:text-dark-500">
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

            <!-- Table Pagination Controls -->
            @if(count($openOrders) > 5)
            <div class="p-3 border-t border-slate-100 dark:border-dark-800 bg-slate-50/50 dark:bg-dark-800/40 flex items-center justify-between text-xs text-slate-500 dark:text-dark-400">
                <div>
                    Page <span class="font-semibold text-slate-800 dark:text-dark-200" x-text="page"></span> of <span class="font-semibold text-slate-800 dark:text-dark-200" x-text="maxPage"></span>
                </div>
                <div class="flex items-center gap-1">
                    <button type="button" @click="page = Math.max(1, page - 1)" :disabled="page === 1"
                            :class="page === 1 ? 'opacity-40 cursor-not-allowed' : 'hover:bg-white dark:hover:bg-dark-700 text-slate-700 dark:text-dark-200 shadow-2xs'"
                            class="px-2 py-0.5 rounded border border-slate-200 dark:border-dark-700 transition">
                        &larr; Prev
                    </button>
                    <button type="button" @click="page = Math.min(maxPage, page + 1)" :disabled="page >= maxPage"
                            :class="page >= maxPage ? 'opacity-40 cursor-not-allowed' : 'hover:bg-white dark:hover:bg-dark-700 text-slate-700 dark:text-dark-200 shadow-2xs'"
                            class="px-2 py-0.5 rounded border border-slate-200 dark:border-dark-700 transition">
                        Next &rarr;
                    </button>
                </div>
            </div>
            @endif
        </div>

    </div>

    <!-- Low Stock Inventory Alerts & Live Audit Logs (2-Column Grid with Pagination) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Low Stock Items Warning with Pagination -->
        <div class="card p-0 overflow-hidden bg-white dark:bg-dark-900 border border-slate-200/80 dark:border-dark-800 shadow-xs flex flex-col justify-between"
             x-data="{
                 page: 1,
                 perPage: 5,
                 total: {{ count($lowStockItems) }},
                 get maxPage() { return Math.max(1, Math.ceil(this.total / this.perPage)); }
             }">
            <div>
                <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-dark-800 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">Low Stock Reorder Alerts</h2>
                    </div>
                    <a href="{{ route('inventory.index') }}" class="text-xs font-semibold text-sky-600 dark:text-sky-400 hover:text-sky-700 hover:underline">
                        Stock Balances &rarr;
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
                            @forelse($lowStockItems as $index => $item)
                            <tr x-show="{{ $index }} >= (page - 1) * perPage && {{ $index }} < page * perPage">
                                <td>
                                    <div class="font-semibold text-slate-900 dark:text-white text-xs">{{ $item->product?->name ?? 'Item' }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono">{{ $item->product?->sku ?? '' }}</div>
                                </td>
                                <td>
                                    <span class="text-xs text-slate-600 dark:text-dark-300">{{ $item->storageLocation?->name ?? 'Default' }}</span>
                                </td>
                                <td>
                                    <span class="font-bold text-rose-600 dark:text-rose-400 text-xs">{{ number_format($item->quantity, 2) }}</span>
                                    <span class="text-[11px] text-slate-400">{{ $item->product?->unit?->code ?? $item->product?->unit?->name ?? '' }}</span>
                                </td>
                                <td>
                                    <span class="text-xs text-slate-500 dark:text-dark-400">{{ number_format($item->product?->minimum_stock_level ?? 0, 2) }}</span>
                                    <span class="text-[11px] text-slate-400">{{ $item->product?->unit?->code ?? $item->product?->unit?->name ?? '' }}</span>
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('stock-receipts.create') }}" class="btn-secondary text-[11px] py-1 px-2.5">
                                        Restock
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-6 text-slate-400 dark:text-dark-500">
                                    <span class="text-emerald-600 dark:text-emerald-400 font-semibold">All inventory levels are healthy!</span>
                                    <p class="text-xs text-slate-400 mt-0.5">No products currently below minimum stock threshold.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Table Pagination Controls -->
            @if(count($lowStockItems) > 5)
            <div class="p-3 border-t border-slate-100 dark:border-dark-800 bg-slate-50/50 dark:bg-dark-800/40 flex items-center justify-between text-xs text-slate-500 dark:text-dark-400">
                <div>
                    Page <span class="font-semibold text-slate-800 dark:text-dark-200" x-text="page"></span> of <span class="font-semibold text-slate-800 dark:text-dark-200" x-text="maxPage"></span>
                </div>
                <div class="flex items-center gap-1">
                    <button type="button" @click="page = Math.max(1, page - 1)" :disabled="page === 1"
                            :class="page === 1 ? 'opacity-40 cursor-not-allowed' : 'hover:bg-white dark:hover:bg-dark-700 text-slate-700 dark:text-dark-200 shadow-2xs'"
                            class="px-2 py-0.5 rounded border border-slate-200 dark:border-dark-700 transition">
                        &larr; Prev
                    </button>
                    <button type="button" @click="page = Math.min(maxPage, page + 1)" :disabled="page >= maxPage"
                            :class="page >= maxPage ? 'opacity-40 cursor-not-allowed' : 'hover:bg-white dark:hover:bg-dark-700 text-slate-700 dark:text-dark-200 shadow-2xs'"
                            class="px-2 py-0.5 rounded border border-slate-200 dark:border-dark-700 transition">
                        Next &rarr;
                    </button>
                </div>
            </div>
            @endif
        </div>

        <!-- Recent Audit Activities Log Feed with Pagination -->
        <div class="card p-0 overflow-hidden bg-white dark:bg-dark-900 border border-slate-200/80 dark:border-dark-800 shadow-xs flex flex-col justify-between"
             x-data="{
                 page: 1,
                 perPage: 5,
                 total: {{ count($recentActivities) }},
                 get maxPage() { return Math.max(1, Math.ceil(this.total / this.perPage)); }
             }">
            <div>
                <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-dark-800 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">Recent System Activities &amp; Audit</h2>
                    </div>
                    @hasanyrole('Super Admin|Auditor')
                    <a href="{{ route('reports.activity') }}" class="text-xs font-semibold text-sky-600 dark:text-sky-400 hover:text-sky-700 hover:underline">
                        Full Audit Trail &rarr;
                    </a>
                    @endhasanyrole
                </div>

                <div class="divide-y divide-slate-100 dark:divide-dark-800">
                    @forelse($recentActivities as $index => $act)
                    <div x-show="{{ $index }} >= (page - 1) * perPage && {{ $index }} < page * perPage"
                         class="p-3.5 sm:px-5 flex items-center justify-between gap-3 hover:bg-slate-50 dark:hover:bg-dark-800 transition">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-dark-800 text-slate-700 dark:text-dark-300 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                {{ strtoupper(substr($act->causer?->name ?? 'S', 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-semibold text-slate-800 dark:text-dark-200 truncate">
                                    {{ $act->causer?->name ?? 'System Event' }}
                                    <span class="font-normal text-slate-500 dark:text-dark-400">— {{ $act->description }}</span>
                                </p>
                                <p class="text-[11px] text-slate-400 truncate">
                                    Log: {{ $act->log_name ?? 'default' }}
                                    @if($act->subject_type)
                                        · {{ class_basename($act->subject_type) }} #{{ $act->subject_id }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        <span class="text-[11px] text-slate-400 flex-shrink-0 whitespace-nowrap">
                            {{ $act->created_at->diffForHumans() }}
                        </span>
                    </div>
                    @empty
                    <div class="text-center py-8 text-slate-400 dark:text-dark-500">
                        <p class="text-sm">No activity logs recorded yet today.</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Table Pagination Controls -->
            @if(count($recentActivities) > 5)
            <div class="p-3 border-t border-slate-100 dark:border-dark-800 bg-slate-50/50 dark:bg-dark-800/40 flex items-center justify-between text-xs text-slate-500 dark:text-dark-400">
                <div>
                    Page <span class="font-semibold text-slate-800 dark:text-dark-200" x-text="page"></span> of <span class="font-semibold text-slate-800 dark:text-dark-200" x-text="maxPage"></span>
                </div>
                <div class="flex items-center gap-1">
                    <button type="button" @click="page = Math.max(1, page - 1)" :disabled="page === 1"
                            :class="page === 1 ? 'opacity-40 cursor-not-allowed' : 'hover:bg-white dark:hover:bg-dark-700 text-slate-700 dark:text-dark-200 shadow-2xs'"
                            class="px-2 py-0.5 rounded border border-slate-200 dark:border-dark-700 transition">
                        &larr; Prev
                    </button>
                    <button type="button" @click="page = Math.min(maxPage, page + 1)" :disabled="page >= maxPage"
                            :class="page >= maxPage ? 'opacity-40 cursor-not-allowed' : 'hover:bg-white dark:hover:bg-dark-700 text-slate-700 dark:text-dark-200 shadow-2xs'"
                            class="px-2 py-0.5 rounded border border-slate-200 dark:border-dark-700 transition">
                        Next &rarr;
                    </button>
                </div>
            </div>
            @endif
        </div>

    </div>

</div>

@push('scripts')
<script>
function isDarkMode() {
    return document.documentElement.classList.contains('dark');
}

function getTickColor() {
    return isDarkMode() ? '#94a3b8' : '#64748b';
}

function getGridColor() {
    return isDarkMode() ? '#1e293b' : '#f1f5f9';
}

// Chart Pagination Component Logic (Alpine.js)
let liveRevenueChart = null;

function chartPaginator() {
    return {
        currentPage: 0,
        metricMode: 'revenue', // 'revenue' or 'orders'
        pages: {!! json_encode($chartPagination['pages'] ?? []) !!},
        goToPage(index) {
            this.currentPage = index;
            this.updateChart();
        },
        prevPage() {
            if (this.currentPage > 0) {
                this.currentPage--;
                this.updateChart();
            }
        },
        nextPage() {
            if (this.currentPage < this.pages.length - 1) {
                this.currentPage++;
                this.updateChart();
            }
        },
        setMetric(mode) {
            this.metricMode = mode;
            this.updateChart();
        },
        updateChart() {
            if (!liveRevenueChart) return;
            const current = this.pages[this.currentPage];
            liveRevenueChart.data.labels = current.labels;
            
            if (this.metricMode === 'revenue') {
                liveRevenueChart.data.datasets[0].label = 'Revenue (Br)';
                liveRevenueChart.data.datasets[0].data = current.revenue;
                liveRevenueChart.data.datasets[0].borderColor = '#06b6d4';
                liveRevenueChart.data.datasets[0].pointBackgroundColor = '#06b6d4';
                liveRevenueChart.options.scales.y.ticks.callback = function(value) {
                    return 'Br ' + Number(value).toLocaleString();
                };
            } else {
                liveRevenueChart.data.datasets[0].label = 'Orders Count';
                liveRevenueChart.data.datasets[0].data = current.orders;
                liveRevenueChart.data.datasets[0].borderColor = '#6366f1';
                liveRevenueChart.data.datasets[0].pointBackgroundColor = '#6366f1';
                liveRevenueChart.options.scales.y.ticks.callback = function(value) {
                    return Number(value).toLocaleString() + ' orders';
                };
            }
            liveRevenueChart.update();
        }
    };
}

function buildCharts() {
    // 1. Revenue Trajectory Chart (Line / Area with dynamic dataset switching)
    const revCtx = document.getElementById('revenueTrendChart');
    if (revCtx) {
        const revLabels = @json($revenueChart['labels'] ?? []);
        const revData   = @json($revenueChart['data'] ?? []);

        liveRevenueChart = new Chart(revCtx, {
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
                        gradient.addColorStop(0, 'rgba(6, 182, 212, 0.24)');
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
                                if (context.dataset.label.includes('Revenue')) {
                                    return ' Revenue: Br ' + Number(context.raw).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                                } else {
                                    return ' Orders: ' + Number(context.raw).toLocaleString() + ' orders';
                                }
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
            liveRevenueChart.options.scales.x.ticks.color = getTickColor();
            liveRevenueChart.options.scales.y.ticks.color = getTickColor();
            liveRevenueChart.options.scales.y.grid.color = getGridColor();
            liveRevenueChart.options.plugins.tooltip.backgroundColor = isDarkMode() ? '#1e293b' : '#0f172a';
            liveRevenueChart.data.datasets[0].pointBorderColor = isDarkMode() ? '#0f172a' : '#ffffff';
            liveRevenueChart.update();
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
<style>
@media print {
    #sidebar, .sidebar, .topbar, .print\:hidden, header, footer, button:not(.print-include), form, .btn-primary, .btn-secondary, a[href*="export"] {
        display: none !important;
    }
    .page-content, main, .page-inner {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
    }
    body {
        background: #ffffff !important;
        color: #000000 !important;
    }
    .card {
        box-shadow: none !important;
        border: 1px solid #cbd5e1 !important;
        background: #ffffff !important;
        color: #000000 !important;
        break-inside: avoid;
        margin-bottom: 1rem !important;
    }
    .dark * {
        color: #000000 !important;
        background: transparent !important;
    }
}
</style>
@endpush
@endif
@endsection
