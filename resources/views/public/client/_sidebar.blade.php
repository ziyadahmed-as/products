@php
    $sidebarLinks = [
        ['route' => 'client.dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', 'label' => 'Dashboard'],
        ['route' => 'client.orders',    'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'label' => 'My Orders'],
        ['route' => 'client.reviews',   'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z', 'label' => 'My Reviews'],
        ['route' => 'client.profile',   'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', 'label' => 'Profile'],
    ];
@endphp

<div style="width:240px;flex-shrink:0;">
    <div style="background:var(--bg-card);border-radius:14px;border:1px solid var(--border);overflow:hidden;position:sticky;top:100px;">

        {{-- User info --}}
        <div style="padding:1.5rem;border-bottom:1px solid var(--border);">
            <div style="width:46px;height:46px;border-radius:50%;background:linear-gradient(135deg,var(--cyan),#6366f1);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:1.1rem;margin-bottom:.75rem;">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div style="font-weight:700;color:var(--text-1);font-size:.95rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ auth()->user()->name }}</div>
            <div style="font-size:.78rem;color:var(--text-3);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;margin-top:.15rem;">{{ auth()->user()->email }}</div>
            @if(auth()->user()->phone)
            <div style="font-size:.78rem;color:var(--text-3);margin-top:.1rem;">{{ auth()->user()->phone }}</div>
            @endif
        </div>

        {{-- Nav links --}}
        <div style="display:flex;flex-direction:column;padding:.4rem 0;">
            @foreach($sidebarLinks as $link)
            @php $isActive = isset($active) && $active === \Illuminate\Support\Str::after($link['route'], 'client.'); @endphp
            <a href="{{ route($link['route']) }}"
               style="display:flex;align-items:center;gap:.65rem;padding:.7rem 1.25rem;font-size:.875rem;color:{{ $isActive ? 'var(--text-1)' : 'var(--text-2)' }};font-weight:{{ $isActive ? '700' : '500' }};border-left:3px solid {{ $isActive ? 'var(--cyan)' : 'transparent' }};background:{{ $isActive ? 'rgba(8,145,178,.06)' : 'transparent' }};transition:all .15s;text-decoration:none;">
                <svg style="width:16px;height:16px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $link['icon'] }}"/>
                </svg>
                {{ $link['label'] }}
            </a>
            @endforeach
        </div>

        {{-- Logout --}}
        <div style="padding:.4rem 0;border-top:1px solid var(--border);">
            <form action="{{ route('client.logout') }}" method="POST" style="margin:0;">
                @csrf
                <button type="submit"
                    style="width:100%;display:flex;align-items:center;gap:.65rem;padding:.7rem 1.25rem;font-size:.875rem;color:var(--text-2);background:none;border:none;cursor:pointer;text-align:left;font-weight:500;">
                    <svg style="width:16px;height:16px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Log Out
                </button>
            </form>
        </div>
    </div>
</div>
