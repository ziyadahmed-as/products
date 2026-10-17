@extends('layouts.app')
@section('title', 'Manufacturing & Raw Material Reports')

@section('content')
<div class="space-y-6">

    <!-- Header & Action Buttons -->
    <div class="page-header flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Manufacturing & Raw Material Reports</h1>
            <p class="text-gray-500 dark:text-dark-400 text-sm mt-1">
                Production order history, finished goods yields, scrap/rejection rates, and raw material consumption analysis.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="btn-secondary flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Print</span>
            </button>
            <a href="{{ route('reports.production.export', request()->query()) }}" class="btn-primary flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Export CSV</span>
            </a>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="card p-5">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-dark-400">Total MOs</span>
            <div class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ number_format($totalOrders) }}</div>
            <div class="text-xs text-gray-500 dark:text-dark-400 mt-1">{{ number_format($completedOrdersCount) }} completed</div>
        </div>

        <div class="card p-5">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-dark-400">Good Yield Produced</span>
            <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-2">{{ number_format($totalGoodProduced, 2) }}</div>
            <div class="text-xs text-gray-500 dark:text-dark-400 mt-1">Planned: {{ number_format($totalPlannedQuantity, 2) }}</div>
        </div>

        <div class="card p-5">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-dark-400">Rejected / Scrap</span>
            <div class="text-2xl font-bold text-rose-600 dark:text-rose-400 mt-2">{{ number_format($totalRejected, 2) }}</div>
            <div class="text-xs text-gray-500 dark:text-dark-400 mt-1">Finished units rejected</div>
        </div>

        <div class="card p-5">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-dark-400">Material Waste</span>
            <div class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-2">{{ number_format($totalWastedMaterials, 2) }}</div>
            <div class="text-xs text-gray-500 dark:text-dark-400 mt-1">Raw material scrap</div>
        </div>

        <div class="card p-5">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-dark-400">Avg. Efficiency</span>
            <div class="text-2xl font-bold text-cyan-600 dark:text-cyan-400 mt-2">{{ number_format($avgEfficiency, 1) }}%</div>
            <div class="text-xs text-gray-500 dark:text-dark-400 mt-1">Target completion rate</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card p-5 print:hidden">
        <form method="GET" action="{{ route('reports.production') }}" class="space-y-4">
            
            {{-- Quick Presets --}}
            <div class="flex flex-wrap items-center gap-2 pb-3 border-b border-gray-100 dark:border-dark-800 text-sm">
                <span class="text-gray-500 dark:text-dark-400 text-xs font-semibold uppercase tracking-wider mr-2">Period:</span>
                @php $curP = request('period'); @endphp
                <a href="{{ route('reports.production', array_merge(request()->except(['period', 'from', 'to', 'page']), ['period' => 'today'])) }}"
                   class="px-3 py-1 rounded-lg text-xs font-medium transition {{ $curP === 'today' ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-dark-800 text-gray-700 dark:text-dark-300 hover:bg-gray-200 dark:hover:bg-dark-700' }}">
                   Today
                </a>
                <a href="{{ route('reports.production', array_merge(request()->except(['period', 'from', 'to', 'page']), ['period' => 'this_week'])) }}"
                   class="px-3 py-1 rounded-lg text-xs font-medium transition {{ $curP === 'this_week' ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-dark-800 text-gray-700 dark:text-dark-300 hover:bg-gray-200 dark:hover:bg-dark-700' }}">
                   This Week
                </a>
                <a href="{{ route('reports.production', array_merge(request()->except(['period', 'from', 'to', 'page']), ['period' => 'this_month'])) }}"
                   class="px-3 py-1 rounded-lg text-xs font-medium transition {{ $curP === 'this_month' ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-dark-800 text-gray-700 dark:text-dark-300 hover:bg-gray-200 dark:hover:bg-dark-700' }}">
                   This Month
                </a>
                <a href="{{ route('reports.production', array_merge(request()->except(['period', 'from', 'to', 'page']), ['period' => 'this_year'])) }}"
                   class="px-3 py-1 rounded-lg text-xs font-medium transition {{ $curP === 'this_year' ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-dark-800 text-gray-700 dark:text-dark-300 hover:bg-gray-200 dark:hover:bg-dark-700' }}">
                   This Year
                </a>
                @if($curP || request('from') || request('to'))
                    <a href="{{ route('reports.production', request()->except(['period', 'from', 'to', 'page'])) }}" class="text-xs text-rose-500 hover:underline ml-2">
                        Clear Date
                    </a>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                <div class="form-group">
                    <label class="form-label text-xs">From Date</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="form-input text-xs">
                </div>
                <div class="form-group">
                    <label class="form-label text-xs">To Date</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="form-input text-xs">
                </div>
                <div class="form-group">
                    <label class="form-label text-xs">Status</label>
                    <select name="status" class="form-input text-xs">
                        <option value="">All Statuses</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="pending_approval" {{ request('status') === 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="materials_issued" {{ request('status') === 'materials_issued' ? 'selected' : '' }}>Materials Issued</option>
                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label text-xs">Product</label>
                    <select name="product_id" class="form-input text-xs">
                        <option value="">All Finished Products</option>
                        @foreach($filterProducts as $pId => $pName)
                            <option value="{{ $pId }}" {{ (string)request('product_id') === (string)$pId ? 'selected' : '' }}>
                                {{ $pName }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 border-t border-gray-100 dark:border-dark-800">
                <div class="form-group flex-1 w-full">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search by order reference or notes..."
                           class="form-input text-xs">
                </div>
                <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                    <button type="submit" class="btn-primary text-xs px-4 py-2">Apply Filters</button>
                    <a href="{{ route('reports.production') }}" class="btn-secondary text-xs px-4 py-2">Reset</a>
                </div>
            </div>
        </form>
    </div>

    <!-- Manufacturing Orders Table -->
    <div class="card p-0 overflow-hidden shadow-sm">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wider text-gray-500 dark:text-dark-400 bg-gray-50/75 dark:bg-dark-950/60 border-b border-gray-200 dark:border-dark-800">
                        <th class="py-3 px-4">Order Ref</th>
                        <th class="py-3 px-4">Planned Date</th>
                        <th class="py-3 px-4">Finished Product</th>
                        <th class="py-3 px-4">Recipe / BOM</th>
                        <th class="py-3 px-4">Storage Location</th>
                        <th class="py-3 px-4 text-right">Planned Qty</th>
                        <th class="py-3 px-4 text-right">Good Produced</th>
                        <th class="py-3 px-4 text-right">Rejected</th>
                        <th class="py-3 px-4 text-center">Efficiency</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Assigned Staff</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-dark-800 text-sm">
                    @forelse($orders as $mo)
                    @php
                        $eff = $mo->calculateEfficiency();
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-dark-800/40 transition">
                        <td class="py-3 px-4 font-mono text-xs font-bold text-primary-600 dark:text-primary-400 whitespace-nowrap">
                            {{ $mo->reference }}
                        </td>
                        <td class="py-3 px-4 text-xs text-gray-500 dark:text-dark-300 whitespace-nowrap">
                            {{ $mo->planned_date ? $mo->planned_date->format('Y-m-d') : '—' }}
                        </td>
                        <td class="py-3 px-4 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                            {{ $mo->product->name ?? '—' }}
                        </td>
                        <td class="py-3 px-4 text-xs text-gray-600 dark:text-dark-300 whitespace-nowrap">
                            {{ $mo->recipe->name ?? 'Standard' }}
                        </td>
                        <td class="py-3 px-4 text-xs text-gray-600 dark:text-dark-300 whitespace-nowrap">
                            {{ $mo->storageLocation->name ?? '—' }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono text-xs text-gray-900 dark:text-white whitespace-nowrap">
                            {{ number_format($mo->planned_quantity, 2) }}
                            <span class="text-gray-400 dark:text-dark-500">{{ $mo->product->unit->code ?? 'units' }}</span>
                        </td>
                        <td class="py-3 px-4 text-right font-mono text-xs font-bold text-emerald-600 dark:text-emerald-400 whitespace-nowrap">
                            {{ number_format($mo->good_quantity ?? 0, 2) }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono text-xs font-medium text-rose-600 dark:text-rose-400 whitespace-nowrap">
                            {{ number_format($mo->rejected_quantity ?? 0, 2) }}
                        </td>
                        <td class="py-3 px-4 text-center whitespace-nowrap font-mono text-xs">
                            @if($mo->status === 'completed')
                                <span class="{{ $eff >= 95 ? 'text-emerald-600 dark:text-emerald-400' : ($eff >= 80 ? 'text-cyan-600 dark:text-cyan-400' : 'text-amber-600 dark:text-amber-400') }} font-bold">
                                    {{ number_format($eff, 1) }}%
                                </span>
                            @else
                                <span class="text-gray-400 dark:text-dark-500">—</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 whitespace-nowrap">
                            @if($mo->status === 'completed')
                                <span class="badge-green text-xs">Completed</span>
                            @elseif($mo->status === 'cancelled')
                                <span class="badge-red text-xs">Cancelled</span>
                            @elseif($mo->status === 'in_progress' || $mo->status === 'materials_issued')
                                <span class="badge-yellow text-xs">{{ ucwords(str_replace('_', ' ', $mo->status)) }}</span>
                            @else
                                <span class="badge-blue text-xs">{{ ucwords(str_replace('_', ' ', $mo->status)) }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-xs text-gray-500 dark:text-dark-300 whitespace-nowrap">
                            {{ $mo->assignedUser->name ?? 'Unassigned' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center py-16 text-gray-500 dark:text-dark-500">
                            No manufacturing orders match the selected criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-dark-800 bg-gray-50/50 dark:bg-dark-900/60">
            {{ $orders->links() }}
        </div>
        @endif
    </div>

    <!-- Raw Material Consumption Breakdown Section -->
    @if($materialIssues->isNotEmpty())
    <div class="card p-0 overflow-hidden shadow-sm print:hidden">
        <div class="p-5 border-b border-gray-200 dark:border-dark-800">
            <h2 class="text-base font-bold text-gray-900 dark:text-white">Raw Material Consumption & Scrap Breakdown</h2>
            <p class="text-xs text-gray-500 dark:text-dark-400 mt-0.5">Tracked material quantities issued, consumed, returned, and wasted per production batch.</p>
        </div>
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wider text-gray-500 dark:text-dark-400 bg-gray-50/75 dark:bg-dark-950/60 border-b border-gray-200 dark:border-dark-800">
                        <th class="py-3 px-4">MO Ref</th>
                        <th class="py-3 px-4">Raw Material Product</th>
                        <th class="py-3 px-4 text-right">Planned Qty</th>
                        <th class="py-3 px-4 text-right">Issued Qty</th>
                        <th class="py-3 px-4 text-right">Consumed Qty</th>
                        <th class="py-3 px-4 text-right">Returned Qty</th>
                        <th class="py-3 px-4 text-right">Wasted Qty</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-dark-800 text-sm">
                    @foreach($materialIssues as $issue)
                    <tr class="hover:bg-gray-50 dark:hover:bg-dark-800/40 transition">
                        <td class="py-3 px-4 font-mono text-xs text-primary-600 dark:text-primary-400 whitespace-nowrap">
                            {{ $issue->manufacturingOrder->reference ?? '—' }}
                        </td>
                        <td class="py-3 px-4 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                            {{ $issue->product->name ?? '—' }}
                            <span class="text-xs text-gray-400 dark:text-dark-500 font-mono">({{ $issue->product->sku ?? '' }})</span>
                        </td>
                        <td class="py-3 px-4 text-right font-mono text-xs text-gray-600 dark:text-dark-300 whitespace-nowrap">
                            {{ number_format($issue->quantity_planned, 2) }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono text-xs text-gray-900 dark:text-white whitespace-nowrap">
                            {{ number_format($issue->quantity_issued, 2) }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono text-xs font-bold text-emerald-600 dark:text-emerald-400 whitespace-nowrap">
                            {{ number_format($issue->quantity_used, 2) }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono text-xs text-cyan-600 dark:text-cyan-400 whitespace-nowrap">
                            {{ number_format($issue->quantity_returned, 2) }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono text-xs font-bold text-rose-600 dark:text-rose-400 whitespace-nowrap">
                            {{ number_format($issue->quantity_wasted, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
@endsection
