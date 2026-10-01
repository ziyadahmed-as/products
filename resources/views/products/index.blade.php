@extends('layouts.app')
@section('title', 'Products')

@section('content')
<div class="space-y-5">

    <!-- Header -->
    <div class="page-header">
        <div>
            <h1>Products</h1>
            <p class="text-dark-400 text-sm mt-1">Manage all resale products, raw materials, and manufactured goods.</p>
        </div>
        <a href="{{ route('products.create') }}" class="btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Product
        </a>
    </div>

    <!-- Filters -->
    <div class="card">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="form-group flex-1 min-w-[160px]">
                <label class="form-label">Search</label>
                <input name="search" value="{{ request('search') }}" type="text"
                       class="form-input" placeholder="Name, SKU...">
            </div>
            <div class="form-group min-w-[140px]">
                <label class="form-label">Type</label>
                <select name="type" class="form-select">
                    <option value="">All Types</option>
                    <option value="resale_product"      {{ request('type') === 'resale_product' ? 'selected' : '' }}>Resale</option>
                    <option value="raw_material"        {{ request('type') === 'raw_material' ? 'selected' : '' }}>Raw Material</option>
                    <option value="manufactured_product"{{ request('type') === 'manufactured_product' ? 'selected' : '' }}>Manufactured</option>
                </select>
            </div>
            <div class="form-group min-w-[140px]">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-select">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary">Filter</button>
                <a href="{{ route('products.index') }}" class="btn-secondary">Reset</a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="card p-0">
        <div class="table-wrapper rounded-2xl">
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>SKU</th>
                        <th>Type</th>
                        <th>Category</th>
                        <th>Unit</th>
                        <th>Cost</th>
                        <th>Price</th>
                        <th>Min Stock</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                @if($product->image)
                                    <img src="{{ asset('storage/'.$product->image) }}" class="w-8 h-8 rounded-lg object-cover">
                                @else
                                    <div class="w-8 h-8 rounded-lg bg-dark-800 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                                        </svg>
                                    </div>
                                @endif
                                <span class="font-medium text-white">{{ $product->name }}</span>
                            </div>
                        </td>
                        <td class="font-mono text-primary-400 text-xs">{{ $product->sku }}</td>
                        <td>
                            @if($product->type === 'resale_product')
                                <span class="badge-blue">Resale</span>
                            @elseif($product->type === 'raw_material')
                                <span class="badge-yellow">Raw Material</span>
                            @else
                                <span class="badge-purple">Manufactured</span>
                            @endif
                        </td>
                        <td class="text-dark-300">{{ $product->category->name ?? '—' }}</td>
                        <td class="text-dark-300">{{ $product->unit->code ?? '—' }}</td>
                        <td class="text-dark-300">${{ number_format($product->purchase_cost ?? 0, 2) }}</td>
                        <td class="font-semibold text-white">${{ number_format($product->selling_price ?? 0, 2) }}</td>
                        <td class="text-dark-300">{{ $product->minimum_stock_level }}</td>
                        <td>
                            @if($product->is_active)
                                <span class="badge-green">Active</span>
                            @else
                                <span class="badge-gray">Inactive</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('products.edit', $product) }}" class="btn-icon" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <form method="POST" action="{{ route('products.destroy', $product) }}"
                                      onsubmit="return confirm('Deactivate this product?')">
                                    @csrf @method('DELETE')
                                    <button class="btn-icon" title="Deactivate">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-12">
                            <div class="w-14 h-14 rounded-2xl bg-dark-800 flex items-center justify-center mx-auto mb-3">
                                <svg class="w-7 h-7 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                                </svg>
                            </div>
                            <p class="text-dark-500 font-medium">No products found</p>
                            <p class="text-dark-600 text-sm mt-1">Start by adding your first product</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())
        <div class="px-6 py-4 border-t border-dark-800">
            {{ $products->withQueryString()->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
