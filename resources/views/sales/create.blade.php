@extends('layouts.app')
@section('title', 'New Sale — POS')

@section('content')
<div class="space-y-6" x-data="posApp()" x-init="init()">

    {{-- Header --}}
    <div class="page-header flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Point of Sale</h1>
            <p class="text-dark-400 text-sm mt-1">
                Quick sale terminal — {{ auth()->user()->name }} &bull;
                @if(auth()->user()->hasRole('Seller'))
                    Branch: <span class="text-primary-300 font-medium">{{ $defaultBranch?->name ?? 'Unassigned' }}</span>
                @else
                    Select branch below
                @endif
            </p>
        </div>
        <a href="{{ route('sales.index') }}" class="btn-secondary flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
            </svg>
            View All Sales
        </a>
    </div>

    @if (session('success'))
        <div class="alert-success flex items-center gap-3 p-4 rounded-xl bg-emerald-950/70 border border-emerald-700 text-emerald-300">
            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="flex items-center gap-3 p-4 rounded-xl bg-rose-950/70 border border-rose-700 text-rose-300">
            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('sales.store') }}" id="sale-form">
        @csrf
        <div class="grid grid-cols-1 xl:grid-cols-5 gap-6">

            {{-- LEFT PANEL: Product Search & Cart --}}
            <div class="xl:col-span-3 space-y-4">

                {{-- Branch / Location Selector (admin/manager only) --}}
                @hasanyrole('Super Admin|Manager')
                <div class="card p-4 bg-dark-900 border border-dark-800 rounded-2xl">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="form-group mb-0">
                            <label class="form-label text-xs">Branch <span class="text-rose-400">*</span></label>
                            <select name="branch_id" id="branch_id" class="form-input text-sm" x-model="selectedBranchId" @change="fetchLocations()">
                                <option value="">— Select Branch —</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            <label class="form-label text-xs">Storage Location</label>
                            <select name="storage_location_id" id="storage_location_id" class="form-input text-sm">
                                <option value="">— Auto (Branch Default) —</option>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}" {{ old('storage_location_id') == $loc->id ? 'selected' : '' }}>
                                        {{ $loc->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                @else
                    {{-- Seller: hidden branch auto-resolved --}}
                    <input type="hidden" name="branch_id" value="{{ $defaultBranch?->id }}">
                @endhasanyrole

                {{-- Product Search --}}
                <div class="card p-4 bg-dark-900 border border-dark-800 rounded-2xl">
                    <label class="form-label text-xs mb-2">Search & Add Products</label>
                    <div class="relative">
                        <input type="text" id="product-search" placeholder="Search by product name or SKU..."
                               class="form-input pl-10 text-sm w-full"
                               autocomplete="off"
                               x-on:input.debounce.300ms="searchProducts($event.target.value)">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>

                    {{-- Search Results Dropdown --}}
                    <div x-show="searchResults.length > 0" x-cloak
                         class="mt-2 rounded-xl border border-dark-700 bg-dark-800 divide-y divide-dark-700 overflow-hidden shadow-xl max-h-64 overflow-y-auto">
                        <template x-for="product in searchResults" :key="product.id">
                            <button type="button"
                                    class="w-full text-left px-4 py-3 hover:bg-dark-700/80 transition flex items-center justify-between"
                                    @click="addToCart(product)">
                                <div>
                                    <div class="text-sm font-medium text-white" x-text="product.name"></div>
                                    <div class="text-xs text-dark-400 mt-0.5">
                                        SKU: <span x-text="product.sku || '—'"></span> &bull;
                                        Stock: <span x-text="product.quantity" :class="product.quantity <= 0 ? 'text-rose-400' : 'text-emerald-400'"></span>
                                        <span x-text="product.unit_code"></span>
                                    </div>
                                </div>
                                <div class="text-right flex-shrink-0 ml-4">
                                    <div class="text-sm font-bold text-white">Br<span x-text="formatMoney(product.selling_price)"></span></div>
                                    <div class="text-xs text-dark-500">per unit</div>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Cart / Sale Lines --}}
                <div class="card p-0 bg-dark-900 border border-dark-800 rounded-2xl overflow-hidden">
                    <div class="p-4 border-b border-dark-800 flex items-center justify-between">
                        <h2 class="text-base font-bold text-white">Cart Items</h2>
                        <span class="text-xs text-dark-400" x-text="cart.length + ' item(s) in cart'"></span>
                    </div>

                    {{-- Empty Cart Message --}}
                    <div x-show="cart.length === 0" class="p-12 text-center text-dark-500">
                        <svg class="w-10 h-10 mx-auto mb-3 text-dark-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <p class="text-sm">No items in cart. Search and add products above.</p>
                    </div>

                    {{-- Cart Table --}}
                    <div x-show="cart.length > 0" class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-xs uppercase tracking-wider text-dark-400 bg-dark-950/60 border-b border-dark-800">
                                    <th class="py-3 px-4">Product</th>
                                    <th class="py-3 px-4 text-right">Unit Price</th>
                                    <th class="py-3 px-4 text-center">Qty</th>
                                    <th class="py-3 px-4 text-right">Line Total</th>
                                    <th class="py-3 px-4 text-center">Remove</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-dark-800">
                                <template x-for="(item, index) in cart" :key="item.product_id">
                                    <tr class="hover:bg-dark-800/40 transition">
                                        <td class="py-3 px-4">
                                            <div class="font-medium text-white text-sm" x-text="item.name"></div>
                                            <div class="text-xs text-dark-500 mt-0.5">SKU: <span x-text="item.sku || '—'"></span></div>
                                            {{-- Hidden inputs for form submission --}}
                                            <input type="hidden" :name="'lines[' + index + '][product_id]'" :value="item.product_id">
                                            <input type="hidden" :name="'lines[' + index + '][unit_price]'" :value="item.unit_price">
                                            <input type="hidden" :name="'lines[' + index + '][quantity]'" :value="item.quantity">
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <input type="number" step="0.01" min="0"
                                                   class="w-28 text-right bg-dark-800 border border-dark-700 rounded-lg px-2 py-1 text-sm text-white focus:outline-none focus:border-primary-500"
                                                   x-model.number="item.unit_price"
                                                   @input="calculateTotals()">
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <div class="flex items-center justify-center gap-1">
                                                <button type="button"
                                                        class="w-7 h-7 rounded-lg bg-dark-700 hover:bg-dark-600 text-white text-sm font-bold transition flex items-center justify-center"
                                                        @click="decrementQty(index)">−</button>
                                                <input type="number" step="0.01" min="0.01"
                                                       class="w-16 text-center bg-dark-800 border border-dark-700 rounded-lg px-1 py-1 text-sm text-white focus:outline-none focus:border-primary-500"
                                                       x-model.number="item.quantity"
                                                       @input="calculateTotals()">
                                                <button type="button"
                                                        class="w-7 h-7 rounded-lg bg-dark-700 hover:bg-dark-600 text-white text-sm font-bold transition flex items-center justify-center"
                                                        @click="incrementQty(index)">+</button>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 text-right font-bold text-white whitespace-nowrap">
                                            Br<span x-text="formatMoney(item.unit_price * item.quantity)"></span>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <button type="button" @click="removeFromCart(index)"
                                                    class="w-7 h-7 rounded-lg bg-rose-900/50 hover:bg-rose-700/50 text-rose-400 transition flex items-center justify-center mx-auto">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            {{-- RIGHT PANEL: Order Summary, Customer Info, Payment --}}
            <div class="xl:col-span-2 space-y-4">

                {{-- Order Summary --}}
                <div class="card p-5 bg-dark-900 border border-dark-800 rounded-2xl">
                    <h2 class="text-base font-bold text-white mb-4">Order Summary</h2>

                    <div class="space-y-2 text-sm">
                        <div class="flex items-center justify-between text-dark-300">
                            <span>Subtotal</span>
                            <span class="font-mono">Br<span x-text="formatMoney(subtotal)"></span></span>
                        </div>
                        <div class="flex items-center justify-between text-dark-300">
                            <span>Discount</span>
                            <div class="flex items-center gap-2">
                                <span class="text-dark-500">Br</span>
                                <input type="number" name="discount" id="discount" step="0.01" min="0"
                                       class="w-24 text-right bg-dark-800 border border-dark-700 rounded-lg px-2 py-1 text-sm text-rose-300 focus:outline-none focus:border-primary-500"
                                       x-model.number="discount" @input="calculateTotals()">
                            </div>
                        </div>
                        <div class="flex items-center justify-between text-dark-300">
                            <span>Tax</span>
                            <div class="flex items-center gap-2">
                                <span class="text-dark-500">Br</span>
                                <input type="number" name="tax" id="tax" step="0.01" min="0"
                                       class="w-24 text-right bg-dark-800 border border-dark-700 rounded-lg px-2 py-1 text-sm text-amber-300 focus:outline-none focus:border-primary-500"
                                       x-model.number="tax" @input="calculateTotals()">
                            </div>
                        </div>
                        <div class="h-px bg-dark-800 my-2"></div>
                        <div class="flex items-center justify-between text-white font-bold text-base">
                            <span>Total</span>
                            <span class="text-emerald-400 text-xl font-bold">Br<span x-text="formatMoney(total)"></span></span>
                        </div>
                        {{-- Hidden totals for form --}}
                        <input type="hidden" name="subtotal" :value="subtotal">
                        <input type="hidden" name="total" :value="total">
                    </div>

                    {{-- Payment --}}
                    <div class="mt-4 pt-4 border-t border-dark-800 space-y-3">
                        <div class="form-group mb-0">
                            <label class="form-label text-xs">Payment Status <span class="text-rose-400">*</span></label>
                            <select name="payment_status" id="payment_status" class="form-input text-sm" x-model="paymentStatus" @change="updatePaidAmount()">
                                <option value="Unpaid">Unpaid (Credit / Later)</option>
                                <option value="Partial">Partial Payment</option>
                                <option value="Paid">Fully Paid (Cash/Card)</option>
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            <label class="form-label text-xs">Amount Paid (Br)</label>
                            <input type="number" name="paid_amount" id="paid_amount" step="0.01" min="0"
                                   class="form-input text-sm"
                                   x-model.number="paidAmount"
                                   :max="total"
                                   :readonly="paymentStatus === 'Unpaid'"
                                   :class="paymentStatus === 'Unpaid' ? 'opacity-40 cursor-not-allowed' : ''">
                        </div>
                        <div x-show="paymentStatus !== 'Unpaid' && paidAmount > 0 && total > 0"
                             class="p-3 rounded-xl bg-dark-800/60 text-xs">
                            <div class="flex justify-between text-dark-300">
                                <span>Change / Balance Due:</span>
                                <span :class="paidAmount >= total ? 'text-emerald-400 font-bold' : 'text-rose-400 font-bold'">
                                    Br<span x-text="formatMoney(Math.abs(paidAmount - total))"></span>
                                    <span x-text="paidAmount >= total ? '(Change)' : '(Remaining)'"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Customer Information --}}
                <div class="card p-5 bg-dark-900 border border-dark-800 rounded-2xl">
                    <h2 class="text-base font-bold text-white mb-4">Customer Information</h2>
                    <div class="space-y-3">
                        <div class="form-group mb-0">
                            <label class="form-label text-xs">Customer Name</label>
                            <input type="text" name="customer_name" id="customer_name" value="{{ old('customer_name') }}"
                                   class="form-input text-sm" placeholder="Mohammed" list="sale_customer_name_suggestions">
                            <datalist id="sale_customer_name_suggestions">
                                <option value="Mohammed">
                            </datalist>
                        </div>
                        <div class="form-group mb-0">
                            <label class="form-label text-xs">Phone Number <span class="text-slate-400 font-normal">(+251...)</span></label>
                            <input type="text" name="customer_phone" id="customer_phone" value="{{ old('customer_phone') }}"
                                   class="form-input text-sm @error('customer_phone') border-rose-400 @enderror"
                                   placeholder="+251912345678"
                                   pattern="^\+251[0-9]{9}$"
                                   title="Must start with +251 followed by 9 digits (total 12 digits, e.g. +251912345678)">
                            <span class="text-[11px] text-gray-400 mt-1 block">Format: +251 followed by 9 digits (e.g. +251912345678)</span>
                            @error('customer_phone')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="form-group mb-0">
                            <label class="form-label text-xs">Order Notes</label>
                            <textarea name="order_notes" id="order_notes" rows="2"
                                      class="form-input text-sm resize-none"
                                      placeholder="Special instructions, delivery notes...">{{ old('order_notes') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="space-y-2">
                    <button type="submit"
                            :disabled="cart.length === 0"
                            :class="cart.length === 0 ? 'opacity-40 cursor-not-allowed' : ''"
                            class="btn-primary w-full text-base py-3 flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Confirm Sale
                    </button>
                    <button type="button" @click="clearCart()"
                            :disabled="cart.length === 0"
                            :class="cart.length === 0 ? 'opacity-40 cursor-not-allowed' : ''"
                            class="btn-secondary w-full flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Clear Cart
                    </button>
                </div>

            </div>
        </div>
    </form>
</div>

<script>
function posApp() {
    return {
        cart: [],
        searchResults: [],
        subtotal: 0,
        discount: 0,
        tax: 0,
        total: 0,
        paymentStatus: 'Paid',
        paidAmount: 0,
        selectedBranchId: '',

        init() {
            this.calculateTotals();
        },

        async searchProducts(query) {
            if (!query || query.trim().length < 2) {
                this.searchResults = [];
                return;
            }
            try {
                const params = new URLSearchParams({ q: query });
                @hasanyrole('Super Admin|Manager')
                if (this.selectedBranchId) params.set('branch_id', this.selectedBranchId);
                @endhasanyrole
                const res = await fetch('/api/products/search?' + params.toString(), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (res.ok) {
                    this.searchResults = await res.json();
                }
            } catch (e) {
                console.error('Product search failed:', e);
            }
        },

        addToCart(product) {
            const existing = this.cart.find(i => i.product_id === product.id);
            if (existing) {
                existing.quantity += 1;
            } else {
                this.cart.push({
                    product_id: product.id,
                    name: product.name,
                    sku: product.sku,
                    unit_price: parseFloat(product.selling_price) || 0,
                    quantity: 1,
                    unit_code: product.unit_code || 'unit',
                    max_stock: parseFloat(product.quantity) || 999,
                });
            }
            this.searchResults = [];
            document.getElementById('product-search').value = '';
            this.calculateTotals();
        },

        removeFromCart(index) {
            this.cart.splice(index, 1);
            this.calculateTotals();
        },

        clearCart() {
            this.cart = [];
            this.discount = 0;
            this.tax = 0;
            this.calculateTotals();
        },

        incrementQty(index) {
            this.cart[index].quantity = parseFloat(this.cart[index].quantity) + 1;
            this.calculateTotals();
        },

        decrementQty(index) {
            if (this.cart[index].quantity > 1) {
                this.cart[index].quantity = parseFloat(this.cart[index].quantity) - 1;
            } else {
                this.removeFromCart(index);
            }
            this.calculateTotals();
        },

        calculateTotals() {
            this.subtotal = this.cart.reduce((sum, item) => {
                return sum + (parseFloat(item.unit_price) * parseFloat(item.quantity));
            }, 0);
            const disc = parseFloat(this.discount) || 0;
            const tx   = parseFloat(this.tax) || 0;
            this.total = Math.max(0, this.subtotal - disc + tx);
            this.updatePaidAmount();
        },

        updatePaidAmount() {
            if (this.paymentStatus === 'Unpaid') {
                this.paidAmount = 0;
            } else if (this.paymentStatus === 'Paid') {
                this.paidAmount = this.total;
            }
            // Partial: leave paidAmount as-is for manual entry
        },

        formatMoney(value) {
            return parseFloat(value || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        },

        async fetchLocations() {
            // Refresh storage location dropdown when branch changes
            const select = document.getElementById('storage_location_id');
            if (!select || !this.selectedBranchId) return;
            try {
                const res = await fetch('/api/branches/' + this.selectedBranchId + '/locations', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (res.ok) {
                    const locations = await res.json();
                    select.innerHTML = '<option value="">— Auto (Branch Default) —</option>';
                    locations.forEach(loc => {
                        const opt = document.createElement('option');
                        opt.value = loc.id;
                        opt.textContent = loc.name;
                        select.appendChild(opt);
                    });
                }
            } catch (e) { /* silently fail */ }
        }
    };
}
</script>
@endsection
