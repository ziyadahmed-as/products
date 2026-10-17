@extends('layouts.app')
@section('title', 'Consolidated Business Reports')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="page-header flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Consolidated Business Reports</h1>
            <p class="text-gray-500 dark:text-dark-400 text-sm mt-1">
                Executive enterprise overview of sales, branch performance, inventory asset valuation, and manufacturing operations.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="btn-secondary flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                <span class="text-gray-500 dark:text-dark-400 text-xs font-semibold uppercase tracking-wider mr-2">Period:</span>
                @php $curP = request('period'); @endphp
                <a href="{{ route('reports.index', ['period' => 'today']) }}"
                   class="px-3 py-1 rounded-lg text-xs font-medium transition {{ $curP === 'today' ? 'bg-primary-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-dark-800 text-gray-700 dark:text-dark-300 hover:bg-gray-200 dark:hover:bg-dark-700' }}">
                   Today
                </a>
                <a href="{{ route('reports.index', ['period' => 'this_week']) }}"
                   class="px-3 py-1 rounded-lg text-xs font-medium transition {{ $curP === 'this_week' ? 'bg-primary-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-dark-800 text-gray-700 dark:text-dark-300 hover:bg-gray-200 dark:hover:bg-dark-700' }}">
                   This Week
                </a>
                <a href="{{ route('reports.index', ['period' => 'this_month']) }}"
                   class="px-3 py-1 rounded-lg text-xs font-medium transition {{ $curP === 'this_month' ? 'bg-primary-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-dark-800 text-gray-700 dark:text-dark-300 hover:bg-gray-200 dark:hover:bg-dark-700' }}">
                   This Month
                </a>
                <a href="{{ route('reports.index', ['period' => 'this_year']) }}"
                   class="px-3 py-1 rounded-lg text-xs font-medium transition {{ $curP === 'this_year' ? 'bg-primary-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-dark-800 text-gray-700 dark:text-dark-300 hover:bg-gray-200 dark:hover:bg-dark-700' }}">
                   This Year
                </a>
            </div>

            <div class="flex items-center gap-3">
                <input type="date" name="from" value="{{ request('from') }}" class="form-input text-xs py-1.5" title="From Date">
                <span class="text-gray-400 dark:text-dark-500 text-xs">to</span>
                <input type="date" name="to" value="{{ request('to') }}" class="form-input text-xs py-1.5" title="To Date">
                <button type="submit" class="btn-primary text-xs px-3 py-1.5 shadow-sm">Apply</button>
                @if($curP || request('from') || request('to'))
                    <a href="{{ route('reports.index') }}" class="text-xs text-rose-500 hover:underline">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Executive KPI Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Revenue --}}
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-dark-400">Total Sales Revenue</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-100 dark:bg-emerald-950/80 border border-emerald-200 dark:border-emerald-800/50 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white mt-3">Br{{ number_format($totalRevenue, 2) }}</div>
            <div class="text-xs text-gray-500 dark:text-dark-400 mt-1">{{ number_format($totalSalesCount) }} total invoices recorded</div>
        </div>

        {{-- Cash Collected vs Outstanding --}}
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-dark-400">Collected vs Receivables</span>
                <div class="w-9 h-9 rounded-xl bg-cyan-100 dark:bg-cyan-950/80 border border-cyan-200 dark:border-cyan-800/50 flex items-center justify-center text-cyan-600 dark:text-cyan-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-3">Br{{ number_format($totalPaid, 2) }}</div>
            <div class="text-xs text-rose-600 dark:text-rose-400 mt-1">Outstanding: Br{{ number_format($totalUnpaid, 2) }}</div>
        </div>

        {{-- Inventory Assets Valuation --}}
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-dark-400">Inventory Asset Valuation</span>
                <div class="w-9 h-9 rounded-xl bg-indigo-100 dark:bg-indigo-950/80 border border-indigo-200 dark:border-indigo-800/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white mt-3">Br{{ number_format($totalInventoryValue, 2) }}</div>
            <div class="text-xs text-gray-500 dark:text-dark-400 mt-1">{{ number_format($totalStockUnits, 2) }} units ({{ $lowStockCount }} low stock)</div>
        </div>

        {{-- Manufacturing Operations --}}
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-dark-400">Manufacturing Output</span>
                <div class="w-9 h-9 rounded-xl bg-purple-100 dark:bg-purple-950/80 border border-purple-200 dark:border-purple-800/50 flex items-center justify-center text-purple-600 dark:text-purple-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white mt-3">{{ number_format($completedProductionQty, 2) }} <span class="text-sm font-normal text-gray-500 dark:text-dark-400">units</span></div>
            <div class="text-xs text-amber-600 dark:text-amber-400 mt-1">{{ $activeManufacturingCount }} active production orders</div>
        </div>
    </div>

    <!-- Quick Navigation Hub -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3 print:hidden">
        <a href="{{ route('reports.sales') }}" class="card p-3 hover:border-primary-500 hover:shadow-sm transition text-center group">
            <div class="text-primary-600 dark:text-primary-400 mb-1 group-hover:scale-110 transition">
                <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <div class="text-xs font-semibold text-gray-900 dark:text-white">Sales & Revenue</div>
        </a>

        <a href="{{ route('reports.inventory') }}" class="card p-3 hover:border-primary-500 hover:shadow-sm transition text-center group">
            <div class="text-indigo-600 dark:text-indigo-400 mb-1 group-hover:scale-110 transition">
                <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <div class="text-xs font-semibold text-gray-900 dark:text-white">Stock Balances</div>
        </a>

        <a href="{{ route('reports.production') }}" class="card p-3 hover:border-primary-500 hover:shadow-sm transition text-center group">
            <div class="text-purple-600 dark:text-purple-400 mb-1 group-hover:scale-110 transition">
                <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                </svg>
            </div>
            <div class="text-xs font-semibold text-gray-900 dark:text-white">Manufacturing</div>
        </a>

        <a href="{{ route('reports.branches') }}" class="card p-3 hover:border-primary-500 hover:shadow-sm transition text-center group">
            <div class="text-cyan-600 dark:text-cyan-400 mb-1 group-hover:scale-110 transition">
                <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div class="text-xs font-semibold text-gray-900 dark:text-white">Branch Rankings</div>
        </a>

        <a href="{{ route('reports.transfers') }}" class="card p-3 hover:border-primary-500 hover:shadow-sm transition text-center group">
            <div class="text-amber-600 dark:text-amber-400 mb-1 group-hover:scale-110 transition">
                <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
            </div>
            <div class="text-xs font-semibold text-gray-900 dark:text-white">Stock Transfers</div>
        </a>

        <a href="{{ route('reports.activity') }}" class="card p-3 hover:border-primary-500 hover:shadow-sm transition text-center group">
            <div class="text-rose-600 dark:text-rose-400 mb-1 group-hover:scale-110 transition">
                <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div class="text-xs font-semibold text-gray-900 dark:text-white">Audit Trails</div>
        </a>
    </div>

    <!-- Branch Performance Breakdown Table -->
    <div class="card p-0 overflow-hidden shadow-sm">
        <div class="p-5 border-b border-gray-200 dark:border-dark-800 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-gray-900 dark:text-white">Branch Operations & Financial Performance</h2>
                <p class="text-xs text-gray-500 dark:text-dark-400 mt-0.5">Direct comparative figures across authorized branch locations.</p>
            </div>
            <a href="{{ route('reports.branches') }}" class="text-xs text-primary-600 dark:text-primary-400 hover:underline font-medium">
                Detailed Branch Report →
            </a>
        </div>
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wider text-gray-500 dark:text-dark-400 bg-gray-50 dark:bg-dark-950/60 border-b border-gray-200 dark:border-dark-800">
                        <th class="py-3 px-4">Branch</th>
                        <th class="py-3 px-4">Invoices</th>
                        <th class="py-3 px-4">Sales Revenue</th>
                        <th class="py-3 px-4">Collected</th>
                        <th class="py-3 px-4">Outstanding</th>
                        <th class="py-3 px-4">Stock Units</th>
                        <th class="py-3 px-4">Stock Valuation</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-dark-800 text-sm">
                    @forelse($branchStats as $bStat)
                    <tr class="hover:bg-gray-50 dark:hover:bg-dark-800/40 transition">
                        <td class="py-3 px-4 font-semibold text-gray-900 dark:text-white whitespace-nowrap">
                            {{ $bStat['branch']->name }}
                            <span class="text-xs font-mono text-gray-500 dark:text-dark-400 ml-1">({{ $bStat['branch']->code }})</span>
                        </td>
                        <td class="py-3 px-4 font-mono text-xs text-gray-600 dark:text-dark-300">
                            {{ number_format($bStat['orders_count']) }}
                        </td>
                        <td class="py-3 px-4 font-bold text-gray-900 dark:text-white whitespace-nowrap">
                            Br{{ number_format($bStat['revenue'], 2) }}
                        </td>
                        <td class="py-3 px-4 text-emerald-600 dark:text-emerald-400 font-medium whitespace-nowrap">
                            Br{{ number_format($bStat['paid'], 2) }}
                        </td>
                        <td class="py-3 px-4 font-medium whitespace-nowrap {{ $bStat['outstanding'] > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-gray-500 dark:text-dark-400' }}">
                            Br{{ number_format($bStat['outstanding'], 2) }}
                        </td>
                        <td class="py-3 px-4 text-xs font-mono text-gray-600 dark:text-dark-300">
                            {{ number_format($bStat['stock_units'], 2) }}
                        </td>
                        <td class="py-3 px-4 font-mono text-xs text-indigo-600 dark:text-indigo-300 whitespace-nowrap">
                            Br{{ number_format($bStat['stock_value'], 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-8 text-gray-400 dark:text-dark-500">No branches registered or authorized.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="card p-0 overflow-hidden shadow-sm">
        <div class="p-5 border-b border-gray-200 dark:border-dark-800 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-gray-900 dark:text-white">Recent Transactions</h2>
                <p class="text-xs text-gray-500 dark:text-dark-400 mt-0.5">Most recent sales orders recorded in authorized scope.</p>
            </div>
            <a href="{{ route('reports.sales') }}" class="text-xs text-primary-600 dark:text-primary-400 hover:underline font-medium">
                View All Sales Reports →
            </a>
        </div>
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wider text-gray-500 dark:text-dark-400 bg-gray-50 dark:bg-dark-950/60 border-b border-gray-200 dark:border-dark-800">
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Invoice #</th>
                        <th class="py-3 px-4">Customer</th>
                        <th class="py-3 px-4">Branch</th>
                        <th class="py-3 px-4">Seller</th>
                        <th class="py-3 px-4">Total</th>
                        <th class="py-3 px-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-dark-800 text-sm">
                    @forelse($recentSales as $sale)
                    <tr class="hover:bg-gray-50 dark:hover:bg-dark-800/40 transition">
                        <td class="py-3 px-4 text-xs text-gray-500 dark:text-dark-400 whitespace-nowrap">
                            {{ $sale->created_at->format('d M Y, H:i') }}
                        </td>
                        <td class="py-3 px-4 text-xs font-mono text-primary-600 dark:text-primary-400 font-semibold whitespace-nowrap">
                            {{ $sale->reference }}
                        </td>
                        <td class="py-3 px-4 text-gray-900 dark:text-white font-medium">
                            {{ $sale->customer_name ?? 'Walk-in' }}
                        </td>
                        <td class="py-3 px-4 text-xs text-gray-600 dark:text-dark-300">
                            {{ $sale->branch?->name ?? '—' }}
                        </td>
                        <td class="py-3 px-4 text-xs text-gray-600 dark:text-dark-300">
                            {{ $sale->user?->name ?? 'System' }}
                        </td>
                        <td class="py-3 px-4 font-bold text-gray-900 dark:text-white whitespace-nowrap">
                            Br{{ number_format($sale->total, 2) }}
                        </td>
                        <td class="py-3 px-4">
                            @if($sale->payment_status === 'Paid')
                                <span class="badge-green text-xs">Paid</span>
                            @elseif($sale->payment_status === 'Partial')
                                <span class="badge-yellow text-xs">Partial</span>
                            @else
                                <span class="badge-red text-xs">Unpaid</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-8 text-gray-400 dark:text-dark-500">No recent sales recorded.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
