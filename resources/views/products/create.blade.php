@extends('layouts.app')
@section('title', 'Register New Item')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    currentType: '{{ old('type', $defaultType) }}',
    purchaseCost: {{ (float) old('purchase_cost', 0) }},
    sellingPrice: {{ (float) old('selling_price', 0) }},
    showMultiBranch: false,
    calcMargin() {
        if (!this.sellingPrice || this.sellingPrice <= 0) return 0;
        return (((this.sellingPrice - this.purchaseCost) / this.sellingPrice) * 100).toFixed(1);
    },
    calcMarkup() {
        if (!this.purchaseCost || this.purchaseCost <= 0) return 0;
        return (((this.sellingPrice - this.purchaseCost) / this.purchaseCost) * 100).toFixed(1);
    },
    calcProfit() {
        return (this.sellingPrice - this.purchaseCost).toFixed(2);
    }
}">

    <!-- Top Navigation / Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('products.index') }}" class="btn-icon" title="Back to products">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight"
                        x-text="currentType === 'raw_material' ? 'Register Raw Material' : (currentType === 'manufactured_product' ? 'Register Manufactured Product' : (currentType === 'resale_product' ? 'Register Resale Goods' : 'Register Consumable Item'))">
                        Register New Item
                    </h1>
                    <span class="text-xs px-2.5 py-0.5 rounded-full font-semibold uppercase tracking-wider"
                          :class="{
                              'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-400 border border-amber-200 dark:border-amber-800': currentType === 'raw_material',
                              'bg-purple-100 text-purple-800 dark:bg-purple-950/60 dark:text-purple-400 border border-purple-200 dark:border-purple-800': currentType === 'manufactured_product',
                              'bg-cyan-100 text-cyan-800 dark:bg-cyan-950/60 dark:text-cyan-400 border border-cyan-200 dark:border-cyan-800': currentType === 'resale_product',
                              'bg-slate-100 text-slate-800 dark:bg-slate-900/60 dark:text-slate-300 border border-slate-200 dark:border-slate-800': currentType === 'consumable',
                          }"
                          x-text="currentType === 'raw_material' ? 'Raw Material' : (currentType === 'manufactured_product' ? 'Manufactured' : (currentType === 'resale_product' ? 'Resale Goods' : 'Consumable'))">
                    </span>
                </div>
                <p class="text-slate-500 dark:text-dark-400 text-xs mt-0.5"
                   x-text="currentType === 'raw_material'
                       ? 'Record bulk ingredients, chemicals, packaging, or solvents used in factory production.'
                       : (currentType === 'manufactured_product'
                           ? 'Register finished goods produced in-house via recipes and manufacturing orders.'
                           : (currentType === 'resale_product'
                               ? 'Register commercial products purchased ready-made for direct customer sale.'
                               : 'Register operating supplies, machine maintenance parts, or laboratory consumables.'))">
                </p>
            </div>
        </div>

        <!-- Quick Switcher Links -->
        <div class="flex items-center gap-1.5 self-start sm:self-center">
            <a href="{{ route('raw-materials.index') }}" class="text-xs text-slate-500 hover:text-amber-600 dark:hover:text-amber-400 px-2.5 py-1 rounded-lg border border-slate-200 dark:border-dark-800 hover:border-amber-300 transition">
                🌾 Raw Materials
            </a>
            <a href="{{ route('manufactured-products.index') }}" class="text-xs text-slate-500 hover:text-purple-600 dark:hover:text-purple-400 px-2.5 py-1 rounded-lg border border-slate-200 dark:border-dark-800 hover:border-purple-300 transition">
                🏭 Manufactured
            </a>
            <a href="{{ route('resale-products.index') }}" class="text-xs text-slate-500 hover:text-cyan-600 dark:hover:text-cyan-400 px-2.5 py-1 rounded-lg border border-slate-200 dark:border-dark-800 hover:border-cyan-300 transition">
                🛒 Resale
            </a>
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

    <form method="POST" action="{{ route('products.store') }}" id="product-form" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- Section 1: Item Classification & Identification -->
        <div class="card p-6 space-y-5">
            <h2 class="text-base font-bold text-slate-900 dark:text-white border-b border-slate-200 dark:border-dark-800 pb-3 flex items-center justify-between">
                <span class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-sky-100 dark:bg-sky-900/40 text-sky-600 dark:text-sky-400 flex items-center justify-center text-xs font-bold">1</span>
                    Item Classification & Identification
                </span>
                <span class="text-xs text-slate-400 font-normal">Choose registration category</span>
            </h2>

            <!-- Type Selector Tabs -->
            <div>
                <label class="form-label text-xs mb-2">Item Registration Category <span class="text-rose-500">*</span></label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <!-- Raw Material Card -->
                    <label class="type-card p-3.5 rounded-xl border cursor-pointer transition flex flex-col gap-1.5"
                           :class="currentType === 'raw_material' ? 'border-amber-500 bg-amber-50/60 dark:bg-amber-950/30 shadow-xs' : 'border-slate-200 dark:border-dark-800 bg-slate-50/70 dark:bg-dark-950/50 hover:border-slate-300 dark:hover:border-dark-700'"
                           @click="currentType = 'raw_material'">
                        <input type="radio" name="type" value="raw_material" class="sr-only"
                               x-model="currentType">
                        <span class="text-xs font-bold text-amber-600 dark:text-amber-400 flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            Raw Material
                        </span>
                        <span class="text-[11px] text-slate-500 dark:text-dark-400 leading-tight">Ingredients, packaging, or solvents used in production</span>
                    </label>

                    <!-- Manufactured Card -->
                    <label class="type-card p-3.5 rounded-xl border cursor-pointer transition flex flex-col gap-1.5"
                           :class="currentType === 'manufactured_product' ? 'border-purple-500 bg-purple-50/60 dark:bg-purple-950/30 shadow-xs' : 'border-slate-200 dark:border-dark-800 bg-slate-50/70 dark:bg-dark-950/50 hover:border-slate-300 dark:hover:border-dark-700'"
                           @click="currentType = 'manufactured_product'">
                        <input type="radio" name="type" value="manufactured_product" class="sr-only"
                               x-model="currentType">
                        <span class="text-xs font-bold text-purple-600 dark:text-purple-400 flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                            Manufactured
                        </span>
                        <span class="text-[11px] text-slate-500 dark:text-dark-400 leading-tight">Finished goods produced in-house via manufacturing orders</span>
                    </label>

                    <!-- Resale Card -->
                    <label class="type-card p-3.5 rounded-xl border cursor-pointer transition flex flex-col gap-1.5"
                           :class="currentType === 'resale_product' ? 'border-cyan-500 bg-cyan-50/60 dark:bg-cyan-950/30 shadow-xs' : 'border-slate-200 dark:border-dark-800 bg-slate-50/70 dark:bg-dark-950/50 hover:border-slate-300 dark:hover:border-dark-700'"
                           @click="currentType = 'resale_product'">
                        <input type="radio" name="type" value="resale_product" class="sr-only"
                               x-model="currentType">
                        <span class="text-xs font-bold text-cyan-600 dark:text-cyan-400 flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            Resale Goods
                        </span>
                        <span class="text-[11px] text-slate-500 dark:text-dark-400 leading-tight">Items purchased ready-made for direct sale to customers</span>
                    </label>

                    <!-- Consumable Card -->
                    <label class="type-card p-3.5 rounded-xl border cursor-pointer transition flex flex-col gap-1.5"
                           :class="currentType === 'consumable' ? 'border-slate-500 bg-slate-100/60 dark:bg-dark-800 shadow-xs' : 'border-slate-200 dark:border-dark-800 bg-slate-50/70 dark:bg-dark-950/50 hover:border-slate-300 dark:hover:border-dark-700'"
                           @click="currentType = 'consumable'">
                        <input type="radio" name="type" value="consumable" class="sr-only"
                               x-model="currentType">
                        <span class="text-xs font-bold text-slate-700 dark:text-dark-300 flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            Consumable
                        </span>
                        <span class="text-[11px] text-slate-500 dark:text-dark-400 leading-tight">Operating supplies, maintenance parts, and lab materials</span>
                    </label>
                </div>
            </div>

            <!-- Name and SKU -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label text-xs">
                        <span x-text="currentType === 'raw_material' ? 'Raw Material Name' : (currentType === 'manufactured_product' ? 'Manufactured Product Name' : (currentType === 'resale_product' ? 'Resale Item Name' : 'Consumable Item Name'))">
                            Item / Material Name
                        </span>
                        <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="form-input text-sm"
                           :placeholder="currentType === 'raw_material'
                               ? 'e.g. Ethiopian White Sesame, Sodium Hydroxide Flakes, PET Bottle 1L'
                               : (currentType === 'manufactured_product'
                                   ? 'e.g. Pure Virgin Sesame Cooking Oil 1L, Dish Soap 500ml'
                                   : 'e.g. Imported Dispenser Pump 28mm, Heavy-Duty Packaging Box')">
                </div>

                <div class="form-group">
                    <label class="form-label text-xs">
                        SKU / Code <span class="text-slate-400 text-[11px] font-normal">(Leave blank to auto-generate)</span>
                    </label>
                    <input type="text" name="sku" value="{{ old('sku') }}"
                           class="form-input text-sm font-mono"
                           :placeholder="currentType === 'raw_material' ? 'e.g. RAW-SES-001' : (currentType === 'manufactured_product' ? 'e.g. MFG-OIL-1000' : 'e.g. RES-PUMP-01')">
                </div>
            </div>

            <!-- Category, Unit, Package Size -->
            <div class="grid grid-cols-1 gap-4" :class="currentType === 'raw_material' ? 'md:grid-cols-2' : 'md:grid-cols-3'">
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
                        <option value="">-- Select Unit --</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}" {{ old('unit_of_measure_id') == $unit->id ? 'selected' : '' }}>
                                {{ $unit->name }} ({{ $unit->code }})
                            </option>
                        @endforeach
                    </select>
                    <span class="text-[11px] text-slate-400 mt-1 block">Supported: kilograms, liters, bottles, bags, etc.</span>
                </div>

                <div class="form-group" x-show="currentType !== 'raw_material'" x-cloak>
                    <label class="form-label text-xs">Package Size / Packing Spec</label>
                    <input type="text" name="package_size" value="{{ old('package_size') }}"
                           class="form-input text-sm"
                           :disabled="currentType === 'raw_material'"
                           :placeholder="currentType === 'manufactured_product' ? 'e.g. 1L bottle, Carton of 12' : 'e.g. Pack of 100, Box'">
                </div>
            </div>

            <div class="form-group" x-show="currentType !== 'raw_material'" x-cloak>
                <label class="form-label text-xs">Specification / Quality Notes</label>
                <textarea name="specification" rows="2"
                          class="form-textarea text-sm"
                          :disabled="currentType === 'raw_material'"
                          placeholder="Product features, manufacturing tolerances, shelf life, regulatory compliance...">{{ old('specification') }}</textarea>
            </div>

            <!-- Product Image (Strictly for Resale & Manufactured Products) -->
            <div class="form-group pt-2 border-t border-slate-100 dark:border-dark-800"
                 x-show="currentType === 'manufactured_product' || currentType === 'resale_product'"
                 x-cloak
                 x-data="{ imagePreview: null }">
                <label class="form-label text-xs flex items-center justify-between mb-1.5">
                    <span class="flex items-center gap-1.5 font-semibold text-slate-800 dark:text-dark-200">
                        <svg class="w-4 h-4 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Product Image
                    </span>
                    <span class="text-[11px] text-slate-400 font-normal">PNG, JPG, WEBP up to 5MB</span>
                </label>

                <div class="flex items-center gap-4 p-3.5 rounded-xl border border-dashed border-slate-300 dark:border-dark-700 bg-slate-50/60 dark:bg-dark-900/40 hover:bg-slate-50 dark:hover:bg-dark-850 transition">
                    <!-- Image Preview / Placeholder -->
                    <div class="relative w-16 h-16 rounded-xl bg-slate-200 dark:bg-dark-800 border border-slate-200 dark:border-dark-700 flex items-center justify-center overflow-hidden shrink-0 shadow-2xs">
                        <template x-if="imagePreview">
                            <img :src="imagePreview" alt="Product Image Preview" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!imagePreview">
                            <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </template>
                    </div>

                    <div class="flex-1 min-w-0">
                        <input type="file" name="image" id="create_product_image" accept="image/png,image/jpeg,image/webp,image/gif"
                               :disabled="currentType !== 'manufactured_product' && currentType !== 'resale_product'"
                               class="block w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100 dark:file:bg-sky-950/60 dark:file:text-sky-300 cursor-pointer"
                               @change="const file = $event.target.files[0]; if (file) { const reader = new FileReader(); reader.onload = (e) => { imagePreview = e.target.result; }; reader.readAsDataURL(file); } else { imagePreview = null; }">
                        <p class="text-[11px] text-slate-400 mt-1">Displayed across storefront catalog, POS terminals, inventory cards, and customer invoices.</p>
                    </div>

                    <template x-if="imagePreview">
                        <button type="button" @click="imagePreview = null; document.getElementById('create_product_image').value = ''"
                                class="btn-icon text-rose-500 hover:text-rose-700 shrink-0" title="Remove selected image">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </template>
                </div>
            </div>
        </div>

        <!-- Section 2: Valuation, Costing & Reorder Control -->
        <div class="card p-6 space-y-5">
            <h2 class="text-base font-bold text-slate-900 dark:text-white border-b border-slate-200 dark:border-dark-800 pb-3 flex items-center justify-between">
                <span class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-sky-100 dark:bg-sky-900/40 text-sky-600 dark:text-sky-400 flex items-center justify-center text-xs font-bold">2</span>
                    Costing, Pricing & Reorder Control
                </span>
                <span class="text-xs text-slate-400 font-normal">Financial valuation & stock thresholds</span>
            </h2>

            <!-- Cost & Pricing Inputs -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start">
                <!-- Purchase / Standard Cost -->
                <div class="form-group">
                    <label class="form-label text-xs">
                        <span x-text="currentType === 'raw_material' ? 'Standard Purchase Cost (Br)' : (currentType === 'manufactured_product' ? 'Standard Unit Production Cost (Br)' : 'Wholesale Purchase Cost (Br)')">
                            Purchase / Production Cost (Br)
                        </span>
                        <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" step="any" min="0" name="purchase_cost" x-model.number="purchaseCost"
                           class="form-input text-sm font-mono">
                    <span class="text-[11px] text-slate-400 mt-1 block"
                          x-text="currentType === 'raw_material' ? 'Procurement cost per unit from supplier' : (currentType === 'manufactured_product' ? 'Estimated materials + direct production cost' : 'Acquisition cost from distributor')">
                        Expected cost per unit
                    </span>
                </div>

                <!-- Selling Price: Shown for Manufactured & Resale Goods; Hidden/Omitted for Raw Materials & Consumables -->
                <div class="form-group" x-show="currentType === 'manufactured_product' || currentType === 'resale_product'">
                    <label class="form-label text-xs">
                        Selling Price (Br) <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" step="any" min="0" name="selling_price" x-model.number="sellingPrice"
                           class="form-input text-sm font-mono">
                    <span class="text-[11px] text-slate-400 mt-1 block">Retail / wholesale price for customers</span>
                </div>

                <!-- Raw Material Notice when Selling Price is omitted -->
                <div class="form-group p-3 rounded-xl bg-amber-50/70 dark:bg-amber-950/20 border border-amber-200/80 dark:border-amber-900/30"
                     x-show="currentType === 'raw_material' || currentType === 'consumable'">
                    <div class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div class="text-[11px] text-amber-800 dark:text-amber-300 leading-tight">
                            <span class="font-bold">Not for direct customer sale:</span> Raw materials and consumables are consumed in production or facility operations. Selling price is not applicable.
                        </div>
                    </div>
                </div>

                <!-- Minimum Stock Level -->
                <div class="form-group">
                    <label class="form-label text-xs">
                        Minimum Stock Level (Reorder Point)
                    </label>
                    <input type="number" step="any" min="0" name="minimum_stock_level" value="{{ old('minimum_stock_level', 10) }}"
                           class="form-input text-sm font-mono">
                    <span class="text-[11px] text-slate-400 mt-1 block">Low stock alert triggers when balance falls below this</span>
                </div>
            </div>

            <!-- Profit Margin / Markup Preview (for Manufactured & Resale Products) -->
            <div x-show="(currentType === 'manufactured_product' || currentType === 'resale_product') && sellingPrice > 0"
                 x-cloak class="p-3.5 rounded-xl bg-slate-50 dark:bg-dark-950/50 border border-slate-200 dark:border-dark-800 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full" :class="calcProfit() >= 0 ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                    <span class="text-xs font-semibold text-slate-700 dark:text-dark-300">Financial Margin Preview:</span>
                </div>
                <div class="flex items-center gap-4 text-xs font-mono">
                    <div>
                        <span class="text-slate-400 font-sans">Profit / Unit:</span>
                        <span class="font-bold" :class="calcProfit() >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'"
                              x-text="'Br ' + calcProfit()"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 font-sans">Gross Margin:</span>
                        <span class="font-bold" :class="calcMargin() >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'"
                              x-text="calcMargin() + '%'"></span>
                    </div>
                    <div x-show="currentType === 'resale_product'">
                        <span class="text-slate-400 font-sans">Markup:</span>
                        <span class="font-bold text-sky-600 dark:text-sky-400" x-text="calcMarkup() + '%'"></span>
                    </div>
                </div>
            </div>

            <!-- Branch & Location Allocation -->
            <div class="pt-3 border-t border-slate-200 dark:border-dark-800 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <label class="form-label text-xs font-bold text-slate-800 dark:text-dark-200">
                            Registration Branch & Storage Location <span class="text-rose-500">*</span>
                        </label>
                        <p class="text-[11px] text-slate-400">Stock entries are automatically registered to your active branch and designated location.</p>
                    </div>
                    @if(!$canChangeBranch)
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 dark:bg-dark-800 text-slate-600 dark:text-dark-300 border border-slate-200 dark:border-dark-700">
                            🔒 Locked to Assigned Branch
                        </span>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label text-xs">Primary Branch</label>
                        @if($canChangeBranch)
                            <select name="branch_id" id="branch_select" class="form-select text-sm" onchange="filterStorageLocations()">
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ old('branch_id', $defaultBranchId) == $branch->id ? 'selected' : '' }}>
                                        🏢 {{ $branch->name }} ({{ $branch->code }})
                                    </option>
                                @endforeach
                            </select>
                        @else
                            <input type="hidden" name="branch_id" id="branch_select" value="{{ $defaultBranchId }}">
                            <div class="form-input text-sm bg-slate-100 dark:bg-dark-800 text-slate-700 dark:text-dark-300 font-medium cursor-not-allowed">
                                🏢 {{ $branches->firstWhere('id', $defaultBranchId)?->name ?? 'Assigned Branch' }}
                            </div>
                        @endif
                    </div>

                    <div class="form-group">
                        <label class="form-label text-xs">Designated Storage Location</label>
                        <select name="initial_storage_location_id" id="storage_location_select" class="form-select text-sm">
                            <option value="">-- Select Storage Location --</option>
                            @foreach($storageLocations as $loc)
                                <option value="{{ $loc->id }}"
                                        data-branch-id="{{ $loc->branch_id }}"
                                        {{ old('initial_storage_location_id', $defaultLocationId) == $loc->id ? 'selected' : '' }}>
                                    📦 {{ $loc->name }} ({{ $loc->branch->name ?? 'Main' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                @if($canChangeBranch)
                <!-- Multi-Branch Distribution (Optional for authorized Super Admin / Multi-branch managers) -->
                <div class="pt-3 border-t border-slate-100 dark:border-dark-800">
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" x-model="showMultiBranch"
                                   class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                            <span class="text-xs font-semibold text-sky-600 dark:text-sky-400 hover:underline">
                                Distribute initial stock across multiple branches (Optional)
                            </span>
                        </label>
                        <span class="text-[11px] text-slate-400" x-text="showMultiBranch ? 'Multi-branch mode active' : 'Single-branch mode'"></span>
                    </div>

                    <div x-show="showMultiBranch" x-cloak class="mt-3 p-4 rounded-xl bg-slate-50 dark:bg-dark-950/60 border border-slate-200 dark:border-dark-800 space-y-3">
                        <p class="text-[11px] text-slate-500 dark:text-dark-400">
                            Allocate specific portions of initial stock to different branches without duplication. Leave unallocated branches blank.
                        </p>
                        <div class="space-y-2.5">
                            @foreach($branches as $idx => $b)
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 items-center p-2 rounded-lg bg-white dark:bg-dark-900 border border-slate-200/80 dark:border-dark-800">
                                <div class="text-xs font-semibold text-slate-800 dark:text-dark-200 truncate">
                                    🏢 {{ $b->name }}
                                    <input type="hidden" name="branch_allocations[{{ $idx }}][branch_id]" value="{{ $b->id }}" :disabled="!showMultiBranch">
                                </div>
                                <div>
                                    <select name="branch_allocations[{{ $idx }}][storage_location_id]" class="form-select text-xs py-1" :disabled="!showMultiBranch">
                                        @foreach($storageLocations->where('branch_id', $b->id) as $loc)
                                            <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <input type="number" step="any" min="0" name="branch_allocations[{{ $idx }}][quantity]"
                                           class="form-input text-xs py-1 font-mono" placeholder="Allocated Qty (0.00)" :disabled="!showMultiBranch">
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
        <!-- Section 3: Initial Opening Stock Quantity -->
        <div class="card p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-dark-800 pb-3">
                <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xs font-bold">3</span>
                    Opening Stock Quantity (Optional)
                </h2>
                <span class="text-[11px] text-slate-400">Initial balance on hand</span>
            </div>

            <div x-show="!showMultiBranch">
                <p class="text-xs text-slate-500 dark:text-dark-400">
                    If physical stock already exists, enter quantity below. It will be recorded strictly for your designated primary branch and storage location.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-3">
                    <div class="form-group">
                        <label class="form-label text-xs">Opening Quantity</label>
                        <input type="number" step="any" min="0" name="initial_quantity" value="{{ old('initial_quantity', 0) }}"
                               class="form-input text-sm font-mono"
                               placeholder="0.00">
                    </div>

                    <div class="flex items-center gap-2 pt-6">
                        <input type="checkbox" name="is_active" id="is_active" value="1" checked
                               class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                        <label for="is_active" class="text-xs text-slate-700 dark:text-dark-300 cursor-pointer">
                            Active (Item is available for operations and inventory tracking)
                        </label>
                    </div>
                </div>
            </div>

            <div x-show="showMultiBranch" x-cloak class="p-3.5 rounded-xl bg-sky-50/70 dark:bg-sky-950/20 border border-sky-200/80 dark:border-sky-900/30">
                <p class="text-xs text-sky-800 dark:text-sky-300 font-medium">
                    ⚡ Multi-branch distribution mode is active. Opening quantities are allocated per branch in Section 2 above.
                </p>
                <div class="flex items-center gap-2 pt-3">
                    <input type="checkbox" name="is_active" id="is_active_multi" value="1" checked
                           class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                    <label for="is_active_multi" class="text-xs text-slate-700 dark:text-dark-300 cursor-pointer">
                        Active (Item is available for operations and inventory tracking)
                    </label>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('products.index') }}" class="btn-secondary px-5 py-2.5 text-xs">Cancel</a>
            <button type="submit" class="btn-primary px-6 py-2.5 text-xs font-semibold shadow-xs">
                <span x-text="currentType === 'raw_material' ? 'Register Raw Material' : (currentType === 'manufactured_product' ? 'Register Manufactured Product' : (currentType === 'resale_product' ? 'Register Resale Product' : 'Register Consumable'))">
                    Register Item
                </span>
            </button>
        </div>
    </form>

</div>

<script>
function filterStorageLocations() {
    const branchSelect = document.getElementById('branch_select');
    const locSelect = document.getElementById('storage_location_select');
    if (!branchSelect || !locSelect) return;

    const branchId = branchSelect.value;
    let firstFound = false;

    Array.from(locSelect.options).forEach((opt, idx) => {
        if (idx === 0) return; // Header option
        const bId = opt.getAttribute('data-branch-id');
        if (!branchId || bId === branchId) {
            opt.style.display = '';
            if (!firstFound) {
                opt.selected = true;
                firstFound = true;
            }
        } else {
            opt.style.display = 'none';
            if (opt.selected) {
                opt.selected = false;
            }
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    filterStorageLocations();
});
</script>
@endsection
