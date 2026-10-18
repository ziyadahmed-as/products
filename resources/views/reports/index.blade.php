@extends('layouts.app')
@section('title', 'Consolidated Business Reports')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="page-header flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-800/60">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </span>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Consolidated Business Reports</h1>
            </div>
            <p class="text-slate-500 dark:text-dark-400 text-sm mt-1 ml-10">
                Executive enterprise overview of sales, branch performance, inventory asset valuation, and manufacturing operations.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="btn-secondary flex items-center gap-2 shadow-xs">
                <svg class="w-4 h-4 text-slate-500 dark:text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Print Overview</span>
            </button>
        </div>
    </div>

    <!-- Quick Date Filter Toolbar -->
    <div class="card p-4 print:hidden">
        <form method="GET" action="{{ route('reports.index') }}" class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-2 text-sm">
                <span class="text-slate-500 dark:text-dark-400 text-xs font-semibold uppercase tracking-wider mr-2">Period:</span>
                @php $curP = request('period'); @endphp
                <a href="{{ route('reports.index', ['period' => 'today']) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-medium transition {{ $curP === 'today' ? 'bg-sky-600 text-white shadow-xs font-semibold' : 'bg-slate-100 dark:bg-dark-800 text-slate-700 dark:text-dark-300 hover:bg-slate-200 dark:hover:bg-dark-700' }}">
                   Today
                </a>
                <a href="{{ route('reports.index', ['period' => 'this_week']) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-medium transition {{ $curP === 'this_week' ? 'bg-sky-600 text-white shadow-xs font-semibold' : 'bg-slate-100 dark:bg-dark-800 text-slate-700 dark:text-dark-300 hover:bg-slate-200 dark:hover:bg-dark-700' }}">
                   This Week
                </a>
                <a href="{{ route('reports.index', ['period' => 'this_month']) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-medium transition {{ $curP === 'this_month' ? 'bg-sky-600 text-white shadow-xs font-semibold' : 'bg-slate-100 dark:bg-dark-800 text-slate-700 dark:text-dark-300 hover:bg-slate-200 dark:hover:bg-dark-700' }}">
                   This Month
                </a>
                <a href="{{ route('reports.index', ['period' => 'this_year']) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-medium transition {{ $curP === 'this_year' ? 'bg-sky-600 text-white shadow-xs font-semibold' : 'bg-slate-100 dark:bg-dark-800 text-slate-700 dark:text-dark-300 hover:bg-slate-200 dark:hover:bg-dark-700' }}">
                   This Year
                </a>
            </div>

            <div class="flex items-center gap-3">
                <input type="date" name="from" value="{{ request('from') }}" class="form-input text-xs py-1.5" title="From Date">
                <span class="text-slate-400 dark:text-dark-500 text-xs">to</span>
                <input type="date" name="to" value="{{ request('to') }}" class="form-input text-xs py-1.5" title="To Date">
                <button type="submit" class="btn-primary text-xs px-4 py-1.5 shadow-xs">Apply</button>
                @if($curP || request('from') || request('to'))
                    <a href="{{ route('reports.index') }}" class="text-xs text-rose-500 hover:underline">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Executive KPI Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Revenue --}}
        <div class="stat-card flex items-center gap-4">
            <div class="stat-icon bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60 flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <div class="stat-label">Total Sales Revenue</div>
                <div class="stat-value text-slate-900 dark:text-white font-mono">Br {{ number_format($totalRevenue, 2) }}</div>
                <div class="text-xs text-slate-400 dark:text-dark-500 mt-0.5">{{ number_format($totalSalesCount) }} total invoices recorded</div>
            </div>
        </div>

        {{-- Cash Collected vs Outstanding --}}
        <div class="stat-card flex items-center gap-4">
            <div class="stat-icon bg-cyan-50 dark:bg-cyan-950/60 text-cyan-600 dark:text-cyan-400 border border-cyan-200 dark:border-cyan-800/60 flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <div class="stat-label">Collected vs Receivables</div>
                <div class="stat-value text-emerald-600 dark:text-emerald-400 font-mono">Br {{ number_format($totalPaid, 2) }}</div>
                <div class="text-xs text-rose-600 dark:text-rose-400 mt-0.5 font-mono">Outstanding: Br {{ number_format($totalUnpaid, 2) }}</div>
            </div>
        </div>

        {{-- Inventory Assets Valuation --}}
        <div class="stat-card flex items-center gap-4">
            <div class="stat-icon bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/60 flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <div class="stat-label">Inventory Asset Valuation</div>
                <div class="stat-value text-slate-900 dark:text-white font-mono">Br {{ number_format($totalInventoryValue, 2) }}</div>
                <div class="text-xs text-slate-400 dark:text-dark-500 mt-0.5">{{ number_format($totalStockUnits, 2) }} units ({{ $lowStockCount }} low stock)</div>
            </div>
        </div>

        {{-- Manufacturing Operations --}}
        <div class="stat-card flex items-center gap-4">
            <div class="stat-icon bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 border border-purple-200 dark:border-purple-800/60 flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <div class="stat-label">Manufacturing Output</div>
                <div class="stat-value text-slate-900 dark:text-white font-mono">{{ number_format($completedProductionQty, 2) }} <span class="text-xs font-normal text-slate-400 dark:text-dark-400 font-sans">units</span></div>
                <div class="text-xs text-amber-600 dark:text-amber-400 mt-0.5">{{ $activeManufacturingCount }} active production orders</div>
            </div>
        </div>
    </div>

    <!-- Quick Navigation Hub -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3 print:hidden">
        <a href="{{ route('reports.sales') }}" class="card p-3.5 hover:border-sky-500 hover:shadow-xs transition text-center group">
            <div class="text-sky-600 dark:text-sky-400 mb-1.5 group-hover:scale-110 transition">
                <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <div class="text-xs font-semibold text-slate-900 dark:text-white">Sales & Revenue</div>
        </a>

        <a href="{{ route('reports.inventory') }}" class="card p-3.5 hover:border-indigo-500 hover:shadow-xs transition text-center group">
            <div class="text-indigo-600 dark:text-indigo-400 mb-1.5 group-hover:scale-110 transition">
                <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <div class="text-xs font-semibold text-slate-900 dark:text-white">Stock Balances</div>
        </a>

        <a href="{{ route('reports.production') }}" class="card p-3.5 hover:border-purple-500 hover:shadow-xs transition text-center group">
            <div class="text-purple-600 dark:text-purple-400 mb-1.5 group-hover:scale-110 transition">
                <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                </svg>
            </div>
            <div class="text-xs font-semibold text-slate-900 dark:text-white">Manufacturing</div>
        </a>

        <a href="{{ route('reports.branches') }}" class="card p-3.5 hover:border-cyan-500 hover:shadow-xs transition text-center group">
            <div class="text-cyan-600 dark:text-cyan-400 mb-1.5 group-hover:scale-110 transition">
                <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div class="text-xs font-semibold text-slate-900 dark:text-white">Branch Rankings</div>
        </a>

        <a href="{{ route('reports.transfers') }}" class="card p-3.5 hover:border-amber-500 hover:shadow-xs transition text-center group">
            <div class="text-amber-600 dark:text-amber-400 mb-1.5 group-hover:scale-110 transition">
                <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
            </div>
            <div class="text-xs font-semibold text-slate-900 dark:text-white">Stock Transfers</div>
        </a>

        <a href="{{ route('reports.activity') }}" class="card p-3.5 hover:border-rose-500 hover:shadow-xs transition text-center group">
            <div class="text-rose-600 dark:text-rose-400 mb-1.5 group-hover:scale-110 transition">
                <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div class="text-xs font-semibold text-slate-900 dark:text-white">Audit Trails</div>
        </a>
    </div>

    <!-- Branch Performance Breakdown Table -->
    <div class="card p-0 overflow-hidden shadow-xs">
        <div class="p-4 border-b border-slate-200 dark:border-dark-800 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white">Branch Operations & Financial Performance</h2>
                <p class="text-xs text-slate-500 dark:text-dark-400 mt-0.5">Direct comparative figures across authorized branch locations.</p>
            </div>
            <a href="{{ route('reports.branches') }}" class="text-xs text-sky-600 dark:text-sky-400 hover:underline font-medium">
                Detailed Branch Report →
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Branch</th>
                        <th>Invoices</th>
                        <th>Sales Revenue</th>
                        <th>Collected</th>
                        <th>Outstanding</th>
                        <th>Stock Units</th>
                        <th>Stock Valuation</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($branchStats as $bStat)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-dark-850/50 transition-colors">
                        <td class="font-semibold text-slate-900 dark:text-white whitespace-nowrap">
                            <span class="badge badge-purple mr-1">{{ $bStat['branch']->code }}</span>
                            {{ $bStat['branch']->name }}
                        </td>
                        <td class="font-mono text-xs text-slate-600 dark:text-dark-300">
                            {{ number_format($bStat['orders_count']) }}
                        </td>
                        <td class="font-mono text-xs font-bold text-slate-900 dark:text-white whitespace-nowrap">
                            Br {{ number_format($bStat['revenue'], 2) }}
                        </td>
                        <td class="font-mono text-xs text-emerald-600 dark:text-emerald-400 font-bold whitespace-nowrap">
                            Br {{ number_format($bStat['paid'], 2) }}
                        </td>
                        <td class="font-mono text-xs font-bold whitespace-nowrap {{ $bStat['outstanding'] > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-400 dark:text-dark-500' }}">
                            Br {{ number_format($bStat['outstanding'], 2) }}
                        </td>
                        <td class="text-xs font-mono text-slate-600 dark:text-dark-300">
                            {{ number_format($bStat['stock_units'], 2) }}
                        </td>
                        <td class="font-mono text-xs text-indigo-600 dark:text-indigo-400 font-bold whitespace-nowrap">
                            Br {{ number_format($bStat['stock_value'], 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-12 text-slate-400 dark:text-dark-500">No branches registered or authorized.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="card p-0 overflow-hidden shadow-xs">
        <div class="p-4 border-b border-slate-200 dark:border-dark-800 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white">Recent Transactions</h2>
                <p class="text-xs text-slate-500 dark:text-dark-400 mt-0.5">Most recent sales orders recorded in authorized scope.</p>
            </div>
            <a href="{{ route('reports.sales') }}" class="text-xs text-sky-600 dark:text-sky-400 hover:underline font-medium">
                View All Sales Reports →
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Invoice #</th>
                        <th>Customer</th>
                        <th>Branch</th>
                        <th>Seller</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentSales as $sale)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-dark-850/50 transition-colors">
                        <td class="font-mono text-xs text-slate-500 dark:text-dark-400 whitespace-nowrap">
                            {{ $sale->created_at->format('d M Y, H:i') }}
                        </td>
                        <td class="text-xs font-mono text-sky-600 dark:text-sky-400 font-bold whitespace-nowrap">
                            {{ $sale->reference }}
                        </td>
                        <td class="text-slate-900 dark:text-white font-medium text-xs">
                            {{ $sale->customer_name ?? 'Walk-in' }}
                        </td>
                        <td class="text-xs text-slate-600 dark:text-dark-300">
                            <span class="badge badge-purple">{{ $sale->branch?->name ?? '—' }}</span>
                        </td>
                        <td class="text-xs text-slate-600 dark:text-dark-300">
                            {{ $sale->user?->name ?? 'System' }}
                        </td>
                        <td class="font-mono text-xs font-bold text-slate-900 dark:text-white whitespace-nowrap">
                            Br {{ number_format($sale->total, 2) }}
                        </td>
                        <td>
                            @if($sale->payment_status === 'Paid')
                                <span class="badge badge-green text-xs">Paid</span>
                            @elseif($sale->payment_status === 'Partial')
                                <span class="badge badge-yellow text-xs">Partial</span>
                            @else
                                <span class="badge badge-red text-xs">Unpaid</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-12 text-slate-400 dark:text-dark-500">No recent sales recorded.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
