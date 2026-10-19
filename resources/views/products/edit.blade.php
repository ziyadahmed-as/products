@extends('layouts.app')
@section('title', 'Edit ' . ($product->type === 'raw_material' ? 'Raw Material' : 'Product') . ' — ' . $product->name)

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    currentType: '{{ old('type', $product->type) }}',
    imagePreview: null,
    removeExisting: false
}">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('products.show', $product) }}" class="btn-icon" title="Back to item details">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Edit {{ $product->name }}</h1>
                    @if($product->type === 'raw_material')
                        <span class="badge badge-yellow text-xs">Raw Material</span>
                    @elseif($product->type === 'manufactured_product')
                        <span class="badge badge-purple text-xs">Manufactured</span>
                    @elseif($product->type === 'resale_product')
                        <span class="badge badge-blue text-xs">Resale</span>
                    @endif
                </div>
                <p class="text-slate-500 dark:text-dark-400 text-xs mt-0.5 font-mono">SKU: {{ $product->sku }}</p>
            </div>
        </div>

        @if(in_array($product->type, ['raw_material', 'resale_product', 'consumable']))
        <a href="{{ route('stock-receipts.create', ['product_id' => $product->id]) }}" class="btn-primary text-xs px-4 py-2 flex items-center gap-1.5 shadow-xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Receive Stock</span>
        </a>
        @endif
    </div>

    <!-- Current Stock Notice -->
    <div class="p-4 rounded-2xl bg-white dark:bg-dark-900 border border-slate-200/80 dark:border-dark-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xs">
        <div>
            <span class="text-xs text-slate-500 dark:text-dark-400 uppercase tracking-wider font-semibold">Current In-Stock Quantity</span>
            <div class="text-2xl font-bold text-slate-900 dark:text-white mt-0.5 font-mono">
                {{ number_format($product->quantity, 2) }} <span class="text-sm font-normal text-slate-500 dark:text-dark-400">{{ $product->unit?->code ?? 'units' }}</span>
            </div>
            <p class="text-[11px] text-slate-400 dark:text-dark-500 mt-1">
                Stock quantities are updated automatically via valid Stock Receipts, Manufacturing Orders, or Stock Movements.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('stock-receipts.create', ['product_id' => $product->id]) }}" class="btn-secondary text-xs px-3 py-1.5 font-semibold">
                + Stock In / Receipt
            </a>
            <a href="{{ route('reports.inventory', ['search' => $product->sku]) }}" class="btn-secondary text-xs px-3 py-1.5">
                View Ledger
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

    <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="card p-6 space-y-5">
            <h2 class="text-base font-bold text-slate-900 dark:text-white border-b border-slate-200 dark:border-dark-800 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-sky-100 dark:bg-sky-900/40 text-sky-600 dark:text-sky-400 flex items-center justify-center text-xs font-bold">1</span>
                Master Data Information
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2 form-group">
                    <label class="form-label text-xs">
                        Item / Material Name <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                           class="form-input text-sm">
                </div>

                <div class="form-group">
                    <label class="form-label text-xs">
                        Item Type <span class="text-rose-500">*</span>
                    </label>
                    <select name="type" required class="form-select text-sm" x-model="currentType">
                        <option value="raw_material" {{ old('type', $product->type) === 'raw_material' ? 'selected' : '' }}>Raw Material</option>
                        <option value="manufactured_product" {{ old('type', $product->type) === 'manufactured_product' ? 'selected' : '' }}>Manufactured Product</option>
                        <option value="resale_product" {{ old('type', $product->type) === 'resale_product' ? 'selected' : '' }}>Resale Product</option>
                        <option value="consumable" {{ old('type', $product->type) === 'consumable' ? 'selected' : '' }}>Consumable</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="form-group">
                    <label class="form-label text-xs">
                        SKU / Material Code <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" required
                           class="form-input text-sm font-mono">
                </div>

                <div class="form-group">
                    <label class="form-label text-xs">Category</label>
                    <select name="category_id" class="form-select text-sm">
                        <option value="">-- None --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
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
                            <option value="{{ $unit->id }}" {{ old('unit_of_measure_id', $product->unit_of_measure_id) == $unit->id ? 'selected' : '' }}>
                                {{ $unit->name }} ({{ $unit->code }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group" x-show="currentType !== 'raw_material'" x-cloak>
                    <label class="form-label text-xs">Package Size / Packing Spec</label>
                    <input type="text" name="package_size" value="{{ old('package_size', $product->package_size) }}"
                           class="form-input text-sm"
                           :disabled="currentType === 'raw_material'"
                           placeholder="e.g. 50kg bag, 1L bottle">
                </div>

                <div class="flex items-center gap-2 pt-6">
                    <input type="checkbox" name="is_active" id="is_active" value="1"
                           {{ old('is_active', $product->is_active) ? 'checked' : '' }}
                           class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                    <label for="is_active" class="text-xs text-slate-700 dark:text-dark-300 cursor-pointer">
                        Active (Visible in inventory selections, manufacturing orders, and catalog)
                    </label>
                </div>
            </div>

            <div class="form-group" x-show="currentType !== 'raw_material'" x-cloak>
                <label class="form-label text-xs">Specification / Description</label>
                <textarea name="specification" rows="3"
                          :disabled="currentType === 'raw_material'"
                          class="form-textarea text-sm">{{ old('specification', $product->specification) }}</textarea>
            </div>

            <!-- Product Image (Strictly for Resale & Manufactured Products) -->
            <div class="form-group pt-4 border-t border-slate-100 dark:border-dark-800"
                 x-show="currentType === 'manufactured_product' || currentType === 'resale_product'"
                 x-cloak>
                <label class="form-label text-xs flex items-center justify-between mb-2">
                    <span class="flex items-center gap-1.5 font-semibold text-slate-800 dark:text-dark-200">
                        <svg class="w-4 h-4 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Product Image
                    </span>
                    <span class="text-[11px] text-slate-400 font-normal">PNG, JPG, WEBP up to 5MB</span>
                </label>

                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 p-4 rounded-xl border border-dashed border-slate-300 dark:border-dark-700 bg-slate-50/60 dark:bg-dark-900/40">
                    <!-- Image Thumbnail Preview / Current Image -->
                    <div class="relative w-20 h-20 rounded-xl bg-slate-200 dark:bg-dark-800 border border-slate-200 dark:border-dark-700 flex items-center justify-center overflow-hidden shrink-0 shadow-2xs">
                        <template x-if="imagePreview">
                            <img :src="imagePreview" alt="New Image Preview" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!imagePreview && !removeExisting">
                            @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                            @else
                                <div class="text-center p-1 text-slate-400">
                                    <svg class="w-7 h-7 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span class="text-[9px] block">No Image</span>
                                </div>
                            @endif
                        </template>
                        <template x-if="removeExisting && !imagePreview">
                            <div class="text-center p-1 text-rose-500">
                                <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                <span class="text-[9px] font-bold block">Removing</span>
                            </div>
                        </template>
                    </div>

                    <div class="flex-1 min-w-0 space-y-1.5">
                        <input type="file" name="image" id="edit_product_image" accept="image/png,image/jpeg,image/webp,image/gif"
                               :disabled="currentType !== 'manufactured_product' && currentType !== 'resale_product'"
                               class="block w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100 dark:file:bg-sky-950/60 dark:file:text-sky-300 cursor-pointer"
                               @change="const file = $event.target.files[0]; if (file) { removeExisting = false; const reader = new FileReader(); reader.onload = (e) => { imagePreview = e.target.result; }; reader.readAsDataURL(file); } else { imagePreview = null; }">
                        <p class="text-[11px] text-slate-400">Upload a new image to replace the existing one, or check remove to delete it.</p>

                        @if($product->image)
                            <div class="pt-1 flex items-center gap-2">
                                <input type="checkbox" name="remove_image" id="remove_image" value="1"
                                       x-model="removeExisting"
                                       @change="if(removeExisting) { imagePreview = null; document.getElementById('edit_product_image').value = ''; }"
                                       class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                                <label for="remove_image" class="text-xs text-rose-600 dark:text-rose-400 cursor-pointer font-medium">
                                    Remove current product image
                                </label>
                            </div>
                        @endif
                    </div>

                    <template x-if="imagePreview">
                        <button type="button" @click="imagePreview = null; document.getElementById('edit_product_image').value = ''"
                                class="btn-icon text-rose-500 hover:text-rose-700 shrink-0" title="Cancel new image selection">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </template>
                </div>
            </div>
        </div>

        <!-- Section 2: Valuation & Stock Alerts -->
        <div class="card p-6 space-y-5">
            <h2 class="text-base font-bold text-slate-900 dark:text-white border-b border-slate-200 dark:border-dark-800 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-sky-100 dark:bg-sky-900/40 text-sky-600 dark:text-sky-400 flex items-center justify-center text-xs font-bold">2</span>
                Pricing & Minimum Stock Control
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="form-group">
                    <label class="form-label text-xs">Purchase / Production Cost (Br)</label>
                    <input type="number" step="any" min="0" name="purchase_cost" value="{{ old('purchase_cost', $product->purchase_cost) }}"
                           class="form-input text-sm font-mono">
                </div>

                <div class="form-group">
                    <label class="form-label text-xs">Selling Price (Br)</label>
                    <input type="number" step="any" min="0" name="selling_price" value="{{ old('selling_price', $product->selling_price) }}"
                           class="form-input text-sm font-mono">
                </div>

                <div class="form-group">
                    <label class="form-label text-xs">Minimum Stock Level (Reorder Point)</label>
                    <input type="number" step="any" min="0" name="minimum_stock_level" value="{{ old('minimum_stock_level', $product->minimum_stock_level) }}"
                           class="form-input text-sm font-mono">
                </div>
            </div>

            <!-- Branch Allocation -->
            <div class="pt-3 border-t border-slate-200 dark:border-dark-800">
                <label class="form-label text-xs mb-2">Authorized Branches</label>
                @php $assignedIds = $product->branches->pluck('id')->toArray(); @endphp
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2">
                    @foreach($branches as $branch)
                        <label class="p-2.5 rounded-xl border border-slate-200 dark:border-dark-800 bg-slate-50/60 dark:bg-dark-950/60 hover:border-slate-300 dark:hover:border-dark-700 cursor-pointer flex items-center gap-2 text-xs text-slate-700 dark:text-dark-300 transition">
                            <input type="checkbox" name="branches[]" value="{{ $branch->id }}"
                                   {{ in_array($branch->id, old('branches', $assignedIds)) ? 'checked' : '' }}
                                   class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                            <span class="truncate">{{ $branch->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('products.show', $product) }}" class="btn-secondary px-5 py-2.5 text-xs">Cancel</a>
            <button type="submit" class="btn-primary px-6 py-2.5 text-xs font-semibold shadow-xs">
                Save Changes
            </button>
        </div>
    </form>

</div>
@endsection
