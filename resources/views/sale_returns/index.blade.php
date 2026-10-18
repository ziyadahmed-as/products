@extends('layouts.app')
@section('title', 'Returns & Refunds')

@section('content')
<div class="space-y-5">

    <div class="page-header">
        <div>
            <h1>Returns & Refunds</h1>
            <p class="text-slate-500 dark:text-dark-400 text-sm mt-1">Manage product return claims, restocks, and customer refund processing.</p>
        </div>
        <a href="{{ route('sale-returns.create') }}" class="btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Return Request
        </a>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stat-card">
            <p class="text-xs uppercase font-semibold text-slate-500 dark:text-dark-400">Total Claims</p>
            <p class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ number_format($summary['total']) }}</p>
        </div>
        <div class="stat-card">
            <p class="text-xs uppercase font-semibold text-amber-500">Pending Review</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">{{ number_format($summary['pending']) }}</p>
        </div>
        <div class="stat-card">
            <p class="text-xs uppercase font-semibold text-emerald-500">Approved Claims</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">{{ number_format($summary['approved']) }}</p>
        </div>
        <div class="stat-card">
            <p class="text-xs uppercase font-semibold text-sky-500">Total Refunded</p>
            <p class="text-2xl font-bold text-sky-600 mt-1">${{ number_format($summary['total_amount'], 2) }}</p>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" class="card p-4 flex flex-wrap gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by sale # or staff name…" class="form-input flex-1 min-w-[200px]">
        <select name="status" class="form-input w-44">
            <option value="">All Statuses</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
        </select>
        <button type="submit" class="btn-primary">Filter</button>
        <a href="{{ route('sale-returns.index') }}" class="btn-secondary">Reset</a>
    </form>

    <!-- Table -->
    <div class="card overflow-hidden">
        <table class="data-table">
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
                <tr>
                    <td>
                        <a href="{{ route('sale-returns.show', $ret) }}" class="font-mono font-semibold text-cyan-600 hover:text-cyan-700 text-sm">
                            #RET-{{ str_pad($ret->id, 5, '0', STR_PAD_LEFT) }}
                        </a>
                        <p class="text-xs text-slate-400">{{ $ret->created_at->format('M d, Y') }}</p>
                    </td>
                    <td>
                        @if($ret->sale)
                            <a href="{{ route('sales.show', $ret->sale) }}" class="text-sm font-medium text-slate-800 dark:text-dark-200 hover:text-cyan-600">
                                Sale #{{ $ret->sale->id }}
                            </a>
                        @else
                            <span class="text-slate-400 text-sm">—</span>
                        @endif
                    </td>
                    <td class="text-sm text-slate-600 dark:text-dark-300">
                        {{ $ret->user?->name ?? 'System' }}
                    </td>
                    <td class="text-sm text-slate-600 dark:text-dark-300 max-w-xs truncate">
                        {{ $ret->reason }}
                    </td>
                    <td class="text-sm font-semibold text-slate-900 dark:text-white">
                        ${{ number_format($ret->refund_amount, 2) }}
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
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('sale-returns.show', $ret) }}" class="btn-icon text-slate-500 hover:text-sky-600" title="View">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            @if($ret->status === 'pending')
                            <a href="{{ route('sale-returns.edit', $ret) }}" class="btn-icon text-slate-500 hover:text-amber-600" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            @endif
                            <form method="POST" action="{{ route('sale-returns.destroy', $ret) }}" onsubmit="return confirm('Delete return claim #{{ $ret->id }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-icon text-slate-500 hover:text-rose-600" title="Delete">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-12 text-slate-400 dark:text-dark-500">
                        No return claims found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-slate-100 dark:border-dark-800">
            {{ $returns->links() }}
        </div>
    </div>

</div>
@endsection
