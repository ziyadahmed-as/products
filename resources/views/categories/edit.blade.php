@extends('layouts.app')
@section('title', 'Edit Category — ' . $category->name)

@section('content')
<div class="max-w-xl mx-auto space-y-5">

    <div class="page-header">
        <div>
            <h1>Edit Category</h1>
            <p class="text-slate-500 dark:text-dark-400 text-sm mt-1">Update category «{{ $category->name }}»</p>
        </div>
        <a href="{{ route('categories.index') }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back
        </a>
    </div>

    <form method="POST" action="{{ route('categories.update', $category) }}" class="card p-6 space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label class="form-label">Category Name <span class="text-rose-500">*</span></label>
            <input type="text" name="name" value="{{ old('name', $category->name) }}" class="form-input @error('name') border-rose-400 @enderror" required>
            @error('name')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="form-label">Description</label>
            <textarea name="description" rows="3" class="form-input">{{ old('description', $category->description) }}</textarea>
            @error('description')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }} class="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
            <label for="is_active" class="text-sm font-medium text-slate-700 dark:text-dark-300">Active category</label>
        </div>

        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-dark-800">
            <a href="{{ route('categories.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Update Category</button>
        </div>
    </form>

</div>
@endsection
