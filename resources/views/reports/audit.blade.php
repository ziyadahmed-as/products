@extends('layouts.app')
@section('title', 'Audit Trail')

@section('content')
<div class="space-y-5">

    <div class="page-header">
        <div>
            <h1>Audit Trail</h1>
            <p class="text-dark-400 text-sm mt-1">Track all system activities and user actions.</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="card">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="form-group flex-1 min-w-[200px]">
                <label class="form-label">Search Activity</label>
                <input name="search" value="{{ request('search') }}" type="text"
                       class="form-input" placeholder="Action, Model...">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary">Filter</button>
                <a href="{{ route('audit.index') }}" class="btn-secondary">Reset</a>
            </div>
        </form>
    </div>

    <!-- Activity Table -->
    <div class="card p-0">
        <div class="table-wrapper rounded-2xl">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Subject</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $activity)
                    <tr>
                        <td class="text-dark-300 text-xs whitespace-nowrap">{{ $activity->created_at->format('d M Y, H:i:s') }}</td>
                        <td class="font-medium text-white">{{ $activity->causer->name ?? 'System' }}</td>
                        <td>
                            @if($activity->description === 'created')
                                <span class="badge-green">Created</span>
                            @elseif($activity->description === 'updated')
                                <span class="badge-blue">Updated</span>
                            @elseif($activity->description === 'deleted')
                                <span class="badge-red">Deleted</span>
                            @else
                                <span class="badge-gray">{{ ucfirst($activity->description) }}</span>
                            @endif
                        </td>
                        <td class="text-dark-300 font-mono text-xs">
                            {{ class_basename($activity->subject_type) }} #{{ $activity->subject_id }}
                        </td>
                        <td class="text-dark-400 text-xs max-w-xs truncate" title="{{ json_encode($activity->properties) }}">
                            {{ json_encode($activity->properties) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-12 text-dark-500">No activity logged yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($activities->hasPages())
        <div class="px-6 py-4 border-t border-dark-800">
            {{ $activities->withQueryString()->links() }}
        </div>
        @endif
    </div>

</div>
@endsection

