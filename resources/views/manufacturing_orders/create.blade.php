@extends('layouts.app')
@section('title', 'Create Manufacturing Order')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('manufacturing-orders.index') }}" class="text-dark-400 hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <h1 class="text-2xl font-bold text-white tracking-tight">Create Manufacturing Order</h1>
            </div>
            <p class="text-dark-400 text-sm mt-1">Plan a production batch, configure raw material requirements, and schedule manufacturing.</p>
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

    <form method="POST" action="{{ route('manufacturing-orders.store') }}" id="mo-form">
        @csrf

        <!-- Section 1: Order Details -->
        <div class="card space-y-5 mb-6">
            <h2 class="text-base font-semibold text-white border-b border-dark-800 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-primary-600/30 text-primary-400 flex items-center justify-center text-xs font-bold">1</span>
                Production Order Identification & Output Product
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Manufactured Product -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-dark-300 mb-1.5">
                        Finished Product to Manufacture <span class="text-rose-400">*</span>
                    </label>
                    <select name="product_id" id="product_id" required class="w-full px-3 py-2 rounded-lg bg-dark-900 border border-dark-800 text-white text-sm focus:border-primary-500 focus:outline-none">
                        <option value="">-- Select Product --</option>
                        @foreach($products as $prod)
                            <option value="{{ $prod->id }}"
                                    data-unit="{{ $prod->unit_of_measure_id }}"
                                    data-recipe="{{ $prod->recipe?->id ?? '' }}"
                                    {{ old('product_id') == $prod->id ? 'selected' : '' }}>
                                {{ $prod->name }} (SKU: {{ $prod->sku }}) [Current Stock: {{ $prod->quantity }} {{ $prod->unit?->code ?? 'units' }}]
                            </option>
                        @endforeach
                    </select>
                    <span class="text-[11px] text-dark-500 mt-1 block">Select the target product for which inventory will be increased upon completion.</span>
                </div>

                <!-- Recipe / BOM -->
                <div>
                    <label class="block text-xs font-medium text-dark-300 mb-1.5">
                        Recipe / Bill of Materials (BOM)
                    </label>
                    <select name="recipe_id" id="recipe_id" class="w-full px-3 py-2 rounded-lg bg-dark-900 border border-dark-800 text-white text-sm focus:border-primary-500 focus:outline-none">
                        <option value="">-- None / Custom Recipe --</option>
                        @foreach($recipes as $rec)
                            <option value="{{ $rec->id }}"
                                    data-product="{{ $rec->product_id }}"
                                    data-materials="{{ json_encode($rec->materials->map(fn($m) => [
                                        'product_id' => $m->raw_material_id,
                                        'name' => $m->rawMaterial->name ?? '',
                                        'sku' => $m->rawMaterial->sku ?? '',
                                        'ratio' => (float)$m->quantity,
                                        'unit' => $m->rawMaterial->unit?->code ?? 'units',
                                        'unit_id' => $m->rawMaterial->unit_of_measure_id,
                                        'available' => (float)$m->rawMaterial->quantity,
                                    ])) }}"
                                    {{ old('recipe_id') == $rec->id ? 'selected' : '' }}>
                                {{ $rec->name }} ({{ $rec->materials->count() }} materials)
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Planned Quantity -->
                <div>
                    <label class="block text-xs font-medium text-dark-300 mb-1.5">
                        Planned Production Quantity <span class="text-rose-400">*</span>
                    </label>
                    <input type="number" step="any" min="0.01" name="planned_quantity" id="planned_quantity"
                           value="{{ old('planned_quantity', 100) }}" required
                           class="w-full px-3 py-2 rounded-lg bg-dark-900 border border-dark-800 text-white text-sm focus:border-primary-500 focus:outline-none font-mono">
                </div>

                <!-- Unit of Measure -->
                <div>
                    <label class="block text-xs font-medium text-dark-300 mb-1.5">
                        Unit of Measurement
                    </label>
                    <select name="unit_of_measure_id" id="unit_of_measure_id" class="w-full px-3 py-2 rounded-lg bg-dark-900 border border-dark-800 text-white text-sm focus:border-primary-500 focus:outline-none">
                        <option value="">-- Default Product Unit --</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}" {{ old('unit_of_measure_id') == $unit->id ? 'selected' : '' }}>
                                {{ $unit->name }} ({{ $unit->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Output Storage Location -->
                <div>
                    <label class="block text-xs font-medium text-dark-300 mb-1.5">
                        Target Storage Location / Warehouse <span class="text-rose-400">*</span>
                    </label>
                    <select name="storage_location_id" required class="w-full px-3 py-2 rounded-lg bg-dark-900 border border-dark-800 text-white text-sm focus:border-primary-500 focus:outline-none">
                        @foreach($storageLocations as $loc)
                            <option value="{{ $loc->id }}" {{ old('storage_location_id') == $loc->id ? 'selected' : '' }}>
                                {{ $loc->name }} ({{ $loc->branch->name ?? 'Main' }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Section 2: Scheduling & Assignment -->
        <div class="card space-y-5 mb-6">
            <h2 class="text-base font-semibold text-white border-b border-dark-800 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-primary-600/30 text-primary-400 flex items-center justify-center text-xs font-bold">2</span>
                Production Scheduling & Assignment
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-medium text-dark-300 mb-1.5">
                        Planned Order Date <span class="text-rose-400">*</span>
                    </label>
                    <input type="date" name="planned_date" value="{{ old('planned_date', date('Y-m-d')) }}" required
                           class="w-full px-3 py-2 rounded-lg bg-dark-900 border border-dark-800 text-white text-sm focus:border-primary-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-medium text-dark-300 mb-1.5">
                        Production Start Date
                    </label>
                    <input type="date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}"
                           class="w-full px-3 py-2 rounded-lg bg-dark-900 border border-dark-800 text-white text-sm focus:border-primary-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-medium text-dark-300 mb-1.5">
                        Expected Completion Date
                    </label>
                    <input type="date" name="expected_completion_date" value="{{ old('expected_completion_date', date('Y-m-d', strtotime('+3 days'))) }}"
                           class="w-full px-3 py-2 rounded-lg bg-dark-900 border border-dark-800 text-white text-sm focus:border-primary-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-medium text-dark-300 mb-1.5">
                        Assigned Production Staff
                    </label>
                    <select name="assigned_user_id" class="w-full px-3 py-2 rounded-lg bg-dark-900 border border-dark-800 text-white text-sm focus:border-primary-500 focus:outline-none">
                        <option value="">-- Unassigned --</option>
                        @foreach($staffMembers as $staff)
                            <option value="{{ $staff->id }}" {{ old('assigned_user_id', auth()->id()) == $staff->id ? 'selected' : '' }}>
                                {{ $staff->name }} ({{ $staff->roles->first()->name ?? 'Staff' }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-dark-300 mb-1.5">
                    Production Notes & Instructions
                </label>
                <textarea name="production_notes" rows="2" placeholder="Special manufacturing instructions, batch parameters, quality guidelines..."
                          class="w-full px-3 py-2 rounded-lg bg-dark-900 border border-dark-800 text-white text-sm focus:border-primary-500 focus:outline-none">{{ old('production_notes') }}</textarea>
            </div>
        </div>

        <!-- Section 3: Raw Material Requirements & Stock Validation -->
        <div class="card space-y-4 mb-6">
            <div class="flex items-center justify-between border-b border-dark-800 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-primary-600/30 text-primary-400 flex items-center justify-center text-xs font-bold">3</span>
                    <h2 class="text-base font-semibold text-white">Required Raw Materials & Consumption Plan</h2>
                </div>
                <button type="button" id="add-material-btn" class="btn-secondary text-xs px-2.5 py-1">
                    + Add Custom Material
                </button>
            </div>

            <p class="text-xs text-dark-400">
                The system checks current raw material stock balances. When the order is approved and materials are issued, these quantities will be atomically deducted from inventory.
            </p>

            <div class="table-wrapper">
                <table class="table" id="materials-table">
                    <thead>
                        <tr>
                            <th style="width: 45%;">Raw Material</th>
                            <th style="width: 20%;">Required Quantity</th>
                            <th style="width: 25%;">Available Stock</th>
                            <th style="width: 10%;" class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody id="materials-container">
                        <!-- Populated dynamically via JS when Recipe is chosen or user clicks Add -->
                    </tbody>
                </table>
            </div>

            <div id="no-materials-notice" class="p-4 rounded-lg bg-dark-900/60 border border-dark-800 text-center text-xs text-dark-400">
                Select a Recipe above to automatically compute material requirements, or click "+ Add Custom Material" to specify raw materials manually.
            </div>
        </div>

        <!-- Submit Bar -->
        <div class="flex items-center justify-between pt-4">
            <a href="{{ route('manufacturing-orders.index') }}" class="btn-secondary text-sm">
                Cancel
            </a>
            <button type="submit" class="btn-primary text-sm px-6 py-2.5 shadow-lg shadow-primary-600/30">
                Create Production Order
            </button>
        </div>
    </form>
</div>

<!-- Raw Material Options Template for JS -->
<template id="material-row-template">
    <tr class="material-row">
        <td>
            <select name="materials[INDEX][product_id]" required class="mat-select w-full px-2.5 py-1.5 rounded bg-dark-900 border border-dark-800 text-white text-xs focus:border-primary-500 focus:outline-none">
                <option value="">-- Choose Raw Material --</option>
                @foreach($rawMaterials as $mat)
                    <option value="{{ $mat->id }}"
                            data-avail="{{ $mat->quantity }}"
                            data-unit="{{ $mat->unit?->code ?? 'units' }}">
                        {{ $mat->name }} (SKU: {{ $mat->sku }})
                    </option>
                @endforeach
            </select>
        </td>
        <td>
            <div class="flex items-center gap-1.5">
                <input type="number" step="any" min="0.001" name="materials[INDEX][quantity]" required
                       class="mat-qty w-full px-2.5 py-1.5 rounded bg-dark-900 border border-dark-800 text-white text-xs focus:border-primary-500 focus:outline-none font-mono"
                       placeholder="Qty">
                <span class="mat-unit-label text-xs text-dark-400 min-w-8">units</span>
            </div>
        </td>
        <td>
            <div class="mat-stock-badge flex items-center gap-1.5 text-xs text-dark-400">
                <span class="mat-avail-val font-mono font-medium">-</span>
                <span class="mat-avail-status text-[11px]"></span>
            </div>
        </td>
        <td class="text-right">
            <button type="button" class="remove-mat-btn text-rose-400 hover:text-rose-300 text-xs px-2 py-1">
                ✕
            </button>
        </td>
    </tr>
</template>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let rowIndex = 0;
    const container = document.getElementById('materials-container');
    const notice = document.getElementById('no-materials-notice');
    const template = document.getElementById('material-row-template');
    const recipeSelect = document.getElementById('recipe_id');
    const productSelect = document.getElementById('product_id');
    const plannedQtyInput = document.getElementById('planned_quantity');

    function updateStockBadge(row) {
        const select = row.querySelector('.mat-select');
        const qtyInput = row.querySelector('.mat-qty');
        const unitLabel = row.querySelector('.mat-unit-label');
        const availVal = row.querySelector('.mat-avail-val');
        const availStatus = row.querySelector('.mat-avail-status');

        const selectedOpt = select.options[select.selectedIndex];
        if (!selectedOpt || !selectedOpt.value) {
            availVal.textContent = '-';
            availStatus.textContent = '';
            unitLabel.textContent = 'units';
            return;
        }

        const avail = parseFloat(selectedOpt.dataset.avail || 0);
        const unit = selectedOpt.dataset.unit || 'units';
        const needed = parseFloat(qtyInput.value || 0);

        unitLabel.textContent = unit;
        availVal.textContent = avail.toFixed(2) + ' ' + unit;

        if (needed > 0) {
            if (avail >= needed) {
                availStatus.textContent = '(Sufficient)';
                availStatus.className = 'mat-avail-status text-[11px] text-emerald-400 font-semibold';
            } else {
                availStatus.textContent = '(Insufficient Stock!)';
                availStatus.className = 'mat-avail-status text-[11px] text-rose-400 font-bold';
            }
        } else {
            availStatus.textContent = '';
        }
    }

    function addRow(productId = '', quantity = '') {
        const html = template.innerHTML.replace(/INDEX/g, rowIndex++);
        const tempDiv = document.createElement('tbody');
        tempDiv.innerHTML = html;
        const row = tempDiv.firstElementChild;

        container.appendChild(row);
        notice.classList.add('hidden');

        const select = row.querySelector('.mat-select');
        const qtyInput = row.querySelector('.mat-qty');
        const removeBtn = row.querySelector('.remove-mat-btn');

        if (productId) select.value = productId;
        if (quantity) qtyInput.value = quantity;

        select.addEventListener('change', () => updateStockBadge(row));
        qtyInput.addEventListener('input', () => updateStockBadge(row));
        removeBtn.addEventListener('click', () => {
            row.remove();
            if (container.children.length === 0) {
                notice.classList.remove('hidden');
            }
        });

        updateStockBadge(row);
        return row;
    }

    document.getElementById('add-material-btn').addEventListener('click', () => addRow());

    // When Product changes, auto-select associated Recipe
    productSelect.addEventListener('change', function () {
        const sel = productSelect.options[productSelect.selectedIndex];
        const defaultRecipe = sel.dataset.recipe;
        if (defaultRecipe && recipeSelect) {
            recipeSelect.value = defaultRecipe;
            recipeSelect.dispatchEvent(new Event('change'));
        }
    });

    // When Recipe changes, auto-populate materials scaled by planned qty
    recipeSelect.addEventListener('change', function () {
        const opt = recipeSelect.options[recipeSelect.selectedIndex];
        if (!opt || !opt.dataset.materials) return;

        try {
            const mats = JSON.parse(opt.dataset.materials);
            container.innerHTML = '';
            const plannedQty = parseFloat(plannedQtyInput.value || 1);

            mats.forEach(m => {
                const reqQty = (m.ratio * plannedQty).toFixed(2);
                addRow(m.product_id, reqQty);
            });
        } catch (e) {
            console.error("Error loading recipe materials", e);
        }
    });

    // When planned quantity updates, scale materials if recipe is active
    plannedQtyInput.addEventListener('input', function () {
        const opt = recipeSelect.options[recipeSelect.selectedIndex];
        if (!opt || !opt.dataset.materials) return;

        try {
            const mats = JSON.parse(opt.dataset.materials);
            const plannedQty = parseFloat(plannedQtyInput.value || 1);
            const rows = container.querySelectorAll('.material-row');

            rows.forEach((row, i) => {
                if (mats[i]) {
                    const qtyInput = row.querySelector('.mat-qty');
                    qtyInput.value = (mats[i].ratio * plannedQty).toFixed(2);
                    updateStockBadge(row);
                }
            });
        } catch (e) {}
    });

    // Trigger initial check if recipe already selected
    if (recipeSelect.value) {
        recipeSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endsection
