@extends('layouts.app')
@section('title', 'Stock Receipts')

@section('content')
<div class="space-y-6">

    <!-- Header & Action Buttons -->
    <div class="page-header flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Stock Receipts</h1>
            <p class="text-dark-400 text-sm mt-1">History of inbound raw material deliveries, purchases, and initial stock receipts.</p>
        </div>
        <div class="flex items-center gap-3">
            @hasanyrole('Super Admin|Manager')
            <a href="{{ route('stock-receipts.create') }}" class="btn-primary flex items-center gap-2 shadow-lg shadow-primary-600/30">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Receive Stock
            </a>
            @endhasanyrole
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="card p-5 bg-dark-900 border border-dark-800 rounded-2xl">
            <span class="text-xs font-semibold uppercase tracking-wider text-dark-400">Total Receipts Processed</span>
            <div class="text-2xl font-bold text-white mt-2">{{ number_format($summary['total_receipts']) }}</div>
            <div class="text-xs text-dark-500 mt-1">All-time inbound deliveries</div>
        </div>

        <div class="card p-5 bg-dark-900 border border-dark-800 rounded-2xl">
            <span class="text-xs font-semibold uppercase tracking-wider text-dark-400">Total Received Value</span>
            <div class="text-2xl font-bold text-emerald-400 mt-2 font-mono">Br {{ number_format($summary['total_valuation'], 2) }}</div>
            <div class="text-xs text-dark-500 mt-1">Cumulated delivery valuation</div>
        </div>

        <div class="card p-5 bg-dark-900 border border-dark-800 rounded-2xl">
            <span class="text-xs font-semibold uppercase tracking-wider text-dark-400">Receipts Today</span>
            <div class="text-2xl font-bold text-cyan-400 mt-2 font-mono">{{ number_format($summary['today_receipts']) }}</div>
            <div class="text-xs text-dark-500 mt-1">{{ now()->format('D, d M Y') }}</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card p-4 bg-dark-900 border border-dark-800 rounded-2xl">
        <form method="GET" action="{{ route('stock-receipts.index') }}" class="flex flex-wrap gap-3 items-end">
            <div class="form-group flex-1 min-w-[200px]">
                <label class="form-label text-xs">Search Reference, Item, or Notes</label>
                <input name="search" value="{{ request('search') }}" type="text"
                       class="form-input text-xs" placeholder="Receipt #, product name, SKU...">
            </div>
            <div class="form-group min-w-[160px]">
                <label class="form-label text-xs">Storage Location</label>
                <select name="storage_location_id" class="form-select text-xs">
                    <option value="">All Locations</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ request('storage_location_id') == $loc->id ? 'selected' : '' }}>
                            {{ $loc->name }} ({{ $loc->branch->name ?? '' }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group min-w-[130px]">
                <label class="form-label text-xs">From Date</label>
                <input type="date" name="from" value="{{ request('from') }}" class="form-input text-xs">
            </div>
            <div class="form-group min-w-[130px]">
                <label class="form-label text-xs">To Date</label>
                <input type="date" name="to" value="{{ request('to') }}" class="form-input text-xs">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary text-xs px-4 py-2">Filter</button>
                <a href="{{ route('stock-receipts.index') }}" class="btn-secondary text-xs px-3 py-2">Reset</a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="card p-0 bg-dark-900 border border-dark-800 rounded-2xl overflow-hidden shadow-xl">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wider text-dark-400 bg-dark-950/60 border-b border-dark-800">
                        <th class="py-3 px-4">Receipt Ref</th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Storage Location</th>
                        <th class="py-3 px-4">Branch</th>
                        <th class="py-3 px-4">Items Received</th>
                        <th class="py-3 px-4 text-right">Total Cost</th>
                        <th class="py-3 px-4">Received By</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-800 text-sm">
                    @forelse($receipts as $receipt)
                    <tr class="hover:bg-dark-800/40 transition">
                        <td class="py-3 px-4 font-mono text-xs font-bold text-primary-400 whitespace-nowrap">
                            <a href="{{ route('stock-receipts.show', $receipt) }}" class="hover:underline">
                                {{ $receipt->reference }}
                            </a>
                        </td>
                        <td class="py-3 px-4 font-mono text-xs text-dark-300 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($receipt->date)->format('Y-m-d') }}
                        </td>
                        <td class="py-3 px-4 text-xs text-white whitespace-nowrap">
                            {{ $receipt->storageLocation->name ?? 'Main Storage' }}
                        </td>
                        <td class="py-3 px-4 text-xs text-dark-400 whitespace-nowrap">
                            {{ $receipt->storageLocation->branch->name ?? '—' }}
                        </td>
                        <td class="py-3 px-4 text-xs text-dark-300">
                            @php
                                $movs = $receipt->stockMovements;
                                $firstThree = $movs->take(2);
                            @endphp
                            @foreach($firstThree as $m)
                                <span class="inline-block px-2 py-0.5 rounded text-[11px] bg-dark-800 text-dark-200 mr-1 mb-1">
                                    {{ $m->product->name ?? 'Item' }} ({{ number_format($m->quantity, 1) }} {{ $m->product->unit?->code ?? '' }})
                                </span>
                            @endforeach
                            @if($movs->count() > 2)
                                <span class="text-[11px] text-dark-500 font-semibold">+{{ $movs->count() - 2 }} more</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right font-mono text-xs font-bold text-emerald-400 whitespace-nowrap">
                            Br {{ number_format($receipt->total_cost, 2) }}
                        </td>
                        <td class="py-3 px-4 text-xs text-dark-400 whitespace-nowrap">
                            {{ $receipt->user->name ?? 'Staff' }}
                        </td>
                        <td class="py-3 px-4 text-right whitespace-nowrap">
                            <a href="{{ route('stock-receipts.show', $receipt) }}" class="btn-icon" title="View Receipt">
                                <svg class="w-4 h-4 text-dark-400 hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-16 text-dark-500">
                            <div class="w-14 h-14 rounded-2xl bg-dark-800 flex items-center justify-center mx-auto mb-3">
                                <svg class="w-7 h-7 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <p class="font-medium text-dark-400">No stock receipts recorded</p>
                            <p class="text-xs text-dark-600 mt-1">Receive deliveries of raw materials or purchased goods to build stock.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($receipts->hasPages())
        <div class="px-6 py-4 border-t border-dark-800 bg-dark-900/60">
            {{ $receipts->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
