@extends('layouts.app')
@section('title', 'Edit Storage Location — ' . $storageLocation->name)

@section('content')
<div class="max-w-xl mx-auto space-y-5">

    <div class="page-header">
        <div>
            <h1>Edit Storage Location</h1>
            <p class="text-slate-500 dark:text-dark-400 text-sm mt-1">Update location «{{ $storageLocation->name }}»</p>
        </div>
        <a href="{{ route('storage-locations.index') }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back
        </a>
    </div>

    <form method="POST" action="{{ route('storage-locations.update', $storageLocation) }}" class="card p-6 space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label class="form-label">Branch <span class="text-rose-500">*</span></label>
            <select name="branch_id" class="form-input @error('branch_id') border-rose-400 @enderror" required>
                <option value="">Select Branch</option>
                @foreach($branches as $b)
                    <option value="{{ $b->id }}" {{ old('branch_id', $storageLocation->branch_id) == $b->id ? 'selected' : '' }}>{{ $b->name }} ({{ $b->code }})</option>
                @endforeach
            </select>
            @error('branch_id')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="form-label">Location Name <span class="text-rose-500">*</span></label>
            <input type="text" name="name" value="{{ old('name', $storageLocation->name) }}" class="form-input @error('name') border-rose-400 @enderror" required>
            @error('name')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="form-label">Location Code / Identifier <span class="text-rose-500">*</span></label>
            <input type="text" name="code" value="{{ old('code', $storageLocation->code) }}" class="form-input font-mono @error('code') border-rose-400 @enderror" required>
            @error('code')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $storageLocation->is_active) ? 'checked' : '' }} class="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
            <label for="is_active" class="text-sm font-medium text-slate-700 dark:text-dark-300">Active Location</label>
        </div>

        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-dark-800">
            <a href="{{ route('storage-locations.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Update Location</button>
        </div>
    </form>

</div>
@endsection
