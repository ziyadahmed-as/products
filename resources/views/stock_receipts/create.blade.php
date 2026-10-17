@extends('layouts.app')
@section('title', 'Record Inbound Stock Receipt')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('stock-receipts.index') }}" class="btn-icon" title="Back to stock receipts">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Record Stock Receipt</h1>
                <p class="text-slate-500 dark:text-dark-400 text-xs mt-0.5">Receive incoming deliveries of raw materials and inventory into a branch storage location.</p>
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

    <form method="POST" action="{{ route('stock-receipts.store') }}" id="receipt-form" class="space-y-6">
        @csrf

        <!-- General Receipt Details -->
        <div class="card p-6 space-y-5">
            <h2 class="text-base font-bold text-slate-900 dark:text-white border-b border-slate-200 dark:border-dark-800 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-sky-100 dark:bg-sky-900/40 text-sky-600 dark:text-sky-400 flex items-center justify-center text-xs font-bold">1</span>
                Receipt Header & Destination
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="form-group">
                    <label class="form-label text-xs">
                        Destination Storage Location <span class="text-rose-500">*</span>
                    </label>
                    <select name="storage_location_id" required class="form-select text-sm">
                        <option value="">-- Select Storage Location --</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ old('storage_location_id') == $loc->id ? 'selected' : '' }}>
                                {{ $loc->name }} ({{ $loc->branch->name ?? 'Main' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label text-xs">
                        Receipt Date <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required
                           class="form-input text-sm">
                </div>

                <div class="form-group">
                    <label class="form-label text-xs">
                        Receipt Ref # <span class="text-slate-400 font-normal text-[11px]">(Auto if blank)</span>
                    </label>
                    <input type="text" name="reference" value="{{ old('reference') }}"
                           class="form-input text-sm font-mono"
                           placeholder="e.g. RCT-20261014-001 or Supplier Inv #">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label text-xs">Receipt Notes / Delivery Slip Reference</label>
                <textarea name="notes" rows="2"
                          class="form-textarea text-sm"
                          placeholder="Vendor details, carrier tracking, physical condition upon receipt, delivery notes...">{{ old('notes') }}</textarea>
            </div>
        </div>

        <!-- Section 2: Items Intake Table -->
        <div class="card p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-dark-800 pb-3">
                <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xs font-bold">2</span>
                    Received Raw Materials & Products
                </h2>
                <button type="button" onclick="addItemRow()" class="btn-secondary text-xs py-1.5 px-3 flex items-center gap-1.5 font-semibold">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Another Material
                </button>
            </div>

            <div class="table-wrapper">
                <table class="table" id="items-table">
                    <thead>
                        <tr>
                            <th class="w-[45%]">Raw Material / Product</th>
                            <th class="text-right w-[20%]">Quantity Received</th>
                            <th class="text-right w-[20%]">Unit Cost (Br)</th>
                            <th class="text-right w-[10%]">Subtotal</th>
                            <th class="text-center w-[5%]"></th>
                        </tr>
                    </thead>
                    <tbody id="items-body">
                        <!-- Default Row 0 -->
                        <tr class="item-row">
                            <td>
                                <select name="items[0][product_id]" required onchange="handleProductSelect(this, 0)"
                                        class="form-select text-xs">
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
                                <span class="text-[11px] text-slate-500 dark:text-dark-500 block mt-0.5" id="unit-hint-0">
                                    {{ isset($preselectedProduct) ? 'Unit: ' . ($preselectedProduct->unit?->name ?? 'units') . ' | Current stock: ' . number_format($preselectedProduct->quantity, 1) : '' }}
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <input type="number" step="any" min="0.0001" name="items[0][quantity]" required
                                           value="{{ old('items.0.quantity', 1) }}" oninput="recalcRow(0)" id="qty-0"
                                           class="form-input text-xs font-mono text-right w-24">
                                    <span class="text-xs text-slate-500 dark:text-dark-400 w-8" id="unit-lbl-0">{{ isset($preselectedProduct) ? ($preselectedProduct->unit?->code ?? '') : '' }}</span>
                                </div>
                            </td>
                            <td class="text-right">
                                <input type="number" step="any" min="0" name="items[0][unit_cost]"
                                       value="{{ old('items.0.unit_cost', isset($preselectedProduct) ? ($preselectedProduct->purchase_cost ?? 0) : 0) }}"
                                       oninput="recalcRow(0)" id="cost-0"
                                       class="form-input text-xs font-mono text-right w-28">
                            </td>
                            <td class="text-right font-mono text-xs font-bold text-slate-900 dark:text-white" id="subtotal-0">
                                Br 0.00
                            </td>
                            <td class="text-center">
                                <button type="button" onclick="removeRow(this)" class="btn-icon hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-950/30" title="Remove">
                                    <svg class="w-4 h-4 text-slate-400 hover:text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-slate-200 dark:border-dark-800 bg-slate-50/50 dark:bg-dark-950/40">
                            <td colspan="3" class="py-3 px-4 text-right text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-dark-300">
                                Total Receipt Valuation:
                            </td>
                            <td class="py-3 px-3 text-right font-mono text-sm font-bold text-emerald-600 dark:text-emerald-400" id="total-val">
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
            <button type="submit" class="btn-primary px-6 py-2.5 text-xs font-semibold shadow-xs flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Complete Receipt & Update Inventory</span>
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
        <td>
            <select name="items[${idx}][product_id]" required onchange="handleProductSelect(this, ${idx})"
                    class="form-select text-xs">
                ${productOptions}
            </select>
            <span class="text-[11px] text-slate-500 dark:text-dark-500 block mt-0.5" id="unit-hint-${idx}"></span>
        </td>
        <td class="text-right">
            <div class="flex items-center justify-end gap-1">
                <input type="number" step="any" min="0.0001" name="items[${idx}][quantity]" required
                       value="1" oninput="recalcRow(${idx})" id="qty-${idx}"
                       class="form-input text-xs font-mono text-right w-24">
                <span class="text-xs text-slate-500 dark:text-dark-400 w-8" id="unit-lbl-${idx}"></span>
            </div>
        </td>
        <td class="text-right">
            <input type="number" step="any" min="0" name="items[${idx}][unit_cost]" value="0"
                   oninput="recalcRow(${idx})" id="cost-${idx}"
                   class="form-input text-xs font-mono text-right w-28">
        </td>
        <td class="text-right font-mono text-xs font-bold text-slate-900 dark:text-white" id="subtotal-${idx}">
            Br 0.00
        </td>
        <td class="text-center">
            <button type="button" onclick="removeRow(this)" class="btn-icon hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-950/30" title="Remove">
                <svg class="w-4 h-4 text-slate-400 hover:text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
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
