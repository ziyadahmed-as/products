@extends('layouts.app')
@section('title', 'Stock Transfers & Logistics Reports')

@section('content')
<div class="space-y-6">

    <!-- Header & Action Buttons -->
    <div class="page-header flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Stock Transfers & Logistics Reports</h1>
            <p class="text-gray-500 dark:text-dark-400 text-sm mt-1">
                Audit tracking of inter-branch inventory transfers, transit movements, and receiving status.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="btn-secondary flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Print</span>
            </button>
            <a href="{{ route('reports.transfers.export', request()->query()) }}" class="btn-primary flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Export CSV</span>
            </a>
        </div>
    </div>

    <!-- Quick Date Filter Toolbar -->
    <div class="card p-4 print:hidden">
        <form method="GET" action="{{ route('reports.transfers') }}" class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-2 text-sm">
                <span class="text-gray-500 dark:text-dark-400 text-xs font-semibold uppercase tracking-wider mr-2">Period:</span>
                @php $curP = request('period'); @endphp
                <a href="{{ route('reports.transfers', ['period' => 'today']) }}"
                   class="px-3 py-1 rounded-lg text-xs font-medium transition {{ $curP === 'today' ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-dark-800 text-gray-700 dark:text-dark-300 hover:bg-gray-200 dark:hover:bg-dark-700' }}">
                   Today
                </a>
                <a href="{{ route('reports.transfers', ['period' => 'this_week']) }}"
                   class="px-3 py-1 rounded-lg text-xs font-medium transition {{ $curP === 'this_week' ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-dark-800 text-gray-700 dark:text-dark-300 hover:bg-gray-200 dark:hover:bg-dark-700' }}">
                   This Week
                </a>
                <a href="{{ route('reports.transfers', ['period' => 'this_month']) }}"
                   class="px-3 py-1 rounded-lg text-xs font-medium transition {{ $curP === 'this_month' ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-dark-800 text-gray-700 dark:text-dark-300 hover:bg-gray-200 dark:hover:bg-dark-700' }}">
                   This Month
                </a>
                <a href="{{ route('reports.transfers', ['period' => 'this_year']) }}"
                   class="px-3 py-1 rounded-lg text-xs font-medium transition {{ $curP === 'this_year' ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-dark-800 text-gray-700 dark:text-dark-300 hover:bg-gray-200 dark:hover:bg-dark-700' }}">
                   This Year
                </a>
            </div>

            <div class="flex items-center gap-3">
                <select name="status" class="form-input text-xs py-1.5">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="dispatched" {{ request('status') === 'dispatched' ? 'selected' : '' }}>Dispatched</option>
                    <option value="received" {{ request('status') === 'received' ? 'selected' : '' }}>Received</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
                <input type="date" name="from" value="{{ request('from') }}" class="form-input text-xs py-1.5" title="From Date">
                <span class="text-gray-400 dark:text-dark-500 text-xs">to</span>
                <input type="date" name="to" value="{{ request('to') }}" class="form-input text-xs py-1.5" title="To Date">
                <button type="submit" class="btn-primary text-xs px-3 py-1.5">Apply</button>
                @if($curP || request('from') || request('to') || request('status'))
                    <a href="{{ route('reports.transfers') }}" class="text-xs text-rose-500 hover:underline">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Transfers Table -->
    <div class="card p-0 overflow-hidden shadow-sm">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wider text-gray-500 dark:text-dark-400 bg-gray-50/75 dark:bg-dark-950/60 border-b border-gray-200 dark:border-dark-800">
                        <th class="py-3 px-4">Transfer Ref</th>
                        <th class="py-3 px-4">Dispatch Date</th>
                        <th class="py-3 px-4">Source Location</th>
                        <th class="py-3 px-4">Destination Location</th>
                        <th class="py-3 px-4">Items Transferred</th>
                        <th class="py-3 px-4">Dispatched By</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-dark-800 text-sm">
                    @forelse($transfers as $tr)
                    <tr class="hover:bg-gray-50 dark:hover:bg-dark-800/40 transition">
                        <td class="py-3 px-4 font-mono text-xs font-bold text-primary-600 dark:text-primary-400 whitespace-nowrap">
                            {{ $tr->reference }}
                        </td>
                        <td class="py-3 px-4 text-xs text-gray-500 dark:text-dark-300 whitespace-nowrap">
                            {{ $tr->dispatch_date ? $tr->dispatch_date : '—' }}
                        </td>
                        <td class="py-3 px-4 text-xs whitespace-nowrap">
                            <div class="font-medium text-gray-900 dark:text-white">{{ $tr->fromLocation->name ?? '—' }}</div>
                            <div class="text-xs text-gray-500 dark:text-dark-500">{{ $tr->fromLocation->branch->name ?? '' }}</div>
                        </td>
                        <td class="py-3 px-4 text-xs whitespace-nowrap">
                            <div class="font-medium text-gray-900 dark:text-white">{{ $tr->toLocation->name ?? '—' }}</div>
                            <div class="text-xs text-gray-500 dark:text-dark-500">{{ $tr->toLocation->branch->name ?? '' }}</div>
                        </td>
                        <td class="py-3 px-4 text-xs text-gray-600 dark:text-dark-300 max-w-xs">
                            @foreach($tr->items as $item)
                                <div class="truncate">
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ number_format($item->quantity, 2) }}×</span>
                                    {{ $item->product->name ?? 'Product' }}
                                </div>
                            @endforeach
                        </td>
                        <td class="py-3 px-4 text-xs text-gray-600 dark:text-dark-300 whitespace-nowrap">
                            {{ $tr->user->name ?? '—' }}
                        </td>
                        <td class="py-3 px-4 whitespace-nowrap">
                            @if($tr->status === 'received' || $tr->status === 'completed')
                                <span class="badge badge-green text-xs">Received</span>
                            @elseif($tr->status === 'cancelled')
                                <span class="badge badge-red text-xs">Cancelled</span>
                            @elseif($tr->status === 'dispatched' || $tr->status === 'in_transit')
                                <span class="badge badge-yellow text-xs">In Transit</span>
                            @else
                                <span class="badge badge-blue text-xs">{{ ucfirst($tr->status) }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-xs text-gray-500 dark:text-dark-400 max-w-xs truncate" title="{{ $tr->notes }}">
                            {{ $tr->notes ?? '—' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-16 text-gray-500 dark:text-dark-500">
                            No stock transfer records found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transfers->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-dark-800 bg-gray-50/50 dark:bg-dark-900/60">
            {{ $transfers->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
