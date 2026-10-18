@extends('layouts.app')
@section('title', 'Stock Receipts')

@section('content')
<div class="space-y-6">

    <!-- Header & Action Buttons -->
    <div class="page-header flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </span>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Stock Receipts</h1>
            </div>
            <p class="text-slate-500 dark:text-dark-400 text-sm mt-1 ml-10">History of inbound raw material deliveries, purchases, and initial stock receipts.</p>
        </div>
        <div class="flex items-center gap-3">
            @hasanyrole('Super Admin|Manager|Production Officer')
            <a href="{{ route('stock-receipts.create') }}" class="btn-primary flex items-center gap-2 shadow-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Receive Stock</span>
            </a>
            @endhasanyrole
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="stat-card flex items-center gap-4">
            <div class="stat-icon bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/60 flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <div class="min-w-0 flex-1">
                <div class="stat-label">Total Receipts Processed</div>
                <div class="stat-value text-slate-900 dark:text-white">{{ number_format($summary['total_receipts']) }}</div>
                <div class="text-xs text-slate-400 dark:text-dark-500 mt-0.5">All-time inbound deliveries</div>
            </div>
        </div>

        <div class="stat-card flex items-center gap-4">
            <div class="stat-icon bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60 flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="min-w-0 flex-1">
                <div class="stat-label">Total Received Value</div>
                <div class="stat-value text-emerald-600 dark:text-emerald-400 font-mono">Br {{ number_format($summary['total_valuation'], 2) }}</div>
                <div class="text-xs text-slate-400 dark:text-dark-500 mt-0.5">Cumulated delivery valuation</div>
            </div>
        </div>

        <div class="stat-card flex items-center gap-4">
            <div class="stat-icon bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-800/60 flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <div class="min-w-0 flex-1">
                <div class="stat-label">Receipts Today</div>
                <div class="stat-value text-sky-600 dark:text-sky-400 font-mono">{{ number_format($summary['today_receipts']) }}</div>
                <div class="text-xs text-slate-400 dark:text-dark-500 mt-0.5">{{ now()->format('D, d M Y') }}</div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card p-4">
        <form method="GET" action="{{ route('stock-receipts.index') }}" class="flex flex-wrap gap-3 items-end">
            <div class="form-group flex-1 min-w-[200px]">
                <label class="form-label text-xs">Search Reference, Item, or Notes</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input name="search" value="{{ request('search') }}" type="text"
                           class="form-input text-xs pl-9" placeholder="Receipt #, product name, SKU...">
                </div>
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

    <!-- Table Card -->
    <div class="card p-0 overflow-hidden shadow-xs">
        <div class="p-4 border-b border-slate-200 dark:border-dark-800 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Receipts Ledger</h3>
                <span class="badge badge-blue">{{ $receipts->total() }} records</span>
            </div>
            @if(request()->anyFilled(['search', 'storage_location_id', 'from', 'to']))
                <span class="text-xs text-slate-500 dark:text-dark-400">Filtered results</span>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Receipt Ref</th>
                        <th>Date</th>
                        <th>Storage Location</th>
                        <th>Branch</th>
                        <th>Items Received</th>
                        <th class="text-right">Total Cost</th>
                        <th>Received By</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($receipts as $receipt)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-dark-850/50 transition-colors">
                        <td class="font-mono text-xs font-bold text-sky-600 dark:text-sky-400 whitespace-nowrap">
                            <a href="{{ route('stock-receipts.show', $receipt) }}" class="hover:underline flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                {{ $receipt->reference }}
                            </a>
                        </td>
                        <td class="font-mono text-xs text-slate-500 dark:text-dark-300 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($receipt->date)->format('Y-m-d') }}
                        </td>
                        <td class="text-xs font-medium text-slate-800 dark:text-white whitespace-nowrap">
                            {{ $receipt->storageLocation->name ?? 'Main Storage' }}
                        </td>
                        <td class="text-xs whitespace-nowrap">
                            <span class="badge badge-purple">{{ $receipt->storageLocation->branch->name ?? '—' }}</span>
                        </td>
                        <td class="text-xs text-slate-700 dark:text-dark-300">
                            @php
                                $movs = $receipt->stockMovements;
                                $firstThree = $movs->take(2);
                            @endphp
                            @foreach($firstThree as $m)
                                <span class="inline-block px-2 py-0.5 rounded text-[11px] bg-slate-100 dark:bg-dark-800 text-slate-700 dark:text-dark-200 border border-slate-200 dark:border-dark-700 mr-1 mb-1">
                                    {{ $m->product->name ?? 'Item' }} ({{ number_format($m->quantity, 1) }} {{ $m->product->unit?->code ?? '' }})
                                </span>
                            @endforeach
                            @if($movs->count() > 2)
                                <span class="text-[11px] text-slate-400 dark:text-dark-500 font-semibold">+{{ $movs->count() - 2 }} more</span>
                            @endif
                        </td>
                        <td class="text-right font-mono text-xs font-bold text-emerald-600 dark:text-emerald-400 whitespace-nowrap">
                            Br {{ number_format($receipt->total_cost, 2) }}
                        </td>
                        <td class="text-xs text-slate-600 dark:text-dark-300 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5">
                                <span class="w-6 h-6 rounded-full bg-slate-200 dark:bg-dark-700 text-slate-700 dark:text-dark-200 text-[10px] font-bold flex items-center justify-center">
                                    {{ strtoupper(substr($receipt->user->name ?? 'S', 0, 1)) }}
                                </span>
                                {{ $receipt->user->name ?? 'Staff' }}
                            </span>
                        </td>
                        <td class="text-right whitespace-nowrap">
                            <a href="{{ route('stock-receipts.show', $receipt) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-sky-600 dark:hover:text-sky-400 hover:bg-sky-50 dark:hover:bg-sky-950/50 transition-colors inline-flex items-center justify-center" title="View Receipt">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-16 text-slate-400 dark:text-dark-500">
                            <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-dark-800 flex items-center justify-center mx-auto mb-3">
                                <svg class="w-7 h-7 text-slate-400 dark:text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <p class="font-medium text-slate-600 dark:text-dark-400">No stock receipts recorded</p>
                            <p class="text-xs text-slate-400 dark:text-dark-600 mt-1">Receive deliveries of raw materials or purchased goods to build stock.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($receipts->hasPages())
        <div class="px-6 py-4 border-t border-slate-200 dark:border-dark-800 bg-slate-50/50 dark:bg-dark-900/60">
            {{ $receipts->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
