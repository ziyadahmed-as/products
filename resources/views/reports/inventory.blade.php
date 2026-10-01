@extends('layouts.app')
@section('title', 'Inventory Report')

@section('content')
<div class="space-y-5">
    <div class="page-header">
        <div>
            <h1>Inventory Report</h1>
            <p class="text-dark-400 text-sm mt-1">Total estimated value: ${{ number_format($totalValue, 2) }}</p>
        </div>
        <button onclick="window.print()" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Print
        </button>
    </div>

    <div class="card p-0">
        <div class="table-wrapper rounded-2xl">
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Location</th>
                        <th>Qty in Stock</th>
                        <th>Unit Cost</th>
                        <th>Total Value</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($balances as $balance)
                    <tr>
                        <td class="font-medium text-white">{{ $balance->product->name ?? '—' }}</td>
                        <td class="text-dark-300">{{ $balance->product->category->name ?? '—' }}</td>
                        <td class="text-dark-300">{{ $balance->storageLocation->name ?? '—' }}</td>
                        <td class="font-bold text-white">{{ $balance->quantity }}</td>
                        <td class="text-dark-300">${{ number_format($balance->product->purchase_cost ?? 0, 2) }}</td>
                        <td class="text-dark-300">${{ number_format($balance->quantity * ($balance->product->purchase_cost ?? 0), 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-12 text-dark-500">No data available</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
