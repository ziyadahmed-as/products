@extends('layouts.app')
@section('title', 'New Return Claim')

@section('content')
<div class="max-w-xl mx-auto space-y-5">

    <div class="page-header">
        <div>
            <h1>New Return Claim</h1>
            <p class="text-slate-500 dark:text-dark-400 text-sm mt-1">Initiate a customer return and refund request against a past sales order.</p>
        </div>
        <a href="{{ route('sale-returns.index') }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back
        </a>
    </div>

    <form method="POST" action="{{ route('sale-returns.store') }}" class="card p-6 space-y-5">
        @csrf

        <div>
            <label class="form-label">Sales Order Reference <span class="text-rose-500">*</span></label>
            <select name="sale_id" class="form-input @error('sale_id') border-rose-400 @enderror" required>
                <option value="">Select Sale Order</option>
                @foreach($sales as $s)
                    <option value="{{ $s->id }}" {{ old('sale_id', request('sale_id')) == $s->id ? 'selected' : '' }}>
                        Sale #{{ $s->id }} &bull; ${{ number_format($s->total_amount, 2) }} ({{ $s->created_at->format('M d, Y') }})
                    </option>
                @endforeach
            </select>
            @error('sale_id')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="form-label">Requested Refund Amount ($) <span class="text-rose-500">*</span></label>
            <input type="number" step="0.01" min="0" name="refund_amount" value="{{ old('refund_amount', '0.00') }}" class="form-input @error('refund_amount') border-rose-400 @enderror" required>
            @error('refund_amount')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="form-label">Reason for Return / Issue Description <span class="text-rose-500">*</span></label>
            <textarea name="reason" rows="3" placeholder="Provide reason (e.g. Defective item, wrong size delivered, customer cancellation)…" class="form-input @error('reason') border-rose-400 @enderror" required>{{ old('reason') }}</textarea>
            @error('reason')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-dark-800">
            <a href="{{ route('sale-returns.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Submit Return Claim</button>
        </div>
    </form>

</div>
@endsection
