@extends('layouts.app')
@section('title', 'Edit Branch — ' . $branch->name)

@section('content')
<div class="max-w-xl mx-auto space-y-5">

    <div class="page-header">
        <div>
            <h1>Edit Branch</h1>
            <p class="text-slate-500 dark:text-dark-400 text-sm mt-1">{{ $branch->name }} ({{ $branch->code }})</p>
        </div>
        <a href="{{ route('branches.index') }}" class="btn-secondary">← Back</a>
    </div>

    <form method="POST" action="{{ route('branches.update', $branch) }}" class="card p-6 space-y-5">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div class="sm:col-span-2">
                <label class="form-label">Branch Name <span class="text-rose-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $branch->name) }}" class="form-input @error('name') border-rose-400 @enderror" required>
                @error('name')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Branch Code <span class="text-rose-500">*</span></label>
                <input type="text" name="code" value="{{ old('code', $branch->code) }}" class="form-input @error('code') border-rose-400 @enderror" required>
                @error('code')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Type <span class="text-rose-500">*</span></label>
                <select name="type" class="form-input" required>
                    <option value="headquarters" {{ old('type', $branch->type) === 'headquarters' ? 'selected' : '' }}>Headquarters</option>
                    <option value="branch" {{ old('type', $branch->type) === 'branch' ? 'selected' : '' }}>Branch</option>
                    <option value="warehouse" {{ old('type', $branch->type) === 'warehouse' ? 'selected' : '' }}>Warehouse</option>
                    <option value="distribution_center" {{ old('type', $branch->type) === 'distribution_center' ? 'selected' : '' }}>Distribution Centre</option>
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="form-label">Parent Branch</label>
                <select name="parent_branch_id" class="form-input">
                    <option value="">None (Top Level)</option>
                    @foreach($parents as $parent)
                        <option value="{{ $parent->id }}" {{ old('parent_branch_id', $branch->parent_branch_id) == $parent->id ? 'selected' : '' }}>{{ $parent->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $branch->is_active) ? 'checked' : '' }} class="sr-only peer">
                <div class="w-11 h-6 bg-slate-200 peer-focus:ring-4 peer-focus:ring-cyan-300 rounded-full peer dark:bg-dark-700 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-cyan-600"></div>
            </label>
            <span class="text-sm font-medium text-slate-700 dark:text-dark-200">Branch Active</span>
        </div>

        <div class="flex items-center gap-3 pt-2 border-t border-slate-100 dark:border-dark-800">
            <button type="submit" class="btn-primary">Save Changes</button>
            <a href="{{ route('branches.index') }}" class="btn-secondary">Cancel</a>
        </div>
    </form>

</div>
@endsection
