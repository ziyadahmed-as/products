@extends('layouts.app')
@section('title', 'Stock Balances')

@section('content')
<div class="space-y-6">

    <div class="page-header flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Stock Balances</h1>
            <p class="text-dark-400 text-sm mt-1">Current inventory levels, branch locations, and valuations across raw materials and products.</p>
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

    <!-- Quick Item Type Filter Tabs -->
    <div class="flex flex-wrap gap-2 text-xs">
        <a href="{{ route('inventory.index', request()->except(['type', 'page'])) }}"
           class="px-3 py-1.5 rounded-lg border font-medium transition {{ !request('type') ? 'bg-primary-600 text-white border-primary-500' : 'bg-dark-900 text-dark-300 border-dark-800 hover:border-dark-700' }}">
            All Item Types
        </a>
        <a href="{{ route('inventory.index', array_merge(request()->except(['type', 'page']), ['type' => 'raw_material'])) }}"
           class="px-3 py-1.5 rounded-lg border font-medium transition flex items-center gap-1.5 {{ request('type') === 'raw_material' ? 'bg-amber-500/20 text-amber-300 border-amber-500/40' : 'bg-dark-900 text-dark-300 border-dark-800 hover:border-dark-700' }}">
            <span class="w-2 h-2 rounded-full bg-amber-400"></span> Raw Materials
        </a>
        <a href="{{ route('inventory.index', array_merge(request()->except(['type', 'page']), ['type' => 'manufactured_product'])) }}"
           class="px-3 py-1.5 rounded-lg border font-medium transition flex items-center gap-1.5 {{ request('type') === 'manufactured_product' ? 'bg-purple-500/20 text-purple-300 border-purple-500/40' : 'bg-dark-900 text-dark-300 border-dark-800 hover:border-dark-700' }}">
            <span class="w-2 h-2 rounded-full bg-purple-400"></span> Manufactured Products
        </a>
        <a href="{{ route('inventory.index', array_merge(request()->except(['type', 'page']), ['type' => 'resale_product'])) }}"
           class="px-3 py-1.5 rounded-lg border font-medium transition flex items-center gap-1.5 {{ request('type') === 'resale_product' ? 'bg-cyan-500/20 text-cyan-300 border-cyan-500/40' : 'bg-dark-900 text-dark-300 border-dark-800 hover:border-dark-700' }}">
            <span class="w-2 h-2 rounded-full bg-cyan-400"></span> Resale Goods
        </a>
    </div>

    <!-- Filters -->
    <div class="card p-4 bg-dark-900 border border-dark-800 rounded-2xl">
        <form method="GET" action="{{ route('inventory.index') }}" class="flex flex-wrap gap-3 items-end">
            @if(request('type'))
                <input type="hidden" name="type" value="{{ request('type') }}">
            @endif
            <div class="form-group flex-1 min-w-[180px]">
                <label class="form-label text-xs">Search Item</label>
                <input name="search" value="{{ request('search') }}" type="text"
                       class="form-input text-xs" placeholder="Name, SKU...">
            </div>
            <div class="form-group min-w-[180px]">
                <label class="form-label text-xs">Storage Location</label>
                <select name="location_id" class="form-select text-xs">
                    <option value="">All Locations</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}>
                            {{ $loc->name }} ({{ $loc->branch->name ?? '' }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group min-w-[140px]">
                <label class="form-label text-xs">Stock Level</label>
                <select name="filter" class="form-select text-xs">
                    <option value="">All Balances</option>
                    <option value="low" {{ request('filter') === 'low' ? 'selected' : '' }}>Low Stock Only</option>
                    <option value="zero" {{ request('filter') === 'zero' ? 'selected' : '' }}>Out of Stock</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary text-xs px-4 py-2">Filter</button>
                <a href="{{ route('inventory.index') }}" class="btn-secondary text-xs px-3 py-2">Reset</a>
            </div>
        </form>
    </div>

    <!-- Inventory Table -->
    <div class="card p-0 bg-dark-900 border border-dark-800 rounded-2xl overflow-hidden shadow-xl">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wider text-dark-400 bg-dark-950/60 border-b border-dark-800">
                        <th class="py-3 px-4">Item Name & SKU</th>
                        <th class="py-3 px-4">Type</th>
                        <th class="py-3 px-4">Category</th>
                        <th class="py-3 px-4">Storage Location</th>
                        <th class="py-3 px-4 text-right">In Stock</th>
                        <th class="py-3 px-4 text-right">Reorder Level</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Valuation</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-800 text-sm">
                    @forelse($balances as $balance)
                    @php
                        $qty = $balance->quantity;
                        $min = $balance->product->minimum_stock_level ?? 0;
                        $isLow = $qty <= $min && $min > 0;
                        $isZero = $qty <= 0;
                        $value = $qty * ($balance->product->purchase_cost ?? 0);
                    @endphp
                    <tr class="hover:bg-dark-800/40 transition">
                        <td class="py-3 px-4">
                            <a href="{{ route('products.show', $balance->product) }}" class="font-medium text-white hover:text-primary-400 transition">
                                {{ $balance->product->name ?? '—' }}
                            </a>
                            <span class="font-mono text-xs text-primary-400/80 block mt-0.5">{{ $balance->product->sku ?? '—' }}</span>
                        </td>
                        <td class="py-3 px-4 whitespace-nowrap">
                            @if($balance->product->type === 'raw_material')
                                <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-500/15 text-amber-300 border border-amber-500/25">
                                    Raw Material
                                </span>
                            @elseif($balance->product->type === 'manufactured_product')
                                <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-purple-500/15 text-purple-300 border border-purple-500/25">
                                    Manufactured
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-cyan-500/15 text-cyan-300 border border-cyan-500/25">
                                    {{ ucwords(str_replace('_', ' ', $balance->product->type)) }}
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-xs text-dark-300 whitespace-nowrap">{{ $balance->product->category->name ?? '—' }}</td>
                        <td class="py-3 px-4 text-xs text-dark-300 whitespace-nowrap">
                            {{ $balance->storageLocation->name ?? '—' }}
                            <span class="text-dark-500 block text-[11px]">{{ $balance->storageLocation->branch->name ?? '' }}</span>
                        </td>
                        <td class="py-3 px-4 text-right font-mono text-xs whitespace-nowrap">
                            <span class="font-bold {{ $isZero ? 'text-rose-400' : ($isLow ? 'text-amber-400' : 'text-emerald-400') }}">
                                {{ number_format($qty, 2) }}
                            </span>
                            <span class="text-dark-500 text-[11px]">{{ $balance->product->unit?->code ?? '' }}</span>
                        </td>
                        <td class="py-3 px-4 text-right font-mono text-xs text-dark-400 whitespace-nowrap">{{ number_format($min, 2) }}</td>
                        <td class="py-3 px-4 whitespace-nowrap">
                            @if($isZero)
                                <span class="badge-red text-xs">Out of Stock</span>
                            @elseif($isLow)
                                <span class="badge-yellow text-xs">Low Stock</span>
                            @else
                                <span class="badge-green text-xs">Available</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right font-mono text-xs text-dark-300 whitespace-nowrap">Br {{ number_format($value, 2) }}</td>
                        <td class="py-3 px-4 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('products.show', $balance->product) }}" class="btn-icon" title="View details">
                                    <svg class="w-4 h-4 text-dark-400 hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                @hasanyrole('Super Admin|Manager')
                                @if(in_array($balance->product->type, ['raw_material', 'resale_product', 'consumable']))
                                <a href="{{ route('stock-receipts.create', ['product_id' => $balance->product->id]) }}" class="btn-icon text-emerald-400" title="Receive Stock">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </a>
                                @endif
                                @endhasanyrole
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-14 text-dark-500">
                            No stock records match the selected filters.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($balances->hasPages())
        <div class="px-6 py-4 border-t border-dark-800 bg-dark-900/60">
            {{ $balances->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
