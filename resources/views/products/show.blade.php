@extends('layouts.app')
@section('title', $product->name . ' — Details & Stock')

@section('content')
<div class="space-y-6">

    <!-- Header & Action Buttons -->
    <div class="page-header flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('products.index') }}" class="p-2 rounded-xl bg-dark-900 border border-dark-800 text-dark-400 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold text-white tracking-tight">{{ $product->name }}</h1>
                    @if($product->type === 'raw_material')
                        <span class="px-2 py-0.5 rounded text-xs font-semibold bg-amber-500/15 text-amber-300 border border-amber-500/25">
                            Raw Material
                        </span>
                    @elseif($product->type === 'manufactured_product')
                        <span class="px-2 py-0.5 rounded text-xs font-semibold bg-purple-500/15 text-purple-300 border border-purple-500/25">
                            Manufactured
                        </span>
                    @elseif($product->type === 'resale_product')
                        <span class="px-2 py-0.5 rounded text-xs font-semibold bg-cyan-500/15 text-cyan-300 border border-cyan-500/25">
                            Resale
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded text-xs font-semibold bg-dark-700 text-dark-300">
                            Consumable
                        </span>
                    @endif

                    @if($product->is_active)
                        <span class="badge-green text-xs">Active</span>
                    @else
                        <span class="badge-gray text-xs">Inactive</span>
                    @endif
                </div>
                <p class="text-dark-400 text-xs mt-0.5 font-mono">SKU: {{ $product->sku }} • Unit: {{ $product->unit->name ?? $product->unit->code ?? 'units' }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            @hasanyrole('Super Admin|Manager')
            @if(in_array($product->type, ['raw_material', 'resale_product', 'consumable']))
            <a href="{{ route('stock-receipts.create', ['product_id' => $product->id]) }}"
               class="btn-primary text-xs px-4 py-2 flex items-center gap-1.5 shadow-lg shadow-primary-600/30">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                </svg>
                Receive Stock
            </a>
            @endif

            @if($product->type === 'manufactured_product' || $product->recipe)
            <a href="{{ route('manufacturing-orders.create', ['product_id' => $product->id]) }}"
               class="btn-secondary text-xs px-4 py-2 flex items-center gap-1.5 border-purple-500/40 text-purple-300 hover:bg-purple-950/30">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Create MO
            </a>
            @endif

            <a href="{{ route('products.edit', $product) }}" class="btn-secondary text-xs px-4 py-2 flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit
            </a>
            @endhasanyrole
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card p-5 bg-dark-900 border border-dark-800 rounded-2xl">
            <span class="text-xs font-semibold uppercase tracking-wider text-dark-400">Total Stock Available</span>
            <div class="text-2xl font-bold font-mono mt-2 {{ $product->quantity <= 0 ? 'text-rose-400' : ($product->minimum_stock_level > 0 && $product->quantity <= $product->minimum_stock_level ? 'text-amber-400' : 'text-emerald-400') }}">
                {{ number_format($product->quantity, 2) }}
                <span class="text-xs font-normal text-dark-400">{{ $product->unit?->code ?? 'units' }}</span>
            </div>
            <div class="text-xs text-dark-500 mt-1">Reorder threshold: {{ number_format($product->minimum_stock_level, 2) }}</div>
        </div>

        <div class="card p-5 bg-dark-900 border border-dark-800 rounded-2xl">
            <span class="text-xs font-semibold uppercase tracking-wider text-dark-400">Inventory Valuation</span>
            <div class="text-2xl font-bold text-white mt-2 font-mono">
                Br {{ number_format($product->quantity * ($product->purchase_cost ?? 0), 2) }}
            </div>
            <div class="text-xs text-dark-500 mt-1">Cost @ Br {{ number_format($product->purchase_cost ?? 0, 2) }} / unit</div>
        </div>

        <div class="card p-5 bg-dark-900 border border-dark-800 rounded-2xl">
            <span class="text-xs font-semibold uppercase tracking-wider text-dark-400">Selling Price</span>
            <div class="text-2xl font-bold text-cyan-400 mt-2 font-mono">
                @if(in_array($product->type, ['resale_product', 'manufactured_product']))
                    Br {{ number_format($product->selling_price ?? 0, 2) }}
                @else
                    <span class="text-dark-500 text-base">Not for direct sale</span>
                @endif
            </div>
            <div class="text-xs text-dark-500 mt-1">
                @if($product->purchase_cost > 0 && $product->selling_price > 0)
                    Margin: {{ round((($product->selling_price - $product->purchase_cost) / $product->selling_price) * 100, 1) }}%
                @else
                    Standard material
                @endif
            </div>
        </div>

        <div class="card p-5 bg-dark-900 border border-dark-800 rounded-2xl">
            <span class="text-xs font-semibold uppercase tracking-wider text-dark-400">Category & Packaging</span>
            <div class="text-base font-bold text-white mt-2 truncate">
                {{ $product->category->name ?? 'Uncategorized' }}
            </div>
            <div class="text-xs text-dark-400 mt-1 truncate">
                {{ $product->package_size ?? 'Standard loose / bulk' }}
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Column 1 & 2: Stock Balances by Storage Location & Movement History -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Stock Per Location Table -->
            <div class="card p-0 bg-dark-900 border border-dark-800 rounded-2xl overflow-hidden shadow-xl">
                <div class="p-5 border-b border-dark-800 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-white">Stock by Storage Location / Branch</h2>
                        <p class="text-xs text-dark-400 mt-0.5">Physical distribution of available quantities across branches.</p>
                    </div>
                    @hasanyrole('Super Admin|Manager')
                    <a href="{{ route('stock-receipts.create', ['product_id' => $product->id]) }}" class="text-xs text-primary-400 hover:text-primary-300 font-semibold flex items-center gap-1">
                        + Add Stock Receipt
                    </a>
                    @endhasanyrole
                </div>
                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-wider text-dark-400 bg-dark-950/60 border-b border-dark-800">
                                <th class="py-3 px-4">Storage Location</th>
                                <th class="py-3 px-4">Branch</th>
                                <th class="py-3 px-4 text-right">Quantity In Hand</th>
                                <th class="py-3 px-4 text-right">Location Value</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-dark-800 text-sm">
                            @forelse($product->inventoryBalances as $balance)
                            <tr class="hover:bg-dark-800/40 transition">
                                <td class="py-3 px-4 font-medium text-white">
                                    {{ $balance->storageLocation->name ?? 'Main Storage' }}
                                </td>
                                <td class="py-3 px-4 text-xs text-dark-300">
                                    {{ $balance->storageLocation->branch->name ?? '—' }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono text-xs font-bold text-emerald-400">
                                    {{ number_format($balance->quantity, 2) }} {{ $product->unit?->code ?? '' }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono text-xs text-dark-300">
                                    Br {{ number_format($balance->quantity * ($product->purchase_cost ?? 0), 2) }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-8 text-dark-500 text-xs">
                                    No stock balances recorded yet. Receive stock to initialize inventory.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Movement History -->
            <div class="card p-0 bg-dark-900 border border-dark-800 rounded-2xl overflow-hidden shadow-xl">
                <div class="p-5 border-b border-dark-800 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-white">Inventory Movement History</h2>
                        <p class="text-xs text-dark-400 mt-0.5">Chronological record of receipts, manufacturing deductions, and returns.</p>
                    </div>
                    <span class="text-xs text-dark-500 font-mono">Last 20 transactions</span>
                </div>
                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-wider text-dark-400 bg-dark-950/60 border-b border-dark-800">
                                <th class="py-3 px-4">Date</th>
                                <th class="py-3 px-4">Type</th>
                                <th class="py-3 px-4">Location</th>
                                <th class="py-3 px-4 text-right">Quantity</th>
                                <th class="py-3 px-4">Reference</th>
                                <th class="py-3 px-4">Staff</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-dark-800 text-sm">
                            @forelse($product->stockMovements as $mov)
                            <tr class="hover:bg-dark-800/40 transition">
                                <td class="py-3 px-4 font-mono text-xs text-dark-400 whitespace-nowrap">
                                    {{ $mov->created_at->format('Y-m-d H:i') }}
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    @if($mov->type === 'in')
                                        <span class="badge-green text-[11px] font-semibold">+ IN</span>
                                    @elseif($mov->type === 'out')
                                        <span class="badge-red text-[11px] font-semibold">- OUT</span>
                                    @else
                                        <span class="badge-yellow text-[11px] font-semibold">{{ strtoupper($mov->type) }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-xs text-dark-300 whitespace-nowrap">
                                    {{ $mov->storageLocation->name ?? 'Store' }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono text-xs font-bold whitespace-nowrap {{ $mov->type === 'in' ? 'text-emerald-400' : 'text-rose-400' }}">
                                    {{ $mov->type === 'in' ? '+' : '-' }}{{ number_format($mov->quantity, 2) }}
                                </td>
                                <td class="py-3 px-4 font-mono text-xs text-primary-400 whitespace-nowrap">
                                    @if($mov->reference_type === \App\Models\StockReceipt::class || $mov->reference_type === 'stock_receipt')
                                        Receipt #{{ $mov->reference_id }}
                                    @elseif($mov->reference_type === \App\Models\ManufacturingOrder::class || $mov->reference_type === 'manufacturing_order')
                                        MO #{{ $mov->reference_id }}
                                    @else
                                        {{ class_basename($mov->reference_type) }} #{{ $mov->reference_id }}
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-xs text-dark-400 whitespace-nowrap">
                                    {{ $mov->user->name ?? 'System' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-8 text-dark-500 text-xs">
                                    No stock movements recorded yet.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Column 3: Master Details & Specifications Sidebar -->
        <div class="space-y-6">

            <div class="card p-5 bg-dark-900 border border-dark-800 rounded-2xl space-y-4">
                <h2 class="text-sm font-bold text-white uppercase tracking-wider border-b border-dark-800 pb-2">
                    Master Specifications
                </h2>

                <div class="space-y-3 text-xs">
                    <div>
                        <span class="text-dark-500 block">Item Code (SKU)</span>
                        <span class="font-mono text-sm font-bold text-primary-400">{{ $product->sku }}</span>
                    </div>

                    <div>
                        <span class="text-dark-500 block">Unit of Measure</span>
                        <span class="text-white font-medium">{{ $product->unit->name ?? '—' }} ({{ $product->unit->code ?? '—' }})</span>
                    </div>

                    <div>
                        <span class="text-dark-500 block">Package Size</span>
                        <span class="text-white font-medium">{{ $product->package_size ?? 'Not specified' }}</span>
                    </div>

                    <div>
                        <span class="text-dark-500 block">Assigned Category</span>
                        <span class="text-white font-medium">{{ $product->category->name ?? 'None' }}</span>
                    </div>

                    <div>
                        <span class="text-dark-500 block">Cost Valuation Method</span>
                        <span class="text-white font-medium">Standard Purchase / Batch Cost</span>
                    </div>

                    @if($product->specification)
                    <div class="pt-2 border-t border-dark-800">
                        <span class="text-dark-500 block mb-1">Technical Specification</span>
                        <p class="text-dark-300 text-xs leading-relaxed whitespace-pre-line">{{ $product->specification }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Allocated Branches -->
            <div class="card p-5 bg-dark-900 border border-dark-800 rounded-2xl space-y-3">
                <h2 class="text-sm font-bold text-white uppercase tracking-wider border-b border-dark-800 pb-2">
                    Authorized Branches
                </h2>
                <div class="flex flex-wrap gap-1.5">
                    @forelse($product->branches as $b)
                        <span class="px-2.5 py-1 rounded-lg bg-dark-800 border border-dark-700 text-xs text-dark-300">
                            {{ $b->name }}
                        </span>
                    @empty
                        <span class="text-xs text-dark-500">Available across all active branches</span>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
