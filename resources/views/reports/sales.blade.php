@extends('layouts.app')
@section('title', $isSeller ? 'My Sales & Revenue Report' : 'Sales & Revenue Reports')

@section('content')
<div class="space-y-6">

    <!-- Header & Action Buttons -->
    <div class="page-header flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">
                {{ $isSeller ? 'My Sales & Revenue Report' : 'Sales & Revenue Reports' }}
            </h1>
            <p class="text-gray-500 dark:text-dark-400 text-sm mt-1">
                {{ $isSeller ? 'Detailed record of your personal sales, products sold, and payment status.' : 'Consolidated and branch-level sales transactions, invoices, and payment collections.' }}
            </p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="btn-secondary flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Print</span>
            </button>
            <a href="{{ route('reports.sales.export', request()->query()) }}" class="btn-primary flex items-center gap-2 shadow-sm">
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
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-dark-400">Total Revenue</span>
            <div class="text-2xl font-bold text-gray-900 dark:text-white mt-2">Br{{ number_format($totalRevenue, 2) }}</div>
            <div class="text-xs text-gray-500 dark:text-dark-400 mt-1">{{ number_format($totalOrders) }} invoices</div>
        </div>

        <div class="card p-5">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-dark-400">Collected Payments</span>
            <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-2">Br{{ number_format($totalPaid, 2) }}</div>
            <div class="text-xs text-emerald-600/80 dark:text-emerald-500/80 mt-1">Paid in full or partial</div>
        </div>

        <div class="card p-5">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-dark-400">Outstanding Receivables</span>
            <div class="text-2xl font-bold text-rose-600 dark:text-rose-400 mt-2">Br{{ number_format($totalUnpaid, 2) }}</div>
            <div class="text-xs text-rose-600/80 dark:text-rose-500/80 mt-1">Unsettled balances</div>
        </div>

        <div class="card p-5">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-dark-400">Avg. Order Value</span>
            <div class="text-2xl font-bold text-cyan-600 dark:text-cyan-400 mt-2">Br{{ number_format($avgOrderValue, 2) }}</div>
            <div class="text-xs text-gray-500 dark:text-dark-400 mt-1">Per transaction average</div>
        </div>

        <div class="card p-5">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-dark-400">Discounts & Taxes</span>
            <div class="text-sm font-semibold text-gray-700 dark:text-dark-200 mt-2">
                Discounts: <span class="text-amber-600 dark:text-amber-400 font-bold">Br{{ number_format($totalDiscount, 2) }}</span>
            </div>
            <div class="text-sm font-semibold text-gray-700 dark:text-dark-200 mt-1">
                Taxes: <span class="text-indigo-600 dark:text-indigo-400 font-bold">Br{{ number_format($totalTax, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card p-5 print:hidden">
        <form method="GET" action="{{ route('reports.sales') }}" class="space-y-4">
            
            {{-- Quick Presets --}}
            <div class="flex flex-wrap items-center gap-2 pb-3 border-b border-gray-200 dark:border-dark-800 text-sm">
                <span class="text-gray-500 dark:text-dark-400 text-xs font-semibold uppercase tracking-wider mr-2">Period:</span>
                @php $curP = request('period'); @endphp
                <a href="{{ route('reports.sales', array_merge(request()->except(['period', 'from', 'to', 'page']), ['period' => 'today'])) }}"
                   class="px-3 py-1 rounded-lg text-xs font-medium transition {{ $curP === 'today' ? 'bg-primary-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-dark-800 text-gray-700 dark:text-dark-300 hover:bg-gray-200 dark:hover:bg-dark-700' }}">
                   Today
                </a>
                <a href="{{ route('reports.sales', array_merge(request()->except(['period', 'from', 'to', 'page']), ['period' => 'this_week'])) }}"
                   class="px-3 py-1 rounded-lg text-xs font-medium transition {{ $curP === 'this_week' ? 'bg-primary-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-dark-800 text-gray-700 dark:text-dark-300 hover:bg-gray-200 dark:hover:bg-dark-700' }}">
                   This Week
                </a>
                <a href="{{ route('reports.sales', array_merge(request()->except(['period', 'from', 'to', 'page']), ['period' => 'this_month'])) }}"
                   class="px-3 py-1 rounded-lg text-xs font-medium transition {{ $curP === 'this_month' ? 'bg-primary-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-dark-800 text-gray-700 dark:text-dark-300 hover:bg-gray-200 dark:hover:bg-dark-700' }}">
                   This Month
                </a>
                <a href="{{ route('reports.sales', array_merge(request()->except(['period', 'from', 'to', 'page']), ['period' => 'this_year'])) }}"
                   class="px-3 py-1 rounded-lg text-xs font-medium transition {{ $curP === 'this_year' ? 'bg-primary-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-dark-800 text-gray-700 dark:text-dark-300 hover:bg-gray-200 dark:hover:bg-dark-700' }}">
                   This Year
                </a>
                @if($curP || request('from') || request('to'))
                    <a href="{{ route('reports.sales', request()->except(['period', 'from', 'to', 'page'])) }}" class="text-xs text-rose-500 hover:underline ml-2">
                        Clear Date
                    </a>
                @endif
            </div>

            {{-- Fields --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
                <div class="form-group">
                    <label class="form-label text-xs">From Date</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="form-input text-xs">
                </div>
                <div class="form-group">
                    <label class="form-label text-xs">To Date</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="form-input text-xs">
                </div>

                @if(!$isSeller && !empty($filterBranches))
                <div class="form-group">
                    <label class="form-label text-xs">Branch</label>
                    <select name="branch_id" class="form-input text-xs">
                        <option value="">All Branches</option>
                        @foreach($filterBranches as $bId => $bName)
                            <option value="{{ $bId }}" {{ (string)request('branch_id') === (string)$bId ? 'selected' : '' }}>
                                {{ $bName }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                @if(!$isSeller && !empty($filterSellers))
                <div class="form-group">
                    <label class="form-label text-xs">Seller</label>
                    <select name="seller_id" class="form-input text-xs">
                        <option value="">All Sellers</option>
                        @foreach($filterSellers as $s)
                            <option value="{{ $s->id }}" {{ (string)request('seller_id') === (string)$s->id ? 'selected' : '' }}>
                                {{ $s->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="form-group">
                    <label class="form-label text-xs">Payment Status</label>
                    <select name="payment_status" class="form-input text-xs">
                        <option value="">All Statuses</option>
                        <option value="Paid" {{ request('payment_status') === 'Paid' ? 'selected' : '' }}>Paid</option>
                        <option value="Partial" {{ request('payment_status') === 'Partial' ? 'selected' : '' }}>Partial</option>
                        <option value="Unpaid" {{ request('payment_status') === 'Unpaid' ? 'selected' : '' }}>Unpaid</option>
                        <option value="Refunded" {{ request('payment_status') === 'Refunded' ? 'selected' : '' }}>Refunded</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label text-xs">Order Status</label>
                    <select name="status" class="form-input text-xs">
                        <option value="">All Orders</option>
                        <option value="Completed" {{ request('status') === 'Completed' ? 'selected' : '' }}>Completed</option>
                        <option value="Fulfilled" {{ request('status') === 'Fulfilled' ? 'selected' : '' }}>Fulfilled</option>
                        <option value="Pending" {{ request('status') === 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="Cancelled" {{ request('status') === 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label text-xs">Order Type</label>
                    <select name="type" class="form-input text-xs">
                        <option value="">All Types</option>
                        <option value="quick_sale" {{ request('type') === 'quick_sale' ? 'selected' : '' }}>Quick Sale (POS)</option>
                        <option value="customer_order" {{ request('type') === 'customer_order' ? 'selected' : '' }}>Online Customer Order</option>
                    </select>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 border-t border-gray-200 dark:border-dark-800">
                <div class="form-group flex-1 w-full">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search by invoice reference, customer name, or phone..."
                           class="form-input text-xs">
                </div>
                <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                    <button type="submit" class="btn-primary text-xs px-4 py-2 shadow-sm">Apply Filters</button>
                    <a href="{{ route('reports.sales') }}" class="btn-secondary text-xs px-4 py-2">Reset</a>
                </div>
            </div>
        </form>
    </div>

    <!-- Sales Table -->
    <div class="card p-0 overflow-hidden shadow-sm">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wider text-gray-500 dark:text-dark-400 bg-gray-50 dark:bg-dark-950/60 border-b border-gray-200 dark:border-dark-800">
                        <th class="py-3 px-4">Invoice #</th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Customer</th>
                        @if(!$isSeller)
                        <th class="py-3 px-4">Branch</th>
                        <th class="py-3 px-4">Seller</th>
                        @endif
                        <th class="py-3 px-4">Products & Qty</th>
                        <th class="py-3 px-4">Total</th>
                        <th class="py-3 px-4">Paid</th>
                        <th class="py-3 px-4">Balance</th>
                        <th class="py-3 px-4">Payment</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Stock</th>
                        <th class="py-3 px-4 text-right">Invoice</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-dark-800 text-sm">
                    @forelse($sales as $sale)
                    @php
                        $balance = $sale->total - $sale->paid_amount;
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-dark-800/40 transition">
                        <td class="py-3 px-4 font-mono text-xs font-bold text-primary-600 dark:text-primary-400 whitespace-nowrap">
                            {{ $sale->reference }}
                        </td>
                        <td class="py-3 px-4 text-xs text-gray-500 dark:text-dark-300 whitespace-nowrap">
                            {{ $sale->created_at ? $sale->created_at->format('Y-m-d H:i') : '—' }}
                        </td>
                        <td class="py-3 px-4 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                            {{ $sale->customer_name ?? 'Walk-in' }}
                            @if($sale->customer_phone)
                                <div class="text-xs text-gray-500 dark:text-dark-400">{{ $sale->customer_phone }}</div>
                            @endif
                        </td>
                        @if(!$isSeller)
                        <td class="py-3 px-4 text-xs text-gray-600 dark:text-dark-300 whitespace-nowrap">
                            {{ $sale->branch?->name ?? '—' }}
                        </td>
                        <td class="py-3 px-4 text-xs text-gray-600 dark:text-dark-300 whitespace-nowrap">
                            {{ $sale->user?->name ?? 'System' }}
                        </td>
                        @endif
                        <td class="py-3 px-4 text-xs text-gray-600 dark:text-dark-300 max-w-xs">
                            @foreach($sale->lines as $line)
                                <div class="truncate" title="{{ $line->product?->name }} ({{ number_format($line->quantity, 2) }} @ Br{{ number_format($line->unit_price, 2) }})">
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ number_format($line->quantity, 2) }}×</span>
                                    {{ $line->product?->name ?? 'Unknown Item' }}
                                </div>
                            @endforeach
                        </td>
                        <td class="py-3 px-4 font-bold text-gray-900 dark:text-white whitespace-nowrap">
                            Br{{ number_format($sale->total, 2) }}
                        </td>
                        <td class="py-3 px-4 text-emerald-600 dark:text-emerald-400 font-medium whitespace-nowrap">
                            Br{{ number_format($sale->paid_amount, 2) }}
                        </td>
                        <td class="py-3 px-4 font-medium whitespace-nowrap {{ $balance > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-gray-500 dark:text-dark-400' }}">
                            Br{{ number_format($balance, 2) }}
                        </td>
                        <td class="py-3 px-4 whitespace-nowrap">
                            @if($sale->payment_status === 'Paid')
                                <span class="badge badge-green text-xs">Paid</span>
                            @elseif($sale->payment_status === 'Partial')
                                <span class="badge badge-yellow text-xs">Partial</span>
                            @else
                                <span class="badge badge-red text-xs">Unpaid</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 whitespace-nowrap">
                            <span class="badge badge-blue text-xs">{{ ucfirst($sale->status) }}</span>
                        </td>
                        <td class="py-3 px-4 whitespace-nowrap text-xs">
                            @if($sale->is_stock_deducted)
                                <span class="text-emerald-600 dark:text-emerald-400">✅ Yes</span>
                            @else
                                <span class="text-amber-600 dark:text-amber-400">⏳ No</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right whitespace-nowrap">
                            <a href="{{ route('invoice.show', $sale) }}" target="_blank"
                               class="text-xs text-primary-600 dark:text-primary-400 hover:underline font-medium inline-flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                </svg>
                                <span>View</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $isSeller ? 11 : 13 }}" class="text-center py-16 text-gray-400 dark:text-dark-500">
                            No sales records found matching the selected criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sales->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-dark-800 bg-gray-50 dark:bg-dark-900/60">
            {{ $sales->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
