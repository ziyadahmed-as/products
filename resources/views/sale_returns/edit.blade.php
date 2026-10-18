@extends('layouts.app')
@section('title', 'Edit Return Claim — #' . $saleReturn->id)

@section('content')
<div class="max-w-xl mx-auto space-y-5">

    <div class="page-header">
        <div>
            <h1>Edit Return Claim</h1>
            <p class="text-slate-500 dark:text-dark-400 text-sm mt-1">Review and process return claim #{{ $saleReturn->id }}</p>
        </div>
        <a href="{{ route('sale-returns.index') }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back
        </a>
    </div>

    <form method="POST" action="{{ route('sale-returns.update', $saleReturn) }}" class="card p-6 space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label class="form-label">Sales Order Reference</label>
            <input type="text" disabled value="Sale #{{ $saleReturn->sale_id }}" class="form-input bg-slate-100 dark:bg-dark-800 text-slate-500">
        </div>

        <div>
            <label class="form-label">Refund Amount ($) <span class="text-rose-500">*</span></label>
            <input type="number" step="0.01" min="0" name="refund_amount" value="{{ old('refund_amount', $saleReturn->refund_amount) }}" class="form-input @error('refund_amount') border-rose-400 @enderror" required>
            @error('refund_amount')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="form-label">Reason / Return Notes <span class="text-rose-500">*</span></label>
            <textarea name="reason" rows="3" class="form-input @error('reason') border-rose-400 @enderror" required>{{ old('reason', $saleReturn->reason) }}</textarea>
            @error('reason')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
            <div>
                <label class="form-label">Approval Status <span class="text-rose-500">*</span></label>
                <select name="status" class="form-input" required>
                    <option value="pending" {{ old('status', $saleReturn->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ old('status', $saleReturn->status) === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ old('status', $saleReturn->status) === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>

            <div class="flex items-center pt-6">
                <input type="checkbox" id="is_refund_processed" name="is_refund_processed" value="1" {{ old('is_refund_processed', $saleReturn->is_refund_processed) ? 'checked' : '' }} class="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
                <label for="is_refund_processed" class="ml-2 text-sm font-medium text-slate-700 dark:text-dark-300">Refund has been disbursed</label>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-dark-800">
            <a href="{{ route('sale-returns.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Update Claim</button>
        </div>
    </form>

</div>
@endsection
