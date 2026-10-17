@extends('layouts.app')
@section('title', 'Register New Product / Raw Material')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('products.index') }}" class="btn-icon" title="Back to products">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Register New Item</h1>
                <p class="text-slate-500 dark:text-dark-400 text-xs mt-0.5">Add a new raw material, manufactured product, or resale item to inventory.</p>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <div>
                <p class="font-semibold text-sm mb-1">Please correct the following errors:</p>
                <ul class="list-disc list-inside text-xs space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('products.store') }}" id="product-form" class="space-y-6">
        @csrf

        <!-- Classification & Basic Info -->
        <div class="card p-6 space-y-5">
            <h2 class="text-base font-bold text-slate-900 dark:text-white border-b border-slate-200 dark:border-dark-800 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-sky-100 dark:bg-sky-900/40 text-sky-600 dark:text-sky-400 flex items-center justify-center text-xs font-bold">1</span>
                Item Classification & Identification
            </h2>

            <!-- Type Selector Tabs / Radios -->
            <div>
                <label class="form-label text-xs mb-2">Item Type <span class="text-rose-500">*</span></label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <label class="type-card p-3.5 rounded-xl border border-slate-200 dark:border-dark-800 bg-slate-50/70 dark:bg-dark-950/50 cursor-pointer transition flex flex-col gap-1.5 hover:border-slate-300 dark:hover:border-dark-700"
                           onclick="selectType('raw_material')">
                        <input type="radio" name="type" value="raw_material" id="type_raw_material" class="sr-only"
                               {{ old('type', $defaultType) === 'raw_material' ? 'checked' : '' }}>
                        <span class="text-xs font-bold text-amber-600 dark:text-amber-400 flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            Raw Material
                        </span>
                        <span class="text-[11px] text-slate-500 dark:text-dark-500 leading-tight">Ingredients, packaging, or solvents used in production</span>
                    </label>

                    <label class="type-card p-3.5 rounded-xl border border-slate-200 dark:border-dark-800 bg-slate-50/70 dark:bg-dark-950/50 cursor-pointer transition flex flex-col gap-1.5 hover:border-slate-300 dark:hover:border-dark-700"
                           onclick="selectType('manufactured_product')">
                        <input type="radio" name="type" value="manufactured_product" id="type_manufactured_product" class="sr-only"
                               {{ old('type', $defaultType) === 'manufactured_product' ? 'checked' : '' }}>
                        <span class="text-xs font-bold text-purple-600 dark:text-purple-400 flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                            Manufactured
                        </span>
                        <span class="text-[11px] text-slate-500 dark:text-dark-500 leading-tight">Finished goods produced in-house via manufacturing orders</span>
                    </label>

                    <label class="type-card p-3.5 rounded-xl border border-slate-200 dark:border-dark-800 bg-slate-50/70 dark:bg-dark-950/50 cursor-pointer transition flex flex-col gap-1.5 hover:border-slate-300 dark:hover:border-dark-700"
                           onclick="selectType('resale_product')">
                        <input type="radio" name="type" value="resale_product" id="type_resale_product" class="sr-only"
                               {{ old('type', $defaultType) === 'resale_product' ? 'checked' : '' }}>
                        <span class="text-xs font-bold text-cyan-600 dark:text-cyan-400 flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            Resale Goods
                        </span>
                        <span class="text-[11px] text-slate-500 dark:text-dark-500 leading-tight">Items purchased ready-made for direct sale to customers</span>
                    </label>

                    <label class="type-card p-3.5 rounded-xl border border-slate-200 dark:border-dark-800 bg-slate-50/70 dark:bg-dark-950/50 cursor-pointer transition flex flex-col gap-1.5 hover:border-slate-300 dark:hover:border-dark-700"
                           onclick="selectType('consumable')">
                        <input type="radio" name="type" value="consumable" id="type_consumable" class="sr-only"
                               {{ old('type', $defaultType) === 'consumable' ? 'checked' : '' }}>
                        <span class="text-xs font-bold text-slate-600 dark:text-dark-300 flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            Consumable
                        </span>
                        <span class="text-[11px] text-slate-500 dark:text-dark-500 leading-tight">Operating supplies, maintenance parts, and lab materials</span>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label text-xs">
                        Item / Material Name <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="form-input text-sm"
                           placeholder="e.g. Sodium Hydroxide Flakes, Plastic Bottle 1L, Albareck Dish Soap 500ml">
                </div>

                <div class="form-group">
                    <label class="form-label text-xs">
                        SKU / Material Code <span class="text-slate-400 text-[11px] font-normal">(Leave blank to auto-generate)</span>
                    </label>
                    <input type="text" name="sku" value="{{ old('sku') }}"
                           class="form-input text-sm font-mono"
                           placeholder="e.g. RAW-SODA-01, BOT-1L-HDPE">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="form-group">
                    <label class="form-label text-xs">Category</label>
                    <select name="category_id" class="form-select text-sm">
                        <option value="">-- Select Category --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label text-xs">
                        Unit of Measurement <span class="text-rose-500">*</span>
                    </label>
                    <select name="unit_of_measure_id" required class="form-select text-sm">
                        <option value="">-- Select Unit (kg, L, bottles, etc.) --</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}" {{ old('unit_of_measure_id') == $unit->id ? 'selected' : '' }}>
                                {{ $unit->name }} ({{ $unit->code }})
                            </option>
                        @endforeach
                    </select>
                    <span class="text-[11px] text-slate-400 mt-1 block">Supported: kilograms, liters, bottles, packaging quantities, etc.</span>
                </div>

                <div class="form-group">
                    <label class="form-label text-xs">Package Size / Packing Spec</label>
                    <input type="text" name="package_size" value="{{ old('package_size') }}"
                           class="form-input text-sm"
                           placeholder="e.g. 25kg bag, 1L bottle, 200L drum">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label text-xs">Specification / Quality Notes</label>
                <textarea name="specification" rows="2"
                          class="form-textarea text-sm"
                          placeholder="Technical specifications, chemical purity, storage recommendations, manufacturer notes...">{{ old('specification') }}</textarea>
            </div>
        </div>

        <!-- Section 2: Valuation, Pricing & Reorder Control -->
        <div class="card p-6 space-y-5">
            <h2 class="text-base font-bold text-slate-900 dark:text-white border-b border-slate-200 dark:border-dark-800 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-sky-100 dark:bg-sky-900/40 text-sky-600 dark:text-sky-400 flex items-center justify-center text-xs font-bold">2</span>
                Costing, Pricing & Reorder Control
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="form-group">
                    <label class="form-label text-xs">
                        Standard Purchase / Unit Cost (Br)
                    </label>
                    <input type="number" step="any" min="0" name="purchase_cost" value="{{ old('purchase_cost', 0) }}"
                           class="form-input text-sm font-mono">
                    <span class="text-[11px] text-slate-400 mt-1 block">Expected cost per unit of measure</span>
                </div>

                <div class="form-group" id="selling-price-wrapper">
                    <label class="form-label text-xs">
                        Selling Price (Br) <span class="text-rose-500" id="selling-price-req">*</span>
                    </label>
                    <input type="number" step="any" min="0" name="selling_price" id="selling_price" value="{{ old('selling_price', 0) }}"
                           class="form-input text-sm font-mono">
                    <span class="text-[11px] text-slate-400 mt-1 block">Retail/wholesale price for saleable finished goods</span>
                </div>

                <div class="form-group">
                    <label class="form-label text-xs">
                        Minimum Stock Level (Reorder Point)
                    </label>
                    <input type="number" step="any" min="0" name="minimum_stock_level" value="{{ old('minimum_stock_level', 10) }}"
                           class="form-input text-sm font-mono">
                    <span class="text-[11px] text-slate-400 mt-1 block">Low stock alert triggers when balance falls below this</span>
                </div>
            </div>

            <!-- Branch Allocation -->
            <div class="pt-3 border-t border-slate-200 dark:border-dark-800">
                <label class="form-label text-xs mb-2">Authorized Branches for Item</label>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2">
                    @foreach($branches as $branch)
                        <label class="p-2.5 rounded-xl border border-slate-200 dark:border-dark-800 bg-slate-50/60 dark:bg-dark-950/60 hover:border-slate-300 dark:hover:border-dark-700 cursor-pointer flex items-center gap-2 text-xs text-slate-700 dark:text-dark-300 transition">
                            <input type="checkbox" name="branches[]" value="{{ $branch->id }}" checked
                                   class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                            <span class="truncate">{{ $branch->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Section 3: Optional Initial Opening Stock -->
        <div class="card p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-dark-800 pb-3">
                <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xs font-bold">3</span>
                    Initial Opening Stock (Optional)
                </h2>
                <span class="text-[11px] text-slate-400">Record existing stock upon registration</span>
            </div>

            <p class="text-xs text-slate-500 dark:text-dark-400">
                If physical stock already exists, specify initial quantity below. It will automatically generate an opening stock transaction and balance.
                Future deliveries should be received via <a href="{{ route('stock-receipts.create') }}" class="text-sky-600 dark:text-sky-400 hover:underline">Stock Receipts</a>.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
                <div class="form-group">
                    <label class="form-label text-xs">Storage Location</label>
                    <select name="initial_storage_location_id" class="form-select text-sm">
                        <option value="">-- Select Storage Location --</option>
                        @foreach($storageLocations as $loc)
                            <option value="{{ $loc->id }}" {{ old('initial_storage_location_id') == $loc->id ? 'selected' : '' }}>
                                {{ $loc->name }} ({{ $loc->branch->name ?? 'Main' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label text-xs">Opening Quantity</label>
                    <input type="number" step="any" min="0" name="initial_quantity" value="{{ old('initial_quantity', 0) }}"
                           class="form-input text-sm font-mono"
                           placeholder="0.00">
                </div>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" name="is_active" id="is_active" value="1" checked
                       class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                <label for="is_active" class="text-xs text-slate-700 dark:text-dark-300 cursor-pointer">
                    Active (Item is available for operations and manufacturing orders)
                </label>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('products.index') }}" class="btn-secondary px-5 py-2.5 text-xs">Cancel</a>
            <button type="submit" class="btn-primary px-6 py-2.5 text-xs font-semibold shadow-xs">
                Register Item
            </button>
        </div>
    </form>

</div>

<script>
function selectType(type) {
    document.querySelectorAll('input[name="type"]').forEach(input => {
        const card = input.closest('.type-card');
        if (input.value === type) {
            input.checked = true;
            if (card) {
                card.classList.add('border-sky-500', 'bg-sky-50/50', 'dark:bg-sky-950/20');
                card.classList.remove('border-slate-200', 'dark:border-dark-800');
            }
        } else {
            input.checked = false;
            if (card) {
                card.classList.remove('border-sky-500', 'bg-sky-50/50', 'dark:bg-sky-950/20');
                card.classList.add('border-slate-200', 'dark:border-dark-800');
            }
        }
    });

    const sellingWrap = document.getElementById('selling-price-wrapper');
    const sellingReq = document.getElementById('selling-price-req');
    if (type === 'raw_material' || type === 'consumable') {
        if (sellingReq) sellingReq.classList.add('hidden');
    } else {
        if (sellingReq) sellingReq.classList.remove('hidden');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const checked = document.querySelector('input[name="type"]:checked');
    if (checked) {
        selectType(checked.value);
    }
});
</script>
@endsection
