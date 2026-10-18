@extends('layouts.app')
@section('title', 'New Recipe / BOM')

@section('content')
<div class="max-w-3xl mx-auto space-y-5" x-data="{
    materials: [
        { material_id: '', quantity: '1' }
    ],
    addRow() {
        this.materials.push({ material_id: '', quantity: '1' });
    },
    removeRow(index) {
        if (this.materials.length > 1) {
            this.materials.splice(index, 1);
        }
    }
}">

    <div class="page-header">
        <div>
            <h1>New Recipe / BOM</h1>
            <p class="text-slate-500 dark:text-dark-400 text-sm mt-1">Specify output manufactured product and raw material components.</p>
        </div>
        <a href="{{ route('recipes.index') }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back
        </a>
    </div>

    <form method="POST" action="{{ route('recipes.store') }}" class="card p-6 space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="form-label">Recipe / Formulation Name <span class="text-rose-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Standard Oak Dining Table Spec" class="form-input @error('name') border-rose-400 @enderror" required>
                @error('name')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label">Manufactured Output Product <span class="text-rose-500">*</span></label>
                <select name="product_id" class="form-input @error('product_id') border-rose-400 @enderror" required>
                    <option value="">Select Target Product</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" {{ old('product_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->name }} ({{ $p->sku }})
                        </option>
                    @endforeach
                </select>
                @error('product_id')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label class="form-label">Description / Instructions</label>
            <textarea name="description" rows="2" placeholder="Production notes or assembly details…" class="form-input">{{ old('description') }}</textarea>
            @error('description')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }} class="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
            <label for="is_active" class="text-sm font-medium text-slate-700 dark:text-dark-300">Active Recipe</label>
        </div>

        <!-- BOM Ingredients Table -->
        <div class="space-y-3 pt-3 border-t border-slate-100 dark:border-dark-800">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-slate-900 dark:text-white text-base">Raw Materials (Bill of Materials)</h3>
                    <p class="text-xs text-slate-500 dark:text-dark-400">Required materials per 1 unit of target product.</p>
                </div>
                <button type="button" @click="addRow()" class="btn-secondary text-xs py-1.5 px-3">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Material
                </button>
            </div>

            <div class="space-y-3">
                <template x-for="(item, index) in materials" :key="index">
                    <div class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-dark-800/60 rounded-lg border border-slate-100 dark:border-dark-800">
                        <div class="flex-1">
                            <label class="block text-xs font-medium text-slate-500 dark:text-dark-400 mb-1">Raw Material</label>
                            <select :name="'materials[' + index + '][material_id]'" x-model="item.material_id" class="form-input text-sm" required>
                                <option value="">Select Material</option>
                                @foreach($materials as $m)
                                    <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->sku }}) - {{ $m->unit?->code }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-32">
                            <label class="block text-xs font-medium text-slate-500 dark:text-dark-400 mb-1">Quantity</label>
                            <input type="number" step="0.001" min="0.001" :name="'materials[' + index + '][quantity]'" x-model="item.quantity" class="form-input text-sm" required>
                        </div>
                        <div class="pt-5">
                            <button type="button" @click="removeRow(index)" class="btn-icon text-slate-400 hover:text-rose-600" title="Remove" :disabled="materials.length === 1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-dark-800">
            <a href="{{ route('recipes.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Save Recipe</button>
        </div>
    </form>

</div>
@endsection
