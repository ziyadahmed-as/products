@extends('layouts.app')
@section('title', 'Return Claim #' . $saleReturn->id)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header -->
    <div class="page-header">
        <div>
            <div class="flex items-center gap-3">
                <h1>Return Claim #RET-{{ str_pad($saleReturn->id, 5, '0', STR_PAD_LEFT) }}</h1>
                @if($saleReturn->status === 'approved')
                    <span class="badge badge-green capitalize">Approved</span>
                @elseif($saleReturn->status === 'pending')
                    <span class="badge badge-yellow capitalize">Pending Review</span>
                @else
                    <span class="badge badge-red capitalize">Rejected</span>
                @endif
                @if($saleReturn->is_refund_processed)
                    <span class="badge badge-green">Refund Disbursed</span>
                @endif
            </div>
            <p class="text-slate-500 dark:text-dark-400 text-sm mt-1">Submitted on {{ $saleReturn->created_at->format('M d, Y h:i A') }}</p>
        </div>
        <div class="flex items-center gap-2">
            @if($saleReturn->status === 'pending')
            <a href="{{ route('sale-returns.edit', $saleReturn) }}" class="btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Process Claim
            </a>
            @endif
            <a href="{{ route('sale-returns.index') }}" class="btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to Returns
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="stat-card">
            <p class="text-xs uppercase font-semibold text-slate-500 dark:text-dark-400">Refund Amount</p>
            <p class="text-2xl font-bold text-slate-900 dark:text-white mt-1">${{ number_format($saleReturn->refund_amount, 2) }}</p>
            <p class="text-xs text-slate-400 mt-1">{{ $saleReturn->is_refund_processed ? 'Processed & Paid' : 'Pending payment' }}</p>
        </div>
        <div class="stat-card">
            <p class="text-xs uppercase font-semibold text-slate-500 dark:text-dark-400">Associated Sale</p>
            <p class="text-2xl font-bold text-slate-900 dark:text-white mt-1">
                @if($saleReturn->sale)
                    <a href="{{ route('sales.show', $saleReturn->sale) }}" class="text-cyan-600 hover:underline">
                        #{{ $saleReturn->sale->id }}
                    </a>
                @else
                    N/A
                @endif
            </p>
            <p class="text-xs text-slate-400 mt-1">Original Order Value: ${{ number_format($saleReturn->sale?->total_amount ?? 0, 2) }}</p>
        </div>
        <div class="stat-card">
            <p class="text-xs uppercase font-semibold text-slate-500 dark:text-dark-400">Initiated By</p>
            <p class="text-xl font-bold text-slate-900 dark:text-white mt-1">{{ $saleReturn->user?->name ?? 'System' }}</p>
            <p class="text-xs text-slate-400 mt-1">{{ $saleReturn->user?->email }}</p>
        </div>
    </div>

    <!-- Claim Reason & Notes -->
    <div class="card p-6">
        <h3 class="text-xs uppercase font-semibold text-slate-400 dark:text-dark-500 mb-2">Claim Reason & Details</h3>
        <p class="text-sm text-slate-800 dark:text-dark-200 leading-relaxed">{{ $saleReturn->reason }}</p>
    </div>

    <!-- Items in Original Sale Order -->
    @if($saleReturn->sale && $saleReturn->sale->lines->isNotEmpty())
    <div class="card overflow-hidden">
        <div class="p-4 border-b border-slate-100 dark:border-dark-800">
            <h3 class="font-semibold text-slate-900 dark:text-white">Items in Original Order #{{ $saleReturn->sale->id }}</h3>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Quantity Ordered</th>
                    <th>Unit Price</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($saleReturn->sale->lines as $line)
                <tr>
                    <td>
                        <p class="font-semibold text-slate-900 dark:text-white text-sm">{{ $line->product?->name ?? 'Item' }}</p>
                    </td>
                    <td class="font-mono text-xs text-slate-500 dark:text-dark-400">{{ $line->product?->sku ?? '—' }}</td>
                    <td class="text-sm text-slate-800 dark:text-dark-200">{{ $line->quantity }}</td>
                    <td class="text-sm text-slate-800 dark:text-dark-200">${{ number_format($line->unit_price, 2) }}</td>
                    <td class="text-sm font-semibold text-slate-900 dark:text-white">${{ number_format($line->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

</div>
@endsection
