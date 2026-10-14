@extends('layouts.app')
@section('title', 'Sale ' . $sale->reference)

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar (hidden on print) -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 print:hidden">
        <div class="flex items-center gap-3">
            <a href="{{ route('sales.index') }}" class="btn-secondary !p-2.5 rounded-xl" title="Back to Sales">
                <svg class="w-5 h-5 text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold text-white tracking-tight">{{ $sale->reference }}</h1>
                    @if($sale->payment_status === 'Paid')
                        <span class="badge-green">Paid</span>
                    @elseif($sale->payment_status === 'Partial')
                        <span class="badge-yellow">Partial</span>
                    @else
                        <span class="badge-red">Unpaid</span>
                    @endif
                    @if($sale->is_stock_deducted)
                        <span class="badge-blue">Stock Deducted</span>
                    @endif
                </div>
                <p class="text-xs text-dark-400 mt-0.5">Recorded on {{ $sale->created_at->format('F d, Y \a\t h:i A') }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto">
            <a href="{{ route('invoice.show', $sale) }}" target="_blank" class="btn-secondary flex items-center gap-2 text-sm">
                <svg class="w-4 h-4 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Full Invoice
            </a>
            <button onclick="window.print()" class="btn-primary flex items-center gap-2 text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Print Receipt
            </button>
            <a href="{{ route('sales.create') }}" class="btn-secondary flex items-center gap-2 text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                New Sale
            </a>
        </div>
    </div>

    <!-- Main Receipt Container (styled for both screen and print) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left 2 Cols: Details & Items -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Transaction Info Card -->
            <div class="card bg-dark-900/90 border border-dark-800 rounded-2xl p-6">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pb-4 border-b border-dark-800">
                    <div>
                        <span class="text-xs text-dark-400 font-medium block">Branch</span>
                        <span class="text-sm font-semibold text-white mt-1 block">{{ $sale->branch->name ?? 'Primary Branch' }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-dark-400 font-medium block">Storage Location</span>
                        <span class="text-sm font-semibold text-white mt-1 block">{{ $sale->storageLocation->name ?? 'Main Store' }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-dark-400 font-medium block">Cashier / Seller</span>
                        <span class="text-sm font-semibold text-white mt-1 block">{{ $sale->user->name ?? 'System' }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-dark-400 font-medium block">Sale Type</span>
                        <span class="text-sm font-semibold text-white mt-1 block capitalize">{{ str_replace('_', ' ', $sale->type) }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-4">
                    <div>
                        <span class="text-xs text-dark-400 font-medium block">Customer Name</span>
                        <span class="text-sm font-semibold text-white mt-1 block">{{ $sale->customer_name ?: 'Walk-in Customer' }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-dark-400 font-medium block">Customer Phone</span>
                        <span class="text-sm font-semibold text-white mt-1 block">{{ $sale->customer_phone ?: '—' }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-dark-400 font-medium block">Payment Status</span>
                        <span class="text-sm font-semibold mt-1 block {{ $sale->payment_status === 'Paid' ? 'text-emerald-400' : ($sale->payment_status === 'Partial' ? 'text-yellow-400' : 'text-red-400') }}">
                            {{ $sale->payment_status }}
                        </span>
                    </div>
                </div>

                @if($sale->order_notes)
                <div class="mt-4 pt-4 border-t border-dark-800">
                    <span class="text-xs text-dark-400 font-medium block">Order Notes:</span>
                    <p class="text-sm text-dark-300 mt-1 italic">{{ $sale->order_notes }}</p>
                </div>
                @endif
            </div>

            <!-- Items Table Card -->
            <div class="card bg-dark-900/90 border border-dark-800 rounded-2xl overflow-hidden p-0">
                <div class="px-6 py-4 border-b border-dark-800 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-white">Line Items</h3>
                    <span class="text-xs text-dark-400">{{ $sale->lines->count() }} items</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead>
                            <tr>
                                <th class="text-left">#</th>
                                <th class="text-left">Item Description</th>
                                <th class="text-center">Unit</th>
                                <th class="text-right">Qty</th>
                                <th class="text-right">Unit Price</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-dark-800">
                            @foreach($sale->lines as $idx => $line)
                            <tr>
                                <td class="text-dark-500 text-xs">{{ $idx + 1 }}</td>
                                <td>
                                    <div class="font-medium text-white">{{ $line->product->name ?? 'Product' }}</div>
                                    <div class="text-xs text-dark-400 font-mono">{{ $line->product->sku ?? '—' }}</div>
                                </td>
                                <td class="text-center text-xs text-dark-300">
                                    {{ $line->product->unit->code ?? $line->product->unit->name ?? 'unit' }}
                                </td>
                                <td class="text-right font-medium text-white">
                                    {{ number_format($line->quantity, 2) }}
                                </td>
                                <td class="text-right text-dark-300">
                                    Br{{ number_format($line->unit_price, 2) }}
                                </td>
                                <td class="text-right font-semibold text-white">
                                    Br{{ number_format($line->total, 2) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Payments History (if any) -->
            @if($sale->payments && $sale->payments->count() > 0)
            <div class="card bg-dark-900/90 border border-dark-800 rounded-2xl p-6">
                <h3 class="text-base font-semibold text-white mb-4">Payment Transactions</h3>
                <div class="space-y-3">
                    @foreach($sale->payments as $payment)
                    <div class="flex items-center justify-between p-3 rounded-xl bg-dark-950/60 border border-dark-800">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <span class="text-sm font-semibold text-white capitalize">{{ $payment->payment_method ?? 'Cash' }}</span>
                                <span class="text-xs text-dark-400 block">{{ $payment->created_at->format('M d, Y h:i A') }}</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-sm font-bold text-emerald-400">+Br{{ number_format($payment->amount, 2) }}</span>
                            @if($payment->reference)
                                <span class="text-xs text-dark-400 block font-mono">{{ $payment->reference }}</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>

        <!-- Right 1 Col: Summary & Actions -->
        <div class="space-y-6">

            <!-- Summary Card -->
            <div class="card bg-dark-900/90 border border-dark-800 rounded-2xl p-6 space-y-4">
                <h3 class="text-base font-semibold text-white pb-3 border-b border-dark-800">Financial Summary</h3>

                <div class="space-y-2.5 text-sm">
                    <div class="flex justify-between text-dark-300">
                        <span>Subtotal</span>
                        <span class="font-medium text-white">Br{{ number_format($sale->subtotal, 2) }}</span>
                    </div>

                    @if($sale->discount > 0)
                    <div class="flex justify-between text-dark-300">
                        <span>Discount</span>
                        <span class="text-emerald-400 font-medium">-Br{{ number_format($sale->discount, 2) }}</span>
                    </div>
                    @endif

                    @if($sale->tax > 0)
                    <div class="flex justify-between text-dark-300">
                        <span>Tax / VAT</span>
                        <span class="font-medium text-white">+Br{{ number_format($sale->tax, 2) }}</span>
                    </div>
                    @endif

                    <div class="pt-3 border-t border-dark-800 flex justify-between items-baseline">
                        <span class="text-base font-bold text-white">Grand Total</span>
                        <span class="text-xl font-extrabold text-primary-400">Br{{ number_format($sale->total, 2) }}</span>
                    </div>

                    <div class="flex justify-between text-dark-300 pt-2">
                        <span>Amount Paid</span>
                        <span class="font-semibold text-emerald-400">Br{{ number_format($sale->paid_amount, 2) }}</span>
                    </div>

                    @php
                        $due = max(0, (float) $sale->total - (float) $sale->paid_amount);
                    @endphp
                    @if($due > 0)
                    <div class="flex justify-between text-dark-300 pt-1">
                        <span>Balance Due</span>
                        <span class="font-bold text-red-400">Br{{ number_format($due, 2) }}</span>
                    </div>
                    @else
                    <div class="flex justify-between text-dark-300 pt-1">
                        <span>Balance Due</span>
                        <span class="font-semibold text-emerald-400">Br0.00 (Fully Paid)</span>
                    </div>
                    @endif
                </div>

                <!-- Status Progress -->
                <div class="pt-4 border-t border-dark-800">
                    <div class="flex items-center justify-between text-xs mb-1.5">
                        <span class="text-dark-400">Payment Progress</span>
                        <span class="font-semibold text-white">
                            {{ $sale->total > 0 ? round(($sale->paid_amount / $sale->total) * 100) : 100 }}%
                        </span>
                    </div>
                    <div class="w-full bg-dark-800 rounded-full h-2 overflow-hidden">
                        <div class="bg-primary-500 h-2 rounded-full transition-all duration-300"
                             style="width: {{ $sale->total > 0 ? min(100, round(($sale->paid_amount / $sale->total) * 100)) : 100 }}%">
                        </div>
                    </div>
                </div>

                <!-- Stock Deducted Notice -->
                <div class="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-start gap-3">
                    <svg class="w-5 h-5 text-emerald-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <div class="text-xs">
                        <span class="font-semibold text-emerald-300 block">Inventory Updated</span>
                        <span class="text-dark-300">Items deducted from {{ $sale->branch->name ?? 'branch' }} inventory automatically.</span>
                    </div>
                </div>
            </div>

            <!-- Quick Action Links -->
            <div class="card bg-dark-900/90 border border-dark-800 rounded-2xl p-5 space-y-3 print:hidden">
                <h4 class="text-xs font-bold text-dark-400 uppercase tracking-wider">Quick Actions</h4>
                <a href="{{ route('invoice.show', $sale) }}" target="_blank" class="w-full btn-secondary text-sm flex items-center justify-center gap-2">
                    <svg class="w-4 h-4 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    Download / View Invoice
                </a>
                <a href="{{ route('sales.create') }}" class="w-full btn-primary text-sm flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Start Another Sale
                </a>
            </div>

        </div>

    </div>

</div>

<!-- Thermal Receipt Print Styles -->
<style>
@media print {
    /* Hide layout elements */
    .sidebar, .topbar, .btn-primary, .btn-secondary, .print\:hidden {
        display: none !important;
    }
    body, html {
        background: #ffffff !important;
        color: #000000 !important;
    }
    .page-content {
        margin: 0 !important;
        padding: 0 !important;
    }
    .card {
        background: transparent !important;
        border: 1px solid #ddd !important;
        box-shadow: none !important;
        color: #000000 !important;
    }
    .table th, .table td {
        color: #000000 !important;
        border-color: #eee !important;
    }
    h1, h2, h3, h4, span, p {
        color: #000000 !important;
    }
}
</style>
@endsection
