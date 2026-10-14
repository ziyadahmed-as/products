@extends('layouts.app')
@section('title', 'Stock Receipt — ' . $receipt->reference)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header -->
    <div class="page-header flex flex-col sm:flex-row sm:items-center justify-between gap-4 print:hidden">
        <div class="flex items-center gap-3">
            <a href="{{ route('stock-receipts.index') }}" class="p-2 rounded-xl bg-dark-900 border border-dark-800 text-dark-400 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold text-white tracking-tight">Receipt #{{ $receipt->reference }}</h1>
                    <span class="badge-green text-xs font-semibold">Processed</span>
                </div>
                <p class="text-dark-400 text-xs mt-0.5">Recorded on {{ \Carbon\Carbon::parse($receipt->date)->format('F d, Y') }} by {{ $receipt->user->name ?? 'Staff' }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            <button onclick="window.print()" class="btn-secondary text-xs px-4 py-2 flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print Slip
            </button>
            @hasanyrole('Super Admin|Manager')
            <a href="{{ route('stock-receipts.create') }}" class="btn-primary text-xs px-4 py-2 flex items-center gap-1.5 shadow-lg shadow-primary-600/30">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New Receipt
            </a>
            @endhasanyrole
        </div>
    </div>

    <!-- Receipt Details Card -->
    <div class="card p-6 bg-dark-900 border border-dark-800 rounded-2xl shadow-xl">
        <div class="border-b border-dark-800 pb-5 mb-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-dark-500">Destination Location</span>
                <p class="text-base font-bold text-white mt-1">{{ $receipt->storageLocation->name ?? 'Main Storage' }}</p>
                <p class="text-xs text-dark-400 font-medium">Branch: {{ $receipt->storageLocation->branch->name ?? 'Central' }}</p>
            </div>
            <div class="sm:text-right">
                <span class="text-xs font-semibold uppercase tracking-wider text-dark-500">Total Receipt Valuation</span>
                <p class="text-2xl font-bold text-emerald-400 font-mono mt-0.5">Br {{ number_format($receipt->total_cost, 2) }}</p>
                <span class="text-xs text-dark-400 font-mono">{{ $receipt->stockMovements->count() }} item lines received</span>
            </div>
        </div>

        @if($receipt->notes)
        <div class="p-3.5 rounded-xl bg-dark-950/60 border border-dark-800 mb-5 text-xs text-dark-300">
            <span class="font-bold text-dark-400 uppercase tracking-wider text-[10px] block mb-1">Receipt Remarks & Documentation</span>
            <p class="whitespace-pre-line">{{ $receipt->notes }}</p>
        </div>
        @endif

        <!-- Inbound Stock Lines -->
        <h2 class="text-sm font-bold text-white uppercase tracking-wider mb-3">Itemized Inventory Intake</h2>
        <div class="table-wrapper rounded-xl border border-dark-800">
            <table class="table">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wider text-dark-400 bg-dark-950/70 border-b border-dark-800">
                        <th class="py-3 px-4">Item Name & SKU</th>
                        <th class="py-3 px-4">Type</th>
                        <th class="py-3 px-4 text-right">Quantity Received</th>
                        <th class="py-3 px-4 text-right">Unit Cost</th>
                        <th class="py-3 px-4 text-right">Line Valuation</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-800 text-sm">
                    @forelse($receipt->stockMovements as $mov)
                    <tr class="hover:bg-dark-800/40 transition">
                        <td class="py-3 px-4">
                            <a href="{{ route('products.show', $mov->product) }}" class="font-medium text-white hover:text-primary-400 transition">
                                {{ $mov->product->name ?? 'Item' }}
                            </a>
                            <span class="font-mono text-xs text-primary-400/80 block mt-0.5">{{ $mov->product->sku ?? '' }}</span>
                        </td>
                        <td class="py-3 px-4 whitespace-nowrap">
                            @if($mov->product->type === 'raw_material')
                                <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-500/15 text-amber-300 border border-amber-500/25">
                                    Raw Material
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-purple-500/15 text-purple-300 border border-purple-500/25">
                                    {{ ucwords(str_replace('_', ' ', $mov->product->type)) }}
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right font-mono text-xs font-bold text-emerald-400 whitespace-nowrap">
                            +{{ number_format($mov->quantity, 2) }}
                            <span class="text-dark-400 font-normal text-[11px]">{{ $mov->product->unit?->code ?? '' }}</span>
                        </td>
                        <td class="py-3 px-4 text-right font-mono text-xs text-dark-300 whitespace-nowrap">
                            Br {{ number_format($mov->unit_cost, 2) }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono text-xs font-bold text-white whitespace-nowrap">
                            Br {{ number_format($mov->quantity * $mov->unit_cost, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-6 text-dark-500 text-xs">
                            No movements found for this receipt.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-dark-950/70 border-t border-dark-800">
                        <td colspan="4" class="py-3 px-4 text-right text-xs font-bold uppercase tracking-wider text-dark-400">
                            Total Valuation:
                        </td>
                        <td class="py-3 px-4 text-right font-mono text-sm font-bold text-emerald-400">
                            Br {{ number_format($receipt->total_cost, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="mt-6 pt-4 border-t border-dark-800 flex items-center justify-between text-xs text-dark-500">
            <span>Verified and recorded in Albareck Inventory Ledger</span>
            <span class="font-mono">Created: {{ $receipt->created_at->format('Y-m-d H:i:s') }}</span>
        </div>
    </div>

</div>
@endsection
