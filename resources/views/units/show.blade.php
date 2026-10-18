@extends('layouts.app')
@section('title', 'Unit — ' . $unit->name)

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <!-- Header -->
    <div class="page-header">
        <div>
            <div class="flex items-center gap-3">
                <h1>{{ $unit->name }}</h1>
                <span class="badge badge-blue font-mono uppercase">{{ $unit->code }}</span>
            </div>
            <p class="text-slate-500 dark:text-dark-400 text-sm mt-1">Standard measurement unit definition</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('units.edit', $unit) }}" class="btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Unit
            </a>
            <a href="{{ route('units.index') }}" class="btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to Units
            </a>
        </div>
    </div>

    <!-- Details Card -->
    <div class="card p-6 space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-xs uppercase font-semibold text-slate-400 dark:text-dark-500">Unit Name</p>
                <p class="text-base font-semibold text-slate-900 dark:text-white mt-0.5">{{ $unit->name }}</p>
            </div>
            <div>
                <p class="text-xs uppercase font-semibold text-slate-400 dark:text-dark-500">Code / Abbreviation</p>
                <p class="text-base font-mono font-semibold text-slate-900 dark:text-white mt-0.5">{{ $unit->code }}</p>
            </div>
            <div>
                <p class="text-xs uppercase font-semibold text-slate-400 dark:text-dark-500">Products Using This Unit</p>
                <p class="text-base font-semibold text-slate-900 dark:text-white mt-0.5">{{ $unit->products_count }} items</p>
            </div>
            <div>
                <p class="text-xs uppercase font-semibold text-slate-400 dark:text-dark-500">Created At</p>
                <p class="text-base text-slate-700 dark:text-dark-300 mt-0.5">{{ $unit->created_at?->format('M d, Y') ?? '—' }}</p>
            </div>
        </div>
    </div>

</div>
@endsection
