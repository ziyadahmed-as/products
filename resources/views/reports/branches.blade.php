@extends('layouts.app')
@section('title', 'Branch Performance Reports')

@section('content')
<div class="space-y-6">

    <!-- Header & Action Buttons -->
    <div class="page-header flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Branch Performance Reports</h1>
            <p class="text-gray-500 dark:text-dark-400 text-sm mt-1">
                Comparative analysis of sales volume, revenue collections, inventory valuations, and staffing by branch.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="btn-secondary flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Print</span>
            </button>
            <a href="{{ route('reports.branches.export', request()->query()) }}" class="btn-primary flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Export CSV</span>
            </a>
        </div>
    </div>

    <!-- Quick Date Filter Toolbar -->
    <div class="card p-4 print:hidden">
        <form method="GET" action="{{ route('reports.branches') }}" class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-2 text-sm">
                <span class="text-gray-500 dark:text-dark-400 text-xs font-semibold uppercase tracking-wider mr-2">Period:</span>
                @php $curP = request('period'); @endphp
                <a href="{{ route('reports.branches', ['period' => 'today']) }}"
                   class="px-3 py-1 rounded-lg text-xs font-medium transition {{ $curP === 'today' ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-dark-800 text-gray-700 dark:text-dark-300 hover:bg-gray-200 dark:hover:bg-dark-700' }}">
                   Today
                </a>
                <a href="{{ route('reports.branches', ['period' => 'this_week']) }}"
                   class="px-3 py-1 rounded-lg text-xs font-medium transition {{ $curP === 'this_week' ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-dark-800 text-gray-700 dark:text-dark-300 hover:bg-gray-200 dark:hover:bg-dark-700' }}">
                   This Week
                </a>
                <a href="{{ route('reports.branches', ['period' => 'this_month']) }}"
                   class="px-3 py-1 rounded-lg text-xs font-medium transition {{ $curP === 'this_month' ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-dark-800 text-gray-700 dark:text-dark-300 hover:bg-gray-200 dark:hover:bg-dark-700' }}">
                   This Month
                </a>
                <a href="{{ route('reports.branches', ['period' => 'this_year']) }}"
                   class="px-3 py-1 rounded-lg text-xs font-medium transition {{ $curP === 'this_year' ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-dark-800 text-gray-700 dark:text-dark-300 hover:bg-gray-200 dark:hover:bg-dark-700' }}">
                   This Year
                </a>
            </div>

            <div class="flex items-center gap-3">
                <input type="date" name="from" value="{{ request('from') }}" class="form-input text-xs py-1.5" title="From Date">
                <span class="text-gray-400 dark:text-dark-500 text-xs">to</span>
                <input type="date" name="to" value="{{ request('to') }}" class="form-input text-xs py-1.5" title="To Date">
                <button type="submit" class="btn-primary text-xs px-3 py-1.5">Apply</button>
                @if($curP || request('from') || request('to'))
                    <a href="{{ route('reports.branches') }}" class="text-xs text-rose-500 hover:underline">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card p-5">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-dark-400">Total Enterprise Sales</span>
            <div class="text-2xl font-bold text-gray-900 dark:text-white mt-2">Br{{ number_format($totalEnterpriseRevenue, 2) }}</div>
            <div class="text-xs text-gray-500 dark:text-dark-400 mt-1">{{ number_format($totalEnterpriseOrders) }} total invoices</div>
        </div>

        <div class="card p-5">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-dark-400">Collected Revenue</span>
            <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-2">Br{{ number_format($totalEnterprisePaid, 2) }}</div>
            <div class="text-xs text-gray-500 dark:text-dark-400 mt-1">Across all branches</div>
        </div>

        <div class="card p-5">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-dark-400">Total Outstanding</span>
            <div class="text-2xl font-bold text-rose-600 dark:text-rose-400 mt-2">Br{{ number_format($totalEnterpriseRevenue - $totalEnterprisePaid, 2) }}</div>
            <div class="text-xs text-gray-500 dark:text-dark-400 mt-1">Pending payments</div>
        </div>

        <div class="card p-5">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-dark-400">Branch Stock Valuation</span>
            <div class="text-2xl font-bold text-cyan-600 dark:text-cyan-400 mt-2">Br{{ number_format($totalEnterpriseValuation, 2) }}</div>
            <div class="text-xs text-gray-500 dark:text-dark-400 mt-1">Combined asset value</div>
        </div>
    </div>

    <!-- Branch Comparative Table -->
    <div class="card p-0 overflow-hidden shadow-sm">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wider text-gray-500 dark:text-dark-400 bg-gray-50/75 dark:bg-dark-950/60 border-b border-gray-200 dark:border-dark-800">
                        <th class="py-3 px-4">Branch</th>
                        <th class="py-3 px-4">Code</th>
                        <th class="py-3 px-4 text-right">Invoices</th>
                        <th class="py-3 px-4 text-right">Sales Revenue</th>
                        <th class="py-3 px-4 text-right">Collected</th>
                        <th class="py-3 px-4 text-right">Outstanding</th>
                        <th class="py-3 px-4 text-center">Collection %</th>
                        <th class="py-3 px-4 text-right">Stock Units</th>
                        <th class="py-3 px-4 text-right">Stock Valuation</th>
                        <th class="py-3 px-4 text-center">Staff</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-dark-800 text-sm">
                    @forelse($branchStats as $b)
                    @php
                        $collPct = $b['revenue'] > 0 ? round(($b['paid'] / $b['revenue']) * 100, 1) : 100.0;
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-dark-800/40 transition">
                        <td class="py-3 px-4 font-bold text-gray-900 dark:text-white whitespace-nowrap">
                            {{ $b['branch']->name }}
                        </td>
                        <td class="py-3 px-4 font-mono text-xs text-primary-600 dark:text-primary-400 whitespace-nowrap">
                            {{ $b['branch']->code }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono text-xs text-gray-600 dark:text-dark-300">
                            {{ number_format($b['orders_count']) }}
                        </td>
                        <td class="py-3 px-4 text-right font-bold text-gray-900 dark:text-white whitespace-nowrap">
                            Br{{ number_format($b['revenue'], 2) }}
                        </td>
                        <td class="py-3 px-4 text-right text-emerald-600 dark:text-emerald-400 font-medium whitespace-nowrap">
                            Br{{ number_format($b['paid'], 2) }}
                        </td>
                        <td class="py-3 px-4 text-right whitespace-nowrap {{ $b['outstanding'] > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-gray-400 dark:text-dark-400' }}">
                            Br{{ number_format($b['outstanding'], 2) }}
                        </td>
                        <td class="py-3 px-4 text-center whitespace-nowrap font-mono text-xs font-semibold">
                            <span class="{{ $collPct >= 90 ? 'text-emerald-600 dark:text-emerald-400' : ($collPct >= 70 ? 'text-amber-600 dark:text-amber-400' : 'text-rose-600 dark:text-rose-400') }}">
                                {{ number_format($collPct, 1) }}%
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right font-mono text-xs text-gray-600 dark:text-dark-300 whitespace-nowrap">
                            {{ number_format($b['stock_units'], 2) }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono text-xs text-indigo-600 dark:text-indigo-300 font-bold whitespace-nowrap">
                            Br{{ number_format($b['stock_value'], 2) }}
                        </td>
                        <td class="py-3 px-4 text-center text-xs text-gray-600 dark:text-dark-300 whitespace-nowrap">
                            {{ $b['staff_count'] }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-16 text-gray-500 dark:text-dark-500">
                            No branch records found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
