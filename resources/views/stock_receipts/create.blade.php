@extends('layouts.app')
@section('title', 'Record Inbound Stock Receipt')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('stock-receipts.index') }}" class="p-2 rounded-xl bg-dark-900 border border-dark-800 text-dark-400 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight">Record Stock Receipt</h1>
                <p class="text-dark-400 text-xs mt-0.5">Receive incoming deliveries of raw materials and inventory into a branch storage location.</p>
            </div>
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

    <form method="POST" action="{{ route('stock-receipts.store') }}" id="receipt-form" class="space-y-6">
        @csrf

        <!-- General Receipt Details -->
        <div class="card p-6 bg-dark-900 border border-dark-800 rounded-2xl space-y-5">
            <h2 class="text-base font-bold text-white border-b border-dark-800 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-primary-600/30 text-primary-400 flex items-center justify-center text-xs font-bold">1</span>
                Receipt Header & Destination
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-dark-300 mb-1.5">
                        Destination Storage Location <span class="text-rose-400">*</span>
                    </label>
                    <select name="storage_location_id" required class="w-full px-3 py-2 rounded-lg bg-dark-950 border border-dark-800 text-white text-sm focus:border-primary-500 focus:outline-none">
                        <option value="">-- Select Storage Location --</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ old('storage_location_id') == $loc->id ? 'selected' : '' }}>
                                {{ $loc->name }} ({{ $loc->branch->name ?? 'Main' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-dark-300 mb-1.5">
                        Receipt Date <span class="text-rose-400">*</span>
                    </label>
                    <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required
                           class="w-full px-3 py-2 rounded-lg bg-dark-950 border border-dark-800 text-white text-sm focus:border-primary-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-medium text-dark-300 mb-1.5">
                        Receipt Ref # <span class="text-dark-500">(Auto if blank)</span>
                    </label>
                    <input type="text" name="reference" value="{{ old('reference') }}"
                           class="w-full px-3 py-2 rounded-lg bg-dark-950 border border-dark-800 text-white text-sm font-mono focus:border-primary-500 focus:outline-none"
                           placeholder="e.g. RCT-20261014-001 or Supplier Inv #">
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-dark-300 mb-1.5">Receipt Notes / Delivery Slip Reference</label>
                <textarea name="notes" rows="2"
                          class="w-full px-3 py-2 rounded-lg bg-dark-950 border border-dark-800 text-white text-sm focus:border-primary-500 focus:outline-none"
                          placeholder="Vendor details, carrier tracking, physical condition upon receipt, delivery notes...">{{ old('notes') }}</textarea>
            </div>
        </div>

        <!-- Section 2: Items Intake Table -->
        <div class="card p-6 bg-dark-900 border border-dark-800 rounded-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-dark-800 pb-3">
                <h2 class="text-base font-bold text-white flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-emerald-600/30 text-emerald-400 flex items-center justify-center text-xs font-bold">2</span>
                    Received Raw Materials & Products
                </h2>
                <button type="button" onclick="addItemRow()" class="text-xs px-3 py-1.5 rounded-lg bg-primary-600/20 text-primary-300 hover:bg-primary-600/30 border border-primary-500/30 flex items-center gap-1.5 transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Another Material
                </button>
            </div>

            <div class="table-wrapper">
                <table class="table" id="items-table">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wider text-dark-400 bg-dark-950/60 border-b border-dark-800">
                            <th class="py-2.5 px-3 w-[45%]">Raw Material / Product</th>
                            <th class="py-2.5 px-3 text-right w-[20%]">Quantity Received</th>
                            <th class="py-2.5 px-3 text-right w-[20%]">Unit Cost (Br)</th>
                            <th class="py-2.5 px-3 text-right w-[10%]">Subtotal</th>
                            <th class="py-2.5 px-3 text-center w-[5%]"></th>
                        </tr>
                    </thead>
                    <tbody id="items-body" class="divide-y divide-dark-800 text-sm">
                        <!-- Default Row 0 -->
                        <tr class="item-row">
                            <td class="py-2.5 px-3">
                                <select name="items[0][product_id]" required onchange="handleProductSelect(this, 0)"
                                        class="w-full px-2.5 py-1.5 rounded-lg bg-dark-950 border border-dark-800 text-white text-xs focus:border-primary-500 focus:outline-none">
                                    <option value="">-- Choose Raw Material / Product --</option>
                                    @foreach($products as $p)
                                        <option value="{{ $p->id }}"
                                                data-cost="{{ $p->purchase_cost ?? 0 }}"
                                                data-unit="{{ $p->unit->code ?? 'units' }}"
                                                data-sku="{{ $p->sku }}"
                                                data-stock="{{ $p->quantity }}"
                                                {{ (old('items.0.product_id') == $p->id || (isset($preselectedProduct) && $preselectedProduct->id == $p->id)) ? 'selected' : '' }}>
                                            [{{ $p->type === 'raw_material' ? 'RAW' : strtoupper(substr($p->type, 0, 3)) }}] {{ $p->name }} ({{ $p->sku }}) [Stock: {{ number_format($p->quantity, 1) }} {{ $p->unit?->code ?? '' }}]
                                        </option>
                                    @endforeach
                                </select>
                                <span class="text-[11px] text-dark-500 block mt-0.5" id="unit-hint-0">
                                    {{ isset($preselectedProduct) ? 'Unit: ' . ($preselectedProduct->unit?->name ?? 'units') . ' | Current stock: ' . number_format($preselectedProduct->quantity, 1) : '' }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <input type="number" step="any" min="0.0001" name="items[0][quantity]" required
                                           value="{{ old('items.0.quantity', 1) }}" oninput="recalcRow(0)" id="qty-0"
                                           class="w-24 px-2.5 py-1.5 rounded-lg bg-dark-950 border border-dark-800 text-white text-xs font-mono text-right focus:border-primary-500 focus:outline-none">
                                    <span class="text-xs text-dark-400 w-8" id="unit-lbl-0">{{ isset($preselectedProduct) ? ($preselectedProduct->unit?->code ?? '') : '' }}</span>
                                </div>
                            </td>
                            <td class="py-2.5 px-3 text-right">
                                <input type="number" step="any" min="0" name="items[0][unit_cost]"
                                       value="{{ old('items.0.unit_cost', isset($preselectedProduct) ? ($preselectedProduct->purchase_cost ?? 0) : 0) }}"
                                       oninput="recalcRow(0)" id="cost-0"
                                       class="w-28 px-2.5 py-1.5 rounded-lg bg-dark-950 border border-dark-800 text-white text-xs font-mono text-right focus:border-primary-500 focus:outline-none">
                            </td>
                            <td class="py-2.5 px-3 text-right font-mono text-xs font-bold text-white" id="subtotal-0">
                                Br 0.00
                            </td>
                            <td class="py-2.5 px-3 text-center">
                                <button type="button" onclick="removeRow(this)" class="text-dark-500 hover:text-rose-400 p-1" title="Remove">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-dark-800 bg-dark-950/40">
                            <td colspan="3" class="py-3 px-4 text-right text-xs font-bold uppercase tracking-wider text-dark-300">
                                Total Receipt Valuation:
                            </td>
                            <td class="py-3 px-3 text-right font-mono text-sm font-bold text-emerald-400" id="total-val">
                                Br 0.00
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Submit & Actions -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('stock-receipts.index') }}" class="btn-secondary px-5 py-2.5 text-xs">Cancel</a>
            <button type="submit" class="btn-primary px-6 py-2.5 text-xs font-semibold shadow-lg shadow-primary-600/30 flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Complete Receipt & Update Inventory
            </button>
        </div>
    </form>

</div>

<script>
let rowCount = 1;

const productOptions = `
    <option value="">-- Choose Raw Material / Product --</option>
    @foreach($products as $p)
        <option value="{{ $p->id }}"
                data-cost="{{ $p->purchase_cost ?? 0 }}"
                data-unit="{{ $p->unit->code ?? 'units' }}"
                data-sku="{{ $p->sku }}"
                data-stock="{{ $p->quantity }}">
            [{{ $p->type === 'raw_material' ? 'RAW' : strtoupper(substr($p->type, 0, 3)) }}] {{ $p->name }} ({{ $p->sku }}) [Stock: {{ number_format($p->quantity, 1) }} {{ $p->unit?->code ?? '' }}]
        </option>
    @endforeach
`;

function handleProductSelect(select, idx) {
    const selected = select.options[select.selectedIndex];
    const cost = selected.getAttribute('data-cost') || 0;
    const unit = selected.getAttribute('data-unit') || '';
    const stock = selected.getAttribute('data-stock') || 0;

    const costInput = document.getElementById(`cost-${idx}`);
    if (costInput && (!costInput.value || costInput.value == 0)) {
        costInput.value = cost;
    }

    const unitLbl = document.getElementById(`unit-lbl-${idx}`);
    if (unitLbl) unitLbl.textContent = unit;

    const hint = document.getElementById(`unit-hint-${idx}`);
    if (hint) {
        hint.textContent = selected.value ? `Unit: ${unit} | Current stock: ${parseFloat(stock).toFixed(2)}` : '';
    }

    recalcRow(idx);
}

function recalcRow(idx) {
    const qtyInput = document.getElementById(`qty-${idx}`);
    const costInput = document.getElementById(`cost-${idx}`);
    const subtotalCell = document.getElementById(`subtotal-${idx}`);

    const qty = parseFloat(qtyInput?.value || 0);
    const cost = parseFloat(costInput?.value || 0);
    const subtotal = qty * cost;

    if (subtotalCell) {
        subtotalCell.textContent = 'Br ' + subtotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    recalcTotal();
}

function recalcTotal() {
    let grandTotal = 0;
    document.querySelectorAll('.item-row').forEach(row => {
        const qty = parseFloat(row.querySelector('input[name*="[quantity]"]')?.value || 0);
        const cost = parseFloat(row.querySelector('input[name*="[unit_cost]"]')?.value || 0);
        grandTotal += (qty * cost);
    });

    const totalEl = document.getElementById('total-val');
    if (totalEl) {
        totalEl.textContent = 'Br ' + grandTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
}

function addItemRow() {
    const idx = rowCount++;
    const tbody = document.getElementById('items-body');
    const tr = document.createElement('tr');
    tr.className = 'item-row';
    tr.innerHTML = `
        <td class="py-2.5 px-3">
            <select name="items[${idx}][product_id]" required onchange="handleProductSelect(this, ${idx})"
                    class="w-full px-2.5 py-1.5 rounded-lg bg-dark-950 border border-dark-800 text-white text-xs focus:border-primary-500 focus:outline-none">
                ${productOptions}
            </select>
            <span class="text-[11px] text-dark-500 block mt-0.5" id="unit-hint-${idx}"></span>
        </td>
        <td class="py-2.5 px-3 text-right">
            <div class="flex items-center justify-end gap-1">
                <input type="number" step="any" min="0.0001" name="items[${idx}][quantity]" required
                       value="1" oninput="recalcRow(${idx})" id="qty-${idx}"
                       class="w-24 px-2.5 py-1.5 rounded-lg bg-dark-950 border border-dark-800 text-white text-xs font-mono text-right focus:border-primary-500 focus:outline-none">
                <span class="text-xs text-dark-400 w-8" id="unit-lbl-${idx}"></span>
            </div>
        </td>
        <td class="py-2.5 px-3 text-right">
            <input type="number" step="any" min="0" name="items[${idx}][unit_cost]" value="0"
                   oninput="recalcRow(${idx})" id="cost-${idx}"
                   class="w-28 px-2.5 py-1.5 rounded-lg bg-dark-950 border border-dark-800 text-white text-xs font-mono text-right focus:border-primary-500 focus:outline-none">
        </td>
        <td class="py-2.5 px-3 text-right font-mono text-xs font-bold text-white" id="subtotal-${idx}">
            Br 0.00
        </td>
        <td class="py-2.5 px-3 text-center">
            <button type="button" onclick="removeRow(this)" class="text-dark-500 hover:text-rose-400 p-1" title="Remove">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
        </td>
    `;
    tbody.appendChild(tr);
}

function removeRow(btn) {
    const rows = document.querySelectorAll('.item-row');
    if (rows.length <= 1) {
        alert('You must have at least one material item in the receipt.');
        return;
    }
    btn.closest('tr').remove();
    recalcTotal();
}

document.addEventListener('DOMContentLoaded', () => {
    recalcRow(0);
});
</script>
@endsection
