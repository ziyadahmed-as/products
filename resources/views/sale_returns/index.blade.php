@extends('layouts.app')
@section('title', 'Returns & Refunds')

@section('content')
<div class="space-y-6">

    <div class="page-header flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800/60">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/></svg>
                </span>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Returns & Refunds</h1>
            </div>
            <p class="text-slate-500 dark:text-dark-400 text-sm mt-1 ml-10">Manage product return claims, restocks, and customer refund processing.</p>
        </div>
        <a href="{{ route('sale-returns.create') }}" class="btn-primary flex items-center gap-2 shadow-xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>New Return Request</span>
        </a>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stat-card flex items-center gap-4">
            <div class="stat-icon bg-slate-100 dark:bg-dark-800 text-slate-600 dark:text-dark-300 border border-slate-200 dark:border-dark-700 flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <div class="min-w-0 flex-1">
                <div class="stat-label">Total Claims</div>
                <div class="stat-value text-slate-900 dark:text-white">{{ number_format($summary['total']) }}</div>
            </div>
        </div>
        <div class="stat-card flex items-center gap-4">
            <div class="stat-icon bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800/60 flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="min-w-0 flex-1">
                <div class="stat-label">Pending Review</div>
                <div class="stat-value text-amber-600 dark:text-amber-400 font-mono">{{ number_format($summary['pending']) }}</div>
            </div>
        </div>
        <div class="stat-card flex items-center gap-4">
            <div class="stat-icon bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60 flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="min-w-0 flex-1">
                <div class="stat-label">Approved Claims</div>
                <div class="stat-value text-emerald-600 dark:text-emerald-400 font-mono">{{ number_format($summary['approved']) }}</div>
            </div>
        </div>
        <div class="stat-card flex items-center gap-4">
            <div class="stat-icon bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-800/60 flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="min-w-0 flex-1">
                <div class="stat-label">Total Refunded</div>
                <div class="stat-value text-sky-600 dark:text-sky-400 font-mono">Br {{ number_format($summary['total_amount'], 2) }}</div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card p-4">
        <form method="GET" action="{{ route('sale-returns.index') }}" class="flex flex-wrap gap-3 items-end">
            <div class="form-group flex-1 min-w-[200px]">
                <label class="form-label text-xs">Search Claim</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by sale # or staff name…" class="form-input text-xs pl-9">
                </div>
            </div>
            <div class="form-group min-w-[160px]">
                <label class="form-label text-xs">Status</label>
                <select name="status" class="form-select text-xs">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary text-xs px-4 py-2">Filter</button>
                <a href="{{ route('sale-returns.index') }}" class="btn-secondary text-xs px-3 py-2">Reset</a>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="card p-0 overflow-hidden shadow-xs">
        <div class="p-4 border-b border-slate-200 dark:border-dark-800 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Returns Ledger</h3>
                <span class="badge badge-blue">{{ $returns->total() }} claims</span>
            </div>
            @if(request()->anyFilled(['search', 'status']))
                <span class="text-xs text-slate-500 dark:text-dark-400">Filtered results</span>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Claim #</th>
                        <th>Sale Order</th>
                        <th>Initiated By</th>
                        <th>Reason</th>
                        <th>Refund Amount</th>
                        <th>Status</th>
                        <th>Refund Processed</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returns as $ret)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-dark-850/50 transition-colors">
                        <td>
                            <a href="{{ route('sale-returns.show', $ret) }}" class="font-mono font-bold text-sky-600 dark:text-sky-400 hover:underline text-xs flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                #RET-{{ str_pad($ret->id, 5, '0', STR_PAD_LEFT) }}
                            </a>
                            <p class="text-[11px] font-mono text-slate-400 dark:text-dark-400 mt-0.5">{{ $ret->created_at->format('M d, Y') }}</p>
                        </td>
                        <td>
                            @if($ret->sale)
                                <a href="{{ route('sales.show', $ret->sale) }}" class="text-xs font-semibold text-slate-800 dark:text-dark-200 hover:text-sky-600 dark:hover:text-sky-400 font-mono">
                                    Sale #{{ $ret->sale->id }}
                                </a>
                            @else
                                <span class="text-slate-400 text-xs">—</span>
                            @endif
                        </td>
                        <td class="text-xs text-slate-600 dark:text-dark-300">
                            <span class="inline-flex items-center gap-1.5">
                                <span class="w-6 h-6 rounded-full bg-slate-200 dark:bg-dark-700 text-slate-700 dark:text-dark-200 text-[10px] font-bold flex items-center justify-center">
                                    {{ strtoupper(substr($ret->user?->name ?? 'S', 0, 1)) }}
                                </span>
                                {{ $ret->user?->name ?? 'System' }}
                            </span>
                        </td>
                        <td class="text-xs text-slate-600 dark:text-dark-300 max-w-xs truncate">
                            {{ $ret->reason }}
                        </td>
                        <td class="text-xs font-mono font-bold text-slate-900 dark:text-white">
                            Br {{ number_format($ret->refund_amount, 2) }}
                        </td>
                        <td>
                            @if($ret->status === 'approved')
                                <span class="badge badge-green capitalize">Approved</span>
                            @elseif($ret->status === 'pending')
                                <span class="badge badge-yellow capitalize">Pending</span>
                            @else
                                <span class="badge badge-red capitalize">Rejected</span>
                            @endif
                        </td>
                        <td>
                            @if($ret->is_refund_processed)
                                <span class="badge badge-green">Refunded</span>
                            @else
                                <span class="badge badge-blue">Not Processed</span>
                            @endif
                        </td>
                        <td class="text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('sale-returns.show', $ret) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-sky-600 dark:hover:text-sky-400 hover:bg-sky-50 dark:hover:bg-sky-950/50 transition-colors" title="View">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                @if($ret->status === 'pending')
                                <a href="{{ route('sale-returns.edit', $ret) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/50 transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                @endif
                                <form method="POST" action="{{ route('sale-returns.destroy', $ret) }}" onsubmit="return confirm('Delete return claim #{{ $ret->id }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/50 transition-colors" title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-16 text-slate-400 dark:text-dark-500">
                            <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-dark-800 flex items-center justify-center mx-auto mb-3">
                                <svg class="w-7 h-7 text-slate-400 dark:text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/></svg>
                            </div>
                            <p class="font-medium text-slate-600 dark:text-dark-400">No return claims found</p>
                            <p class="text-xs text-slate-400 dark:text-dark-600 mt-1">Processed product returns and refund requests will appear here.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($returns->hasPages())
        <div class="p-4 border-t border-slate-200 dark:border-dark-800 bg-slate-50/50 dark:bg-dark-900/60">
            {{ $returns->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
