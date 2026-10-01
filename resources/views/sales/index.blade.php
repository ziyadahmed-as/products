@extends('layouts.app')
@section('title', 'Sales')

@section('content')
<div class="space-y-5">

    <!-- Header -->
    <div class="page-header">
        <div>
            <h1>Sales</h1>
            <p class="text-dark-400 text-sm mt-1">All sales transactions — quick sales and customer orders.</p>
        </div>
        <a href="{{ route('sales.create') }}" class="btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New Sale
        </a>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="stat-card">
            <div class="stat-icon bg-emerald-900/40">
                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="stat-value">${{ number_format($summary['total_revenue'] ?? 0, 2) }}</p>
                <p class="stat-label">Total Revenue</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-primary-900/40">
                <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div>
                <p class="stat-value">{{ $summary['total_count'] ?? 0 }}</p>
                <p class="stat-label">Total Sales</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-yellow-900/40">
                <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="stat-value text-yellow-400">${{ number_format($summary['unpaid'] ?? 0, 2) }}</p>
                <p class="stat-label">Outstanding</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-emerald-900/40">
                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <div>
                <p class="stat-value">${{ number_format($summary['today'] ?? 0, 2) }}</p>
                <p class="stat-label">Today's Revenue</p>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="form-group flex-1 min-w-[160px]">
                <label class="form-label">Search Reference</label>
                <input name="search" value="{{ request('search') }}" type="text"
                       class="form-input" placeholder="REF-...">
            </div>
            <div class="form-group min-w-[140px]">
                <label class="form-label">Payment Status</label>
                <select name="payment_status" class="form-select">
                    <option value="">All</option>
                    <option value="Paid"    {{ request('payment_status') === 'Paid' ? 'selected' : '' }}>Paid</option>
                    <option value="Partial" {{ request('payment_status') === 'Partial' ? 'selected' : '' }}>Partial</option>
                    <option value="Unpaid"  {{ request('payment_status') === 'Unpaid' ? 'selected' : '' }}>Unpaid</option>
                </select>
            </div>
            <div class="form-group min-w-[130px]">
                <label class="form-label">From Date</label>
                <input name="from" value="{{ request('from') }}" type="date" class="form-input">
            </div>
            <div class="form-group min-w-[130px]">
                <label class="form-label">To Date</label>
                <input name="to" value="{{ request('to') }}" type="date" class="form-input">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary">Filter</button>
                <a href="{{ route('sales.index') }}" class="btn-secondary">Reset</a>
            </div>
        </form>
    </div>

    <!-- Sales Table -->
    <div class="card p-0">
        <div class="table-wrapper rounded-2xl">
            <table class="table">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Type</th>
                        <th>Customer</th>
                        <th>Seller</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Paid</th>
                        <th>Payment</th>
                        <th>Date</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                    <tr>
                        <td class="font-mono text-primary-400">{{ $sale->reference }}</td>
                        <td>
                            @if($sale->type === 'quick_sale')
                                <span class="badge-blue">Quick Sale</span>
                            @else
                                <span class="badge-purple">Order</span>
                            @endif
                        </td>
                        <td>{{ $sale->customer_name ?? 'Walk-in' }}</td>
                        <td class="text-dark-300">{{ $sale->user->name ?? '—' }}</td>
                        <td class="text-dark-300">{{ $sale->lines_count ?? $sale->lines->count() }}</td>
                        <td class="font-bold text-white">${{ number_format($sale->total, 2) }}</td>
                        <td class="text-dark-300">${{ number_format($sale->paid_amount, 2) }}</td>
                        <td>
                            @if($sale->payment_status === 'Paid')
                                <span class="badge-green">Paid</span>
                            @elseif($sale->payment_status === 'Partial')
                                <span class="badge-yellow">Partial</span>
                            @else
                                <span class="badge-red">Unpaid</span>
                            @endif
                        </td>
                        <td class="text-dark-400 text-xs">{{ $sale->created_at->format('d M Y, H:i') }}</td>
                        <td class="text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('sales.show', $sale) }}" class="btn-icon" title="View Receipt">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-12">
                            <div class="w-14 h-14 rounded-2xl bg-dark-800 flex items-center justify-center mx-auto mb-3">
                                <svg class="w-7 h-7 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <p class="text-dark-500 font-medium">No sales found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($sales->hasPages())
        <div class="px-6 py-4 border-t border-dark-800">
            {{ $sales->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
