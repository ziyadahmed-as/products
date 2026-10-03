@extends('layouts.app')
@section('title', 'Stock Balances')

@section('content')
<div class="space-y-5">

    <div class="page-header">
        <div>
            <h1>Stock Balances</h1>
            <p class="text-dark-400 text-sm mt-1">Current inventory levels across all storage locations.</p>
        </div>
        <a href="{{ route('stock-receipts.create') }}" class="btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Record Receipt
        </a>
    </div>

    <!-- Filters -->
    <div class="card">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="form-group flex-1 min-w-[160px]">
                <label class="form-label">Search Product</label>
                <input name="search" value="{{ request('search') }}" type="text"
                       class="form-input" placeholder="Name, SKU...">
            </div>
            <div class="form-group min-w-[180px]">
                <label class="form-label">Storage Location</label>
                <select name="location_id" class="form-select">
                    <option value="">All Locations</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}>
                            {{ $loc->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group min-w-[140px]">
                <label class="form-label">Show</label>
                <select name="filter" class="form-select">
                    <option value="">All Items</option>
                    <option value="low" {{ request('filter') === 'low' ? 'selected' : '' }}>Low Stock Only</option>
                    <option value="zero" {{ request('filter') === 'zero' ? 'selected' : '' }}>Zero Stock</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary">Filter</button>
                <a href="{{ route('inventory.index') }}" class="btn-secondary">Reset</a>
            </div>
        </form>
    </div>

    <!-- Inventory Table -->
    <div class="card p-0">
        <div class="table-wrapper rounded-2xl">
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>SKU</th>
                        <th>Category</th>
                        <th>Location</th>
                        <th>In Stock</th>
                        <th>Min Level</th>
                        <th>Status</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($balances as $balance)
                    @php
                        $qty = $balance->quantity;
                        $min = $balance->product->minimum_stock_level ?? 0;
                        $isLow = $qty <= $min && $min > 0;
                        $isZero = $qty <= 0;
                        $value = $qty * ($balance->product->purchase_cost ?? 0);
                    @endphp
                    <tr>
                        <td class="font-medium text-white">{{ $balance->product->name ?? '—' }}</td>
                        <td class="font-mono text-xs text-primary-400">{{ $balance->product->sku ?? '—' }}</td>
                        <td class="text-dark-300">{{ $balance->product->category->name ?? '—' }}</td>
                        <td class="text-dark-300">{{ $balance->storageLocation->name ?? '—' }}</td>
                        <td>
                            <span class="font-bold {{ $isZero ? 'text-red-400' : ($isLow ? 'text-yellow-400' : 'text-emerald-400') }}">
                                {{ number_format($qty, 2) }}
                            </span>
                        </td>
                        <td class="text-dark-400">{{ $min }}</td>
                        <td>
                            @if($isZero)
                                <span class="badge-red">Out of Stock</span>
                            @elseif($isLow)
                                <span class="badge-yellow">Low Stock</span>
                            @else
                                <span class="badge-green">OK</span>
                            @endif
                        </td>
                        <td class="text-dark-300">Br{{ number_format($value, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-12 text-dark-500">No stock records found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($balances->hasPages())
        <div class="px-6 py-4 border-t border-dark-800">
            {{ $balances->withQueryString()->links() }}
        </div>
        @endif
    </div>

</div>
@endsection

