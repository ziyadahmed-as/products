@extends('layouts.app')
@section('title', 'Category — ' . $category->name)

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="page-header">
        <div>
            <div class="flex items-center gap-3">
                <h1>{{ $category->name }}</h1>
                @if($category->is_active)
                    <span class="badge badge-green">Active</span>
                @else
                    <span class="badge badge-red">Inactive</span>
                @endif
                <span class="badge badge-blue">{{ $category->products_count }} Products</span>
            </div>
            <p class="text-slate-500 dark:text-dark-400 text-sm mt-1">Slug: <span class="font-mono">{{ $category->slug }}</span></p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('categories.edit', $category) }}" class="btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Category
            </a>
            <a href="{{ route('categories.index') }}" class="btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to Categories
            </a>
        </div>
    </div>

    @if($category->description)
        <div class="card p-5">
            <h3 class="text-xs uppercase font-semibold text-slate-400 dark:text-dark-500 mb-1">Description</h3>
            <p class="text-sm text-slate-700 dark:text-dark-300">{{ $category->description }}</p>
        </div>
    @endif

    <!-- Associated Products Table -->
    <div class="card overflow-hidden">
        <div class="p-4 border-b border-slate-100 dark:border-dark-800 flex items-center justify-between">
            <h3 class="font-semibold text-slate-900 dark:text-white">Products in this Category</h3>
            <span class="badge badge-blue">{{ $products->total() }} Total</span>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Type</th>
                    <th>SKU</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr>
                    <td>
                        <a href="{{ route('products.show', $product) }}" class="font-semibold text-slate-900 dark:text-white hover:text-cyan-600 transition-colors text-sm">
                            {{ $product->name }}
                        </a>
                    </td>
                    <td>
                        <span class="badge badge-blue text-xs capitalize">{{ str_replace('_', ' ', $product->type) }}</span>
                    </td>
                    <td class="font-mono text-xs text-slate-500 dark:text-dark-400">{{ $product->sku }}</td>
                    <td class="text-sm font-semibold text-slate-900 dark:text-white">${{ number_format($product->selling_price, 2) }}</td>
                    <td>
                        @if($product->quantity <= 5)
                            <span class="badge badge-red">{{ $product->quantity }} Low</span>
                        @else
                            <span class="badge badge-green">{{ $product->quantity }}</span>
                        @endif
                    </td>
                    <td class="text-right">
                        <a href="{{ route('products.show', $product) }}" class="btn-icon text-slate-500 hover:text-sky-600" title="View Product">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-12 text-slate-400 dark:text-dark-500">
                        No products currently assigned to this category.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($products->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-dark-800">
                {{ $products->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
