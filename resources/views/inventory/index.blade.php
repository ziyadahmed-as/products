@extends('layouts.app')
@section('title', 'Stock Balances')

@section('content')
<div class="space-y-6">

    <div class="page-header flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Stock Balances</h1>
            <p class="text-slate-500 dark:text-dark-400 text-sm mt-1">Current inventory levels, branch locations, and valuations across raw materials and products.</p>
        </div>
        <div class="flex items-center gap-3">
            @hasanyrole('Super Admin|Manager|Production Officer')
            <a href="{{ route('stock-receipts.create') }}" class="btn-primary flex items-center gap-2 shadow-xs">
                <svg class="w-4 h-4 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Receive Stock</span>
            </a>
            @endhasanyrole
        </div>
    </div>

    <!-- Quick Item Type Filter Tabs -->
    <div class="flex flex-wrap gap-2 text-xs">
        <a href="{{ route('inventory.index', request()->except(['type', 'page'])) }}"
           class="px-3 py-1.5 rounded-lg border font-medium transition {{ !request('type') ? 'bg-sky-600 text-white border-sky-600 shadow-xs' : 'bg-white dark:bg-dark-900 text-slate-600 dark:text-dark-300 border-slate-200/80 dark:border-dark-800 hover:border-slate-300 dark:hover:border-dark-700' }}">
            All Item Types
        </a>
        <a href="{{ route('inventory.index', array_merge(request()->except(['type', 'page']), ['type' => 'raw_material'])) }}"
           class="px-3 py-1.5 rounded-lg border font-medium transition flex items-center gap-1.5 {{ request('type') === 'raw_material' ? 'bg-amber-500 text-white border-amber-500 shadow-xs' : 'bg-white dark:bg-dark-900 text-slate-600 dark:text-dark-300 border-slate-200/80 dark:border-dark-800 hover:border-slate-300 dark:hover:border-dark-700' }}">
            <span class="w-2 h-2 rounded-full {{ request('type') === 'raw_material' ? 'bg-white' : 'bg-amber-500' }}"></span> Raw Materials
        </a>
        <a href="{{ route('inventory.index', array_merge(request()->except(['type', 'page']), ['type' => 'manufactured_product'])) }}"
           class="px-3 py-1.5 rounded-lg border font-medium transition flex items-center gap-1.5 {{ request('type') === 'manufactured_product' ? 'bg-purple-600 text-white border-purple-600 shadow-xs' : 'bg-white dark:bg-dark-900 text-slate-600 dark:text-dark-300 border-slate-200/80 dark:border-dark-800 hover:border-slate-300 dark:hover:border-dark-700' }}">
            <span class="w-2 h-2 rounded-full {{ request('type') === 'manufactured_product' ? 'bg-white' : 'bg-purple-500' }}"></span> Manufactured Products
        </a>
        <a href="{{ route('inventory.index', array_merge(request()->except(['type', 'page']), ['type' => 'resale_product'])) }}"
           class="px-3 py-1.5 rounded-lg border font-medium transition flex items-center gap-1.5 {{ request('type') === 'resale_product' ? 'bg-cyan-600 text-white border-cyan-600 shadow-xs' : 'bg-white dark:bg-dark-900 text-slate-600 dark:text-dark-300 border-slate-200/80 dark:border-dark-800 hover:border-slate-300 dark:hover:border-dark-700' }}">
            <span class="w-2 h-2 rounded-full {{ request('type') === 'resale_product' ? 'bg-white' : 'bg-cyan-500' }}"></span> Resale Goods
        </a>
    </div>

    <!-- Filters -->
    <div class="card p-4">
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

    {{-- Inventory Table --}}
    <div class="card p-0 overflow-hidden shadow-xs">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-dark-800 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-900 dark:text-white">Stock Balances</h2>
                <p class="text-xs text-slate-500 dark:text-dark-400 mt-0.5">{{ $balances->total() }} record{{ $balances->total() !== 1 ? 's' : '' }} found</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Item Name & SKU</th>
                        <th>Type</th>
                        <th>Category</th>
                        <th>Storage Location</th>
                        <th class="text-right">In Stock</th>
                        <th class="text-right">Reorder Level</th>
                        <th>Status</th>
                        <th class="text-right">Valuation</th>
                        <th class="text-right">Actions</th>
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
                        <td>
                            <a href="{{ route('products.show', $balance->product) }}" class="font-semibold text-slate-900 dark:text-white hover:text-sky-600 dark:hover:text-sky-400 transition">
                                {{ $balance->product->name ?? '—' }}
                            </a>
                            <span class="font-mono text-xs text-sky-600 dark:text-sky-400 block mt-0.5">{{ $balance->product->sku ?? '—' }}</span>
                        </td>
                        <td class="whitespace-nowrap">
                            @if($balance->product->type === 'raw_material')
                                <span class="badge badge-yellow text-[11px] font-semibold">Raw Material</span>
                            @elseif($balance->product->type === 'manufactured_product')
                                <span class="badge badge-purple text-[11px] font-semibold">Manufactured</span>
                            @else
                                <span class="badge badge-blue text-[11px] font-semibold">{{ ucwords(str_replace('_', ' ', $balance->product->type)) }}</span>
                            @endif
                        </td>
                        <td class="text-xs text-slate-600 dark:text-dark-300 whitespace-nowrap">{{ $balance->product->category->name ?? '—' }}</td>
                        <td class="text-xs text-slate-600 dark:text-dark-300 whitespace-nowrap">
                            {{ $balance->storageLocation->name ?? '—' }}
                            <span class="text-slate-400 dark:text-dark-500 block text-[11px]">{{ $balance->storageLocation->branch->name ?? '' }}</span>
                        </td>
                        <td class="text-right font-mono text-xs whitespace-nowrap">
                            <span class="font-bold {{ $isZero ? 'text-rose-600 dark:text-rose-400' : ($isLow ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400') }}">
                                {{ number_format($qty, 2) }}
                            </span>
                            <span class="text-slate-400 dark:text-dark-500 text-[11px]">{{ $balance->product->unit?->code ?? '' }}</span>
                        </td>
                        <td class="text-right font-mono text-xs text-slate-500 dark:text-dark-400 whitespace-nowrap">{{ number_format($min, 2) }}</td>
                        <td class="whitespace-nowrap">
                            @if($isZero)
                                <span class="badge badge-red text-xs">Out of Stock</span>
                            @elseif($isLow)
                                <span class="badge badge-yellow text-xs">Low Stock</span>
                            @else
                                <span class="badge badge-green text-xs">Available</span>
                            @endif
                        </td>
                        <td class="text-right font-mono text-xs text-slate-700 dark:text-dark-300 whitespace-nowrap">Br {{ number_format($value, 2) }}</td>
                        <td class="text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('products.show', $balance->product) }}" class="btn-icon" title="View details">
                                    <svg class="w-4 h-4 text-slate-500 dark:text-dark-400 hover:text-slate-900 dark:hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                @hasanyrole('Super Admin|Manager')
                                @if(in_array($balance->product->type, ['raw_material', 'resale_product', 'consumable']))
                                <a href="{{ route('stock-receipts.create', ['product_id' => $balance->product->id]) }}" class="btn-icon text-emerald-600 dark:text-emerald-400 hover:text-emerald-700" title="Receive Stock">
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
                        <td colspan="9" class="py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-dark-800 flex items-center justify-center">
                                    <svg class="w-7 h-7 text-slate-400 dark:text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/></svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-700 dark:text-dark-300">No stock records found</p>
                                    <p class="text-xs text-slate-400 dark:text-dark-500 mt-0.5">Try adjusting your filters or receive new stock.</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($balances->hasPages())
        <div class="px-5 py-4 border-t border-slate-100 dark:border-dark-800 bg-slate-50/50 dark:bg-dark-900/40">
            {{ $balances->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
