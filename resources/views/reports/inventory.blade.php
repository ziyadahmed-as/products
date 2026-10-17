@extends('layouts.app')
@section('title', 'Inventory & Stock Movement Reports')

@section('content')
<div class="space-y-6">

    <!-- Header & Action Buttons -->
    <div class="page-header flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Inventory & Stock Movement Reports</h1>
            <p class="text-gray-500 dark:text-dark-400 text-sm mt-1">
                Real-time stock balances, asset valuation, minimum threshold alerts, and recent audit movements.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="btn-secondary flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Print</span>
            </button>
            <a href="{{ route('reports.inventory.export', request()->query()) }}" class="btn-primary flex items-center gap-2 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Export CSV</span>
            </a>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-dark-400">Total Asset Valuation</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-100 dark:bg-emerald-950/80 border border-emerald-200 dark:border-emerald-800/50 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white mt-3">Br{{ number_format($totalValuation, 2) }}</div>
            <div class="text-xs text-gray-500 dark:text-dark-400 mt-1">Based on purchase cost</div>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-dark-400">Total Stock on Hand</span>
                <div class="w-9 h-9 rounded-xl bg-cyan-100 dark:bg-cyan-950/80 border border-cyan-200 dark:border-cyan-800/50 flex items-center justify-center text-cyan-600 dark:text-cyan-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white mt-3">{{ number_format($totalStockUnits, 2) }} <span class="text-sm font-normal text-gray-500 dark:text-dark-400">units</span></div>
            <div class="text-xs text-gray-500 dark:text-dark-400 mt-1">Across all authorized stores</div>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-dark-400">Low Stock Warnings</span>
                <div class="w-9 h-9 rounded-xl bg-rose-100 dark:bg-rose-950/80 border border-rose-200 dark:border-rose-800/50 flex items-center justify-center text-rose-600 dark:text-rose-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-rose-600 dark:text-rose-400 mt-3">{{ $lowStockCount }}</div>
            <div class="text-xs text-gray-500 dark:text-dark-400 mt-1">Below minimum threshold level</div>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-dark-400">Storage Locations</span>
                <div class="w-9 h-9 rounded-xl bg-indigo-100 dark:bg-indigo-950/80 border border-indigo-200 dark:border-indigo-800/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white mt-3">{{ count($filterLocations) }}</div>
            <div class="text-xs text-gray-500 dark:text-dark-400 mt-1">Active store points</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card p-5 print:hidden">
        <form method="GET" action="{{ route('reports.inventory') }}" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
                @if(!$isSeller && !empty($filterBranches))
                <div class="form-group">
                    <label class="form-label text-xs">Branch</label>
                    <select name="branch_id" class="form-input text-xs">
                        <option value="">All Branches</option>
                        @foreach($filterBranches as $bId => $bName)
                            <option value="{{ $bId }}" {{ (string)request('branch_id') === (string)$bId ? 'selected' : '' }}>
                                {{ $bName }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="form-group">
                    <label class="form-label text-xs">Storage Location</label>
                    <select name="storage_location_id" class="form-input text-xs">
                        <option value="">All Locations</option>
                        @foreach($filterLocations as $lId => $lName)
                            <option value="{{ $lId }}" {{ (string)request('storage_location_id') === (string)$lId ? 'selected' : '' }}>
                                {{ $lName }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label text-xs">Category</label>
                    <select name="category_id" class="form-input text-xs">
                        <option value="">All Categories</option>
                        @foreach($filterCategories as $cId => $cName)
                            <option value="{{ $cId }}" {{ (string)request('category_id') === (string)$cId ? 'selected' : '' }}>
                                {{ $cName }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label text-xs">Product Type</label>
                    <select name="product_type" class="form-input text-xs">
                        <option value="">All Product Types</option>
                        <option value="manufactured_product" {{ request('product_type') === 'manufactured_product' ? 'selected' : '' }}>Manufactured Product</option>
                        <option value="raw_material" {{ request('product_type') === 'raw_material' ? 'selected' : '' }}>Raw Material</option>
                        <option value="resale_product" {{ request('product_type') === 'resale_product' ? 'selected' : '' }}>Resale Product</option>
                        <option value="consumable" {{ request('product_type') === 'consumable' ? 'selected' : '' }}>Consumable</option>
                    </select>
                </div>

                <div class="form-group flex items-end">
                    <label class="flex items-center gap-2 cursor-pointer pb-2 text-xs text-gray-700 dark:text-dark-300">
                        <input type="checkbox" name="only_low_stock" value="1" {{ request('only_low_stock') ? 'checked' : '' }}
                               class="rounded border-gray-300 dark:border-dark-700 bg-white dark:bg-dark-800 text-primary-600 focus:ring-0">
                        <span class="font-medium text-amber-700 dark:text-amber-300">Show Only Low Stock</span>
                    </label>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 border-t border-gray-200 dark:border-dark-800">
                <div class="form-group flex-1 w-full">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search by product name or SKU..."
                           class="form-input text-xs">
                </div>
                <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                    <button type="submit" class="btn-primary text-xs px-4 py-2 shadow-sm">Apply Filters</button>
                    <a href="{{ route('reports.inventory') }}" class="btn-secondary text-xs px-4 py-2">Reset</a>
                </div>
            </div>
        </form>
    </div>

    <!-- Inventory Balances Table -->
    <div class="card p-0 overflow-hidden shadow-sm">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wider text-gray-500 dark:text-dark-400 bg-gray-50 dark:bg-dark-950/60 border-b border-gray-200 dark:border-dark-800">
                        <th class="py-3 px-4">Product Name</th>
                        <th class="py-3 px-4">SKU</th>
                        <th class="py-3 px-4">Type</th>
                        <th class="py-3 px-4">Category</th>
                        <th class="py-3 px-4">Location & Branch</th>
                        <th class="py-3 px-4 text-right">In Stock</th>
                        <th class="py-3 px-4 text-right">Min Level</th>
                        <th class="py-3 px-4 text-right">Unit Cost</th>
                        <th class="py-3 px-4 text-right">Valuation</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-dark-800 text-sm">
                    @forelse($balances as $bal)
                    @php
                        $prod = $bal->product;
                        $minLevel = $prod->minimum_stock_level ?? 0;
                        $unitCost = $prod->purchase_cost ?? 0;
                        $lineValuation = $bal->quantity * $unitCost;
                        $isLow = $bal->quantity <= $minLevel;
                        $isOut = $bal->quantity <= 0;
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-dark-800/40 transition">
                        <td class="py-3 px-4 font-semibold text-gray-900 dark:text-white whitespace-nowrap">
                            {{ $prod->name ?? '—' }}
                        </td>
                        <td class="py-3 px-4 font-mono text-xs text-primary-600 dark:text-primary-400 whitespace-nowrap">
                            {{ $prod->sku ?? '—' }}
                        </td>
                        <td class="py-3 px-4 text-xs whitespace-nowrap">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-dark-800 text-gray-700 dark:text-dark-300 border border-gray-200 dark:border-dark-700">
                                {{ ucwords(str_replace('_', ' ', $prod->type ?? 'Product')) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-xs text-gray-600 dark:text-dark-300 whitespace-nowrap">
                            {{ $prod->category->name ?? '—' }}
                        </td>
                        <td class="py-3 px-4 text-xs text-gray-600 dark:text-dark-300 whitespace-nowrap">
                            <div class="text-gray-900 dark:text-white font-medium">{{ $bal->storageLocation->name ?? '—' }}</div>
                            <div class="text-gray-400 dark:text-dark-500 text-xs">{{ $bal->storageLocation->branch->name ?? '' }}</div>
                        </td>
                        <td class="py-3 px-4 text-right font-bold text-gray-900 dark:text-white whitespace-nowrap">
                            {{ number_format($bal->quantity, 2) }}
                            <span class="text-xs font-normal text-gray-500 dark:text-dark-400">{{ $prod->unit->code ?? 'units' }}</span>
                        </td>
                        <td class="py-3 px-4 text-right font-mono text-xs text-gray-500 dark:text-dark-400 whitespace-nowrap">
                            {{ number_format($minLevel, 2) }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono text-xs text-gray-600 dark:text-dark-300 whitespace-nowrap">
                            Br{{ number_format($unitCost, 2) }}
                        </td>
                        <td class="py-3 px-4 text-right font-bold text-emerald-600 dark:text-emerald-400 whitespace-nowrap font-mono">
                            Br{{ number_format($lineValuation, 2) }}
                        </td>
                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            @if($isOut)
                                <span class="badge-red text-xs">Out of Stock</span>
                            @elseif($isLow)
                                <span class="badge-yellow text-xs">Low Stock</span>
                            @else
                                <span class="badge-green text-xs">In Stock</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-16 text-gray-400 dark:text-dark-500">
                            No inventory records found matching your filters.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($balances->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-dark-800 bg-gray-50 dark:bg-dark-900/60">
            {{ $balances->links() }}
        </div>
        @endif
    </div>

    <!-- Recent Stock Movements Audit Section -->
    @if($recentMovements->isNotEmpty())
    <div class="card p-0 overflow-hidden shadow-sm print:hidden">
        <div class="p-5 border-b border-gray-200 dark:border-dark-800">
            <h2 class="text-base font-bold text-gray-900 dark:text-white">Recent Stock Movement Transactions</h2>
            <p class="text-xs text-gray-500 dark:text-dark-400 mt-0.5">Audit log of inventory inflows, outflows, transfers, and manufacturing deductions.</p>
        </div>
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wider text-gray-500 dark:text-dark-400 bg-gray-50 dark:bg-dark-950/60 border-b border-gray-200 dark:border-dark-800">
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Product</th>
                        <th class="py-3 px-4">Movement Type</th>
                        <th class="py-3 px-4 text-right">Quantity</th>
                        <th class="py-3 px-4">Storage Location</th>
                        <th class="py-3 px-4">Logged By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-dark-800 text-sm">
                    @foreach($recentMovements as $mov)
                    <tr class="hover:bg-gray-50 dark:hover:bg-dark-800/40 transition">
                        <td class="py-3 px-4 text-xs text-gray-600 dark:text-dark-300 font-mono whitespace-nowrap">
                            {{ $mov->created_at->format('Y-m-d H:i') }}
                        </td>
                        <td class="py-3 px-4 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                            {{ $mov->product->name ?? '—' }}
                        </td>
                        <td class="py-3 px-4 whitespace-nowrap">
                            @if($mov->type === 'in')
                                <span class="badge-green text-xs">Inflow (Stock In)</span>
                            @elseif($mov->type === 'out')
                                <span class="badge-red text-xs">Outflow (Deduction)</span>
                            @elseif($mov->type === 'transfer')
                                <span class="badge-blue text-xs">Transfer</span>
                            @else
                                <span class="badge-gray text-xs">{{ ucfirst($mov->type) }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right font-mono font-bold {{ $mov->type === 'in' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }} whitespace-nowrap">
                            {{ $mov->type === 'in' ? '+' : '-' }}{{ number_format($mov->quantity, 2) }}
                        </td>
                        <td class="py-3 px-4 text-xs text-gray-600 dark:text-dark-300 whitespace-nowrap">
                            {{ $mov->storageLocation->name ?? '—' }}
                        </td>
                        <td class="py-3 px-4 text-xs text-gray-600 dark:text-dark-300 whitespace-nowrap">
                            {{ $mov->user->name ?? 'System' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
@endsection
