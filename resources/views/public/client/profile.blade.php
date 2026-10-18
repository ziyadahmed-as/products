@extends('layouts.public')
@section('title', 'My Profile')

@section('content')
<div class="pub-container" style="padding-top:3rem;padding-bottom:5rem;">
    <div style="display:flex;gap:2rem;flex-wrap:wrap;align-items:flex-start;">

        @include('public.client._sidebar', ['active' => 'profile'])

        <div style="flex:1;min-width:300px;max-width:580px;">
            <h1 style="font-size:2rem;font-weight:800;color:var(--text-1);margin-bottom:1.75rem;">My Profile</h1>

            @if(session('success'))
            <div style="background:rgba(16,185,129,.1);color:#10b981;padding:1rem 1.25rem;border-radius:10px;margin-bottom:1.5rem;border:1px solid rgba(16,185,129,.2);display:flex;align-items:center;gap:.5rem;">
                <svg style="width:18px;height:18px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('success') }}
            </div>
            @endif

            @if($errors->any())
            <div style="background:rgba(239,68,68,.1);color:#ef4444;padding:.875rem 1rem;border-radius:10px;margin-bottom:1.5rem;font-size:.875rem;border:1px solid rgba(239,68,68,.2);">
                {{ $errors->first() }}
            </div>
            @endif

            <form action="{{ route('client.profile.update') }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Basic Info --}}
                <div style="background:var(--bg-card);border-radius:14px;border:1px solid var(--border);overflow:hidden;margin-bottom:1.5rem;">
                    <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--border);font-weight:700;color:var(--text-1);font-size:.95rem;">
                        Account Information
                    </div>
                    <div style="padding:1.5rem;display:flex;flex-direction:column;gap:1rem;">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                            <div>
                                <label class="pub-form-label">Full Name *</label>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="pub-input" style="width:100%;" placeholder="Mohammed" list="profile_name_suggestions">
                                <datalist id="profile_name_suggestions">
                                    <option value="Mohammed">
                                </datalist>
                            </div>
                            <div>
                                <label class="pub-form-label">Phone <span style="font-size:0.75rem;color:var(--text-3);font-weight:normal;">(+251...)</span></label>
                                <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" class="pub-input" style="width:100%;" placeholder="+251912345678" pattern="^\+251[0-9]{9}$" title="Must start with +251 followed by 9 digits (total 12 digits, e.g. +251912345678)">
                                <div style="font-size:.72rem;color:var(--text-3);margin-top:.25rem;">Format: +251 followed by 9 digits</div>
                            </div>
                        </div>
                        <div>
                            <label class="pub-form-label">Email Address</label>
                            <input type="email" value="{{ $user->email }}" class="pub-input" style="width:100%;opacity:.6;cursor:not-allowed;" readonly>
                            <div style="font-size:.72rem;color:var(--text-3);margin-top:.3rem;">Email cannot be changed. Contact support if needed.</div>
                        </div>
                    </div>
                </div>

                {{-- Change Password --}}
                <div style="background:var(--bg-card);border-radius:14px;border:1px solid var(--border);overflow:hidden;margin-bottom:1.75rem;">
                    <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--border);font-weight:700;color:var(--text-1);font-size:.95rem;">
                        Change Password <span style="color:var(--text-3);font-weight:400;font-size:.82rem;">(leave blank to keep current)</span>
                    </div>
                    <div style="padding:1.5rem;display:flex;flex-direction:column;gap:1rem;">
                        <div>
                            <label class="pub-form-label">Current Password</label>
                            <input type="password" name="current_password" class="pub-input" style="width:100%;" placeholder="Enter current password" autocomplete="current-password">
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                            <div>
                                <label class="pub-form-label">New Password</label>
                                <input type="password" name="password" class="pub-input" style="width:100%;" placeholder="Min. 8 characters" autocomplete="new-password">
                            </div>
                            <div>
                                <label class="pub-form-label">Confirm New Password</label>
                                <input type="password" name="password_confirmation" class="pub-input" style="width:100%;" placeholder="Repeat" autocomplete="new-password">
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="pub-btn pub-btn-primary" style="padding:.85rem 2rem;font-size:.95rem;">
                    Save Changes
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
