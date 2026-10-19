@extends('layouts.app')
@section('title', 'Product & Raw Material Inventory')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="page-header flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Product & Raw Material Inventory</h1>
            <p class="text-slate-500 dark:text-dark-400 text-sm mt-1">Manage catalog items, register new raw materials, and oversee available stock.</p>
        </div>
        <div class="flex items-center gap-2">
            @hasanyrole('Super Admin|Manager')
            <a href="{{ route('stock-receipts.create') }}" class="btn-secondary text-xs flex items-center gap-1.5 py-2 px-3">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                </svg>
                <span>Receive Stock</span>
            </a>

            <!-- Registration Dropdown -->
            <div x-data="{ openReg: false }" class="relative">
                <button @click="openReg = !openReg" type="button" class="btn-primary text-xs flex items-center gap-2 py-2 px-3 shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Register New Item</span>
                    <svg class="w-3.5 h-3.5 ml-1 transition-transform" :class="{ 'rotate-180': openReg }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-show="openReg" @click.away="openReg = false" x-cloak
                     class="absolute right-0 mt-2 w-64 rounded-xl bg-white dark:bg-dark-900 border border-slate-200 dark:border-dark-800 shadow-xl z-50 p-1.5 space-y-1">
                    <a href="{{ route('raw-materials.create') }}"
                       class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-amber-50 dark:hover:bg-amber-950/40 text-slate-700 dark:text-dark-200 transition">
                        <span class="w-7 h-7 rounded-lg bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xs font-bold shrink-0">🌾</span>
                        <div>
                            <div class="text-xs font-bold text-slate-900 dark:text-white">Raw Material</div>
                            <div class="text-[10px] text-slate-400">Ingredients, chemicals & packaging</div>
                        </div>
                    </a>

                    <a href="{{ route('manufactured-products.create') }}"
                       class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-purple-50 dark:hover:bg-purple-950/40 text-slate-700 dark:text-dark-200 transition">
                        <span class="w-7 h-7 rounded-lg bg-purple-100 dark:bg-purple-900/40 text-purple-600 dark:text-purple-400 flex items-center justify-center text-xs font-bold shrink-0">🏭</span>
                        <div>
                            <div class="text-xs font-bold text-slate-900 dark:text-white">Manufactured Product</div>
                            <div class="text-[10px] text-slate-400">In-house produced finished goods</div>
                        </div>
                    </a>

                    <a href="{{ route('resale-products.create') }}"
                       class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-cyan-50 dark:hover:bg-cyan-950/40 text-slate-700 dark:text-dark-200 transition">
                        <span class="w-7 h-7 rounded-lg bg-cyan-100 dark:bg-cyan-900/40 text-cyan-600 dark:text-cyan-400 flex items-center justify-center text-xs font-bold shrink-0">🛒</span>
                        <div>
                            <div class="text-xs font-bold text-slate-900 dark:text-white">Resale Goods</div>
                            <div class="text-[10px] text-slate-400">Ready-made items for direct sale</div>
                        </div>
                    </a>

                    <div class="border-t border-slate-100 dark:border-dark-800 my-1"></div>

                    <a href="{{ route('products.create', ['type' => 'consumable']) }}"
                       class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-dark-800 text-slate-700 dark:text-dark-200 transition">
                        <span class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-dark-800 text-slate-600 dark:text-slate-300 flex items-center justify-center text-xs font-bold shrink-0">📦</span>
                        <div>
                            <div class="text-xs font-bold text-slate-900 dark:text-white">Consumable Item</div>
                            <div class="text-[10px] text-slate-400">Factory & lab supplies</div>
                        </div>
                    </a>
                </div>
            </div>
            @endhasanyrole
        </div>
    </div>

    <!-- Quick Stat Badges -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
        <a href="{{ route('products.index') }}"
           class="p-4 rounded-xl bg-white dark:bg-dark-900 border transition shadow-xs {{ !request('type') && !request('stock_status') ? 'border-sky-500 shadow-sky-500/10' : 'border-slate-200/80 dark:border-dark-800 hover:border-slate-300 dark:hover:border-dark-700' }}">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-dark-400">All Items</span>
            <div class="text-xl font-bold text-slate-900 dark:text-white mt-1">{{ number_format($counts['all']) }}</div>
        </a>
        <a href="{{ route('raw-materials.index') }}"
           class="p-4 rounded-xl bg-white dark:bg-dark-900 border transition shadow-xs {{ request('type') === 'raw_material' ? 'border-amber-500 shadow-amber-500/10' : 'border-slate-200/80 dark:border-dark-800 hover:border-slate-300 dark:hover:border-dark-700' }}">
            <span class="text-xs font-semibold uppercase tracking-wider text-amber-600 dark:text-amber-400 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span> Raw Materials
            </span>
            <div class="text-xl font-bold text-slate-900 dark:text-white mt-1">{{ number_format($counts['raw_material']) }}</div>
        </a>
        <a href="{{ route('manufactured-products.index') }}"
           class="p-4 rounded-xl bg-white dark:bg-dark-900 border transition shadow-xs {{ request('type') === 'manufactured_product' ? 'border-purple-500 shadow-purple-500/10' : 'border-slate-200/80 dark:border-dark-800 hover:border-slate-300 dark:hover:border-dark-700' }}">
            <span class="text-xs font-semibold uppercase tracking-wider text-purple-600 dark:text-purple-400 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-purple-500"></span> Manufactured
            </span>
            <div class="text-xl font-bold text-slate-900 dark:text-white mt-1">{{ number_format($counts['manufactured']) }}</div>
        </a>
        <a href="{{ route('resale-products.index') }}"
           class="p-4 rounded-xl bg-white dark:bg-dark-900 border transition shadow-xs {{ request('type') === 'resale_product' ? 'border-cyan-500 shadow-cyan-500/10' : 'border-slate-200/80 dark:border-dark-800 hover:border-slate-300 dark:hover:border-dark-700' }}">
            <span class="text-xs font-semibold uppercase tracking-wider text-cyan-600 dark:text-cyan-400 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-cyan-500"></span> Resale Goods
            </span>
            <div class="text-xl font-bold text-slate-900 dark:text-white mt-1">{{ number_format($counts['resale']) }}</div>
        </a>
        <a href="{{ route('products.index', ['stock_status' => 'low']) }}"
           class="p-4 rounded-xl bg-white dark:bg-dark-900 border transition shadow-xs {{ request('stock_status') === 'low' ? 'border-rose-500 shadow-rose-500/10' : 'border-slate-200/80 dark:border-dark-800 hover:border-slate-300 dark:hover:border-dark-700' }}">
            <span class="text-xs font-semibold uppercase tracking-wider text-rose-600 dark:text-rose-400 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span> Low Stock Alert
            </span>
            <div class="text-xl font-bold text-rose-600 dark:text-rose-400 mt-1">{{ number_format($counts['low_stock']) }}</div>
        </a>
    </div>

    <!-- Filters -->
    <div class="card p-4">
        <form method="GET" action="{{ route('products.index') }}" class="flex flex-wrap gap-3 items-end">
            <div class="form-group flex-1 min-w-[200px]">
                <label class="form-label text-xs">Search Products / Materials</label>
                <input name="search" value="{{ request('search') }}" type="text"
                       class="form-input text-xs" placeholder="Search by name, SKU, or specification...">
            </div>
            <div class="form-group min-w-[150px]">
                <label class="form-label text-xs">Category</label>
                <select name="category_id" class="form-select text-xs">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group min-w-[140px]">
                <label class="form-label text-xs">Item Type</label>
                <select name="type" class="form-select text-xs">
                    <option value="">All Types</option>
                    <option value="raw_material"        {{ request('type') === 'raw_material' ? 'selected' : '' }}>Raw Material</option>
                    <option value="manufactured_product"{{ request('type') === 'manufactured_product' ? 'selected' : '' }}>Manufactured Product</option>
                    <option value="resale_product"      {{ request('type') === 'resale_product' ? 'selected' : '' }}>Resale Product</option>
                    <option value="consumable"          {{ request('type') === 'consumable' ? 'selected' : '' }}>Consumable</option>
                </select>
            </div>
            <div class="form-group min-w-[120px]">
                <label class="form-label text-xs">Status</label>
                <select name="status" class="form-select text-xs">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary text-xs px-4 py-2">Filter</button>
                <a href="{{ route('products.index') }}" class="btn-secondary text-xs px-3 py-2">Reset</a>
            </div>
        </form>
    </div>

    {{-- ── Product Table ── --}}
    <div class="card p-0 overflow-hidden shadow-xs">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-dark-800 flex items-center justify-between flex-wrap gap-2">
            <div>
                <h2 class="text-sm font-bold text-slate-900 dark:text-white">Inventory Catalog</h2>
                <p class="text-xs text-slate-500 dark:text-dark-400 mt-0.5">{{ $products->total() }} item{{ $products->total() !== 1 ? 's' : '' }} found</p>
            </div>
            @hasanyrole('Super Admin|Manager')
            <div class="flex items-center gap-2">
                <a href="{{ route('stock-receipts.create') }}" class="btn-secondary text-xs flex items-center gap-1.5 py-1.5">
                    <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                    Receive Stock
                </a>
            </div>
            @endhasanyrole
        </div>
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Item & Code</th>
                        <th>Type</th>
                        <th>Category</th>
                        <th>Unit</th>
                        <th class="text-right">In Stock</th>
                        <th class="text-right">Min Stock</th>
                        <th class="text-right">Cost Price</th>
                        <th class="text-right">Selling Price</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr>
                        <td>
                            <div class="flex items-center gap-2.5">
                                @if($product->image && in_array($product->type, ['resale_product', 'manufactured_product']))
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-9 h-9 rounded-lg object-cover border border-slate-200 dark:border-dark-700 shrink-0 shadow-2xs">
                                @endif
                                <div class="min-w-0">
                                    <a href="{{ route('products.show', $product) }}" class="font-semibold text-slate-900 dark:text-white hover:text-sky-600 dark:hover:text-sky-400 transition flex items-center gap-2">
                                        <span class="truncate">{{ $product->name }}</span>
                                    </a>
                                    <span class="font-mono text-xs text-sky-600 dark:text-sky-400 block mt-0.5">{{ $product->sku }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($product->type === 'raw_material')
                                <span class="badge badge-yellow text-xs">Raw Material</span>
                            @elseif($product->type === 'manufactured_product')
                                <span class="badge badge-purple text-xs">Manufactured</span>
                            @elseif($product->type === 'resale_product')
                                <span class="badge badge-blue text-xs">Resale</span>
                            @else
                                <span class="badge badge-gray text-xs">Consumable</span>
                            @endif
                        </td>
                        <td class="text-slate-600 dark:text-dark-300 text-xs">
                            {{ $product->category->name ?? '—' }}
                        </td>
                        <td class="text-slate-600 dark:text-dark-300 text-xs">
                            {{ $product->unit->name ?? $product->unit->code ?? '—' }}
                        </td>
                        <td class="text-right font-mono text-xs">
                            @php
                                $isLow = $product->minimum_stock_level > 0 && $product->quantity <= $product->minimum_stock_level;
                                $isOut = $product->quantity <= 0;
                            @endphp
                            <span class="font-bold {{ $isOut ? 'text-rose-600 dark:text-rose-400' : ($isLow ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400') }}">
                                {{ number_format($product->quantity, 2) }}
                            </span>
                            <span class="text-slate-500 dark:text-dark-500 text-[11px]">{{ $product->unit?->code ?? '' }}</span>
                        </td>
                        <td class="text-right font-mono text-xs text-slate-500 dark:text-dark-400">
                            {{ number_format($product->minimum_stock_level, 2) }}
                        </td>
                        <td class="text-right font-mono text-xs text-slate-600 dark:text-dark-300">
                            Br {{ number_format($product->purchase_cost ?? 0, 2) }}
                        </td>
                        <td class="text-right font-mono text-xs">
                            @if(in_array($product->type, ['resale_product', 'manufactured_product']))
                                <span class="font-semibold text-slate-900 dark:text-white">Br {{ number_format($product->selling_price ?? 0, 2) }}</span>
                            @else
                                <span class="text-slate-400 dark:text-dark-500">—</span>
                            @endif
                        </td>
                        <td>
                            @if($product->is_active)
                                <span class="badge badge-green text-xs">Active</span>
                            @else
                                <span class="badge badge-gray text-xs">Inactive</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('products.show', $product) }}" class="btn-icon" title="View details & stock">
                                    <svg class="w-4 h-4 text-slate-500 dark:text-dark-400 hover:text-slate-900 dark:hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>

                                @hasanyrole('Super Admin|Manager')
                                @if(in_array($product->type, ['raw_material', 'resale_product', 'consumable']))
                                <a href="{{ route('stock-receipts.create', ['product_id' => $product->id]) }}"
                                   class="btn-icon text-emerald-600 dark:text-emerald-400 hover:text-emerald-700" title="Receive Stock">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </a>
                                @endif

                                <a href="{{ route('products.edit', $product) }}" class="btn-icon" title="Edit details">
                                    <svg class="w-4 h-4 text-slate-500 dark:text-dark-400 hover:text-slate-900 dark:hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                @endhasanyrole
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-16 text-slate-400 dark:text-dark-500">
                            <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-dark-800 flex items-center justify-center mx-auto mb-3">
                                <svg class="w-7 h-7 text-slate-400 dark:text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                                </svg>
                            </div>
                            <p class="font-medium text-slate-600 dark:text-dark-400">No products or raw materials found</p>
                            <p class="text-xs text-slate-400 dark:text-dark-600 mt-1">Try adjusting your search criteria or register a new item.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())
        <div class="px-5 py-4 border-t border-slate-100 dark:border-dark-800 bg-slate-50/50 dark:bg-dark-900/40">
            {{ $products->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
