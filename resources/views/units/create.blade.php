@extends('layouts.app')
@section('title', 'New Unit of Measure')

@section('content')
<div class="max-w-xl mx-auto space-y-5">

    <div class="page-header">
        <div>
            <h1>New Unit of Measure</h1>
            <p class="text-slate-500 dark:text-dark-400 text-sm mt-1">Add a unit definition such as Kilogram, Liter, Meter, Piece.</p>
        </div>
        <a href="{{ route('units.index') }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back
        </a>
    </div>

    <form method="POST" action="{{ route('units.store') }}" class="card p-6 space-y-5">
        @csrf

        <div>
            <label class="form-label">Unit Name <span class="text-rose-500">*</span></label>
            <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Kilogram, Meter, Box, Piece" class="form-input @error('name') border-rose-400 @enderror" required>
            @error('name')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="form-label">Code / Abbreviation <span class="text-rose-500">*</span></label>
            <input type="text" name="code" value="{{ old('code') }}" placeholder="e.g. kg, m, bx, pcs" class="form-input font-mono @error('code') border-rose-400 @enderror" required>
            @error('code')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-dark-800">
            <a href="{{ route('units.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Create Unit</button>
        </div>
    </form>

</div>
@endsection
