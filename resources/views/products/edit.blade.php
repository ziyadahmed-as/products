@extends('layouts.app')
@section('title', 'Edit ' . ($product->type === 'raw_material' ? 'Raw Material' : 'Product') . ' — ' . $product->name)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('products.show', $product) }}" class="p-2 rounded-xl bg-dark-900 border border-dark-800 text-dark-400 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold text-white tracking-tight">Edit {{ $product->name }}</h1>
                    @if($product->type === 'raw_material')
                        <span class="px-2 py-0.5 rounded text-xs font-semibold bg-amber-500/15 text-amber-300 border border-amber-500/25">
                            Raw Material
                        </span>
                    @elseif($product->type === 'manufactured_product')
                        <span class="px-2 py-0.5 rounded text-xs font-semibold bg-purple-500/15 text-purple-300 border border-purple-500/25">
                            Manufactured
                        </span>
                    @endif
                </div>
                <p class="text-dark-400 text-xs mt-0.5 font-mono">SKU: {{ $product->sku }}</p>
            </div>
        </div>

        @if(in_array($product->type, ['raw_material', 'resale_product', 'consumable']))
        <a href="{{ route('stock-receipts.create', ['product_id' => $product->id]) }}" class="btn-primary text-xs px-4 py-2 flex items-center gap-1.5 shadow-lg shadow-primary-600/30">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Receive Additional Stock
        </a>
        @endif
    </div>

    <!-- Current Stock Notice -->
    <div class="p-4 rounded-2xl bg-dark-900 border border-dark-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="text-xs text-dark-400 uppercase tracking-wider font-semibold">Current In-Stock Quantity</span>
            <div class="text-2xl font-bold text-white mt-0.5 font-mono">
                {{ number_format($product->quantity, 2) }} <span class="text-sm font-normal text-dark-400">{{ $product->unit?->code ?? 'units' }}</span>
            </div>
            <p class="text-[11px] text-dark-500 mt-1">
                Stock quantities are updated automatically via valid Stock Receipts, Manufacturing Orders, or Stock Movements.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('stock-receipts.create', ['product_id' => $product->id]) }}" class="px-3 py-1.5 rounded-lg bg-emerald-600/20 border border-emerald-500/30 text-emerald-400 text-xs font-semibold hover:bg-emerald-600/30 transition">
                + Stock In / Receipt
            </a>
            <a href="{{ route('reports.inventory', ['search' => $product->sku]) }}" class="px-3 py-1.5 rounded-lg bg-dark-800 text-dark-300 text-xs hover:text-white transition">
                View Ledger
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="p-4 rounded-xl bg-rose-950/60 border border-rose-800/50 text-rose-300">
            <p class="font-semibold text-sm mb-1">Please correct the following errors:</p>
            <ul class="list-disc list-inside text-xs space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('products.update', $product) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="card p-6 bg-dark-900 border border-dark-800 rounded-2xl space-y-5">
            <h2 class="text-base font-bold text-white border-b border-dark-800 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-primary-600/30 text-primary-400 flex items-center justify-center text-xs font-bold">1</span>
                Master Data Information
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-dark-300 mb-1.5">
                        Item / Material Name <span class="text-rose-400">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                           class="w-full px-3 py-2 rounded-lg bg-dark-950 border border-dark-800 text-white text-sm focus:border-primary-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-medium text-dark-300 mb-1.5">
                        Item Type <span class="text-rose-400">*</span>
                    </label>
                    <select name="type" required class="w-full px-3 py-2 rounded-lg bg-dark-950 border border-dark-800 text-white text-sm focus:border-primary-500 focus:outline-none">
                        <option value="raw_material" {{ old('type', $product->type) === 'raw_material' ? 'selected' : '' }}>Raw Material</option>
                        <option value="manufactured_product" {{ old('type', $product->type) === 'manufactured_product' ? 'selected' : '' }}>Manufactured Product</option>
                        <option value="resale_product" {{ old('type', $product->type) === 'resale_product' ? 'selected' : '' }}>Resale Product</option>
                        <option value="consumable" {{ old('type', $product->type) === 'consumable' ? 'selected' : '' }}>Consumable</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-dark-300 mb-1.5">
                        SKU / Material Code <span class="text-rose-400">*</span>
                    </label>
                    <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" required
                           class="w-full px-3 py-2 rounded-lg bg-dark-950 border border-dark-800 text-white text-sm font-mono focus:border-primary-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-medium text-dark-300 mb-1.5">Category</label>
                    <select name="category_id" class="w-full px-3 py-2 rounded-lg bg-dark-950 border border-dark-800 text-white text-sm focus:border-primary-500 focus:outline-none">
                        <option value="">-- None --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-dark-300 mb-1.5">
                        Unit of Measurement <span class="text-rose-400">*</span>
                    </label>
                    <select name="unit_of_measure_id" required class="w-full px-3 py-2 rounded-lg bg-dark-950 border border-dark-800 text-white text-sm focus:border-primary-500 focus:outline-none">
                        <option value="">-- Select Unit --</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}" {{ old('unit_of_measure_id', $product->unit_of_measure_id) == $unit->id ? 'selected' : '' }}>
                                {{ $unit->name }} ({{ $unit->code }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-dark-300 mb-1.5">Package Size / Packing Spec</label>
                    <input type="text" name="package_size" value="{{ old('package_size', $product->package_size) }}"
                           class="w-full px-3 py-2 rounded-lg bg-dark-950 border border-dark-800 text-white text-sm focus:border-primary-500 focus:outline-none"
                           placeholder="e.g. 50kg bag, 1L bottle">
                </div>

                <div class="flex items-center gap-2 pt-6">
                    <input type="checkbox" name="is_active" id="is_active" value="1"
                           {{ old('is_active', $product->is_active) ? 'checked' : '' }}
                           class="rounded bg-dark-900 border-dark-700 text-primary-600 focus:ring-primary-500">
                    <label for="is_active" class="text-xs text-dark-300 cursor-pointer">
                        Active (Visible in inventory selections, manufacturing orders, and catalog)
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-dark-300 mb-1.5">Specification / Description</label>
                <textarea name="specification" rows="3"
                          class="w-full px-3 py-2 rounded-lg bg-dark-950 border border-dark-800 text-white text-sm focus:border-primary-500 focus:outline-none">{{ old('specification', $product->specification) }}</textarea>
            </div>
        </div>

        <!-- Section 2: Valuation & Stock Alerts -->
        <div class="card p-6 bg-dark-900 border border-dark-800 rounded-2xl space-y-5">
            <h2 class="text-base font-bold text-white border-b border-dark-800 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-primary-600/30 text-primary-400 flex items-center justify-center text-xs font-bold">2</span>
                Pricing & Minimum Stock Control
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-dark-300 mb-1.5">Purchase / Production Cost (Br)</label>
                    <input type="number" step="any" min="0" name="purchase_cost" value="{{ old('purchase_cost', $product->purchase_cost) }}"
                           class="w-full px-3 py-2 rounded-lg bg-dark-950 border border-dark-800 text-white text-sm font-mono focus:border-primary-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-medium text-dark-300 mb-1.5">Selling Price (Br)</label>
                    <input type="number" step="any" min="0" name="selling_price" value="{{ old('selling_price', $product->selling_price) }}"
                           class="w-full px-3 py-2 rounded-lg bg-dark-950 border border-dark-800 text-white text-sm font-mono focus:border-primary-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-medium text-dark-300 mb-1.5">Minimum Stock Level (Reorder Point)</label>
                    <input type="number" step="any" min="0" name="minimum_stock_level" value="{{ old('minimum_stock_level', $product->minimum_stock_level) }}"
                           class="w-full px-3 py-2 rounded-lg bg-dark-950 border border-dark-800 text-white text-sm font-mono focus:border-primary-500 focus:outline-none">
                </div>
            </div>

            <!-- Branch Allocation -->
            <div class="pt-3 border-t border-dark-800">
                <label class="block text-xs font-medium text-dark-300 mb-2">Authorized Branches</label>
                @php $assignedIds = $product->branches->pluck('id')->toArray(); @endphp
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2">
                    @foreach($branches as $branch)
                        <label class="p-2.5 rounded-xl border border-dark-800 bg-dark-950/60 hover:border-dark-700 cursor-pointer flex items-center gap-2 text-xs text-dark-300 transition">
                            <input type="checkbox" name="branches[]" value="{{ $branch->id }}"
                                   {{ in_array($branch->id, old('branches', $assignedIds)) ? 'checked' : '' }}
                                   class="rounded bg-dark-900 border-dark-700 text-primary-600 focus:ring-primary-500">
                            <span class="truncate">{{ $branch->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('products.show', $product) }}" class="btn-secondary px-5 py-2.5 text-xs">Cancel</a>
            <button type="submit" class="btn-primary px-6 py-2.5 text-xs font-semibold shadow-lg shadow-primary-600/30">
                Save Changes
            </button>
        </div>
    </form>

</div>
@endsection
