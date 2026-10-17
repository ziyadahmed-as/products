<nav class="sidebar-nav">

    {{-- ── 1. MAIN / DASHBOARD OVERVIEW ── --}}
    <div class="sidebar-section">Main</div>

    @if(auth()->user()->hasRole('Production Officer') && !auth()->user()->hasAnyRole(['Super Admin', 'Manager']))
        <a href="{{ route('manufacturing.dashboard') }}" class="sidebar-item {{ request()->routeIs('manufacturing.dashboard') ? 'active' : '' }}">
            <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
            </svg>
            <span>Manufacturing Dashboard</span>
        </a>
    @else
        <a href="{{ route('dashboard') }}" class="sidebar-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <svg class="w-4 h-4 text-sky-600 dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span>{{ auth()->user()->hasRole('Seller') && !auth()->user()->hasAnyRole(['Super Admin', 'Manager']) ? 'Seller Dashboard' : 'Dashboard' }}</span>
        </a>
    @endif

    {{-- ── 2. SALES & POINT OF SALE (Collapsible Group) ── --}}
    @hasanyrole('Super Admin|Manager|Seller|Admin')
    @php
        $salesActive = request()->routeIs('sales.*') || request()->routeIs('sale-returns.*');
    @endphp
    <div x-data="{ open: {{ $salesActive ? 'true' : 'false' }} }" class="sidebar-group">
        <button type="button" @click="open = !open"
                class="sidebar-group-toggle {{ $salesActive ? 'text-sky-600 dark:text-sky-400 font-semibold' : '' }}">
            <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-cyan-600 dark:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span>Sales & POS</span>
            </div>
            <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200"
                 :class="{ 'rotate-180': open, 'rotate-0': !open }"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="open" style="display: {{ $salesActive ? 'block' : 'none' }};" class="space-y-1 pl-1 pt-1">
            <a href="{{ route('sales.create') }}" class="sidebar-subitem {{ request()->routeIs('sales.create') ? 'active' : '' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('sales.create') ? 'bg-cyan-500' : 'bg-slate-400' }}"></span>
                <span>Quick Sale (POS)</span>
            </a>
            <a href="{{ route('sales.index') }}" class="sidebar-subitem {{ request()->routeIs('sales.index') || request()->routeIs('sales.show') ? 'active' : '' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('sales.index') || request()->routeIs('sales.show') ? 'bg-cyan-500' : 'bg-slate-400' }}"></span>
                <span>Sales Records</span>
            </a>
            <a href="{{ route('sale-returns.index') }}" class="sidebar-subitem {{ request()->routeIs('sale-returns.*') ? 'active' : '' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('sale-returns.*') ? 'bg-cyan-500' : 'bg-slate-400' }}"></span>
                <span>Returns & Refunds</span>
            </a>
        </div>
    </div>
    @endhasanyrole

    {{-- ── 3. INVENTORY & STOCK (Collapsible Group) ── --}}
    @php
        $inventoryActive = request()->routeIs('products.*') || request()->routeIs('stock-receipts.*') || request()->routeIs('inventory.*');
    @endphp
    <div x-data="{ open: {{ $inventoryActive ? 'true' : 'false' }} }" class="sidebar-group">
        <button type="button" @click="open = !open"
                class="sidebar-group-toggle {{ $inventoryActive ? 'text-sky-600 dark:text-sky-400 font-semibold' : '' }}">
            <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-sky-600 dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <span>Inventory & Stock</span>
            </div>
            <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200"
                 :class="{ 'rotate-180': open, 'rotate-0': !open }"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="open" style="display: {{ $inventoryActive ? 'block' : 'none' }};" class="space-y-1 pl-1 pt-1">
            <a href="{{ route('products.index') }}" class="sidebar-subitem {{ request()->routeIs('products.*') ? 'active' : '' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('products.*') ? 'bg-sky-500' : 'bg-slate-400' }}"></span>
                <span>Products & Materials</span>
            </a>
            @hasanyrole('Super Admin|Manager|Production Officer|Auditor|Admin')
            <a href="{{ route('stock-receipts.index') }}" class="sidebar-subitem {{ request()->routeIs('stock-receipts.*') ? 'active' : '' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('stock-receipts.*') ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                <span>Stock Receipts</span>
            </a>
            @endhasanyrole
            <a href="{{ route('inventory.index') }}" class="sidebar-subitem {{ request()->routeIs('inventory.*') ? 'active' : '' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('inventory.*') ? 'bg-indigo-500' : 'bg-slate-400' }}"></span>
                <span>Stock Balances</span>
            </a>
        </div>
    </div>

    {{-- ── 4. MANUFACTURING & PRODUCTION (Collapsible Group) ── --}}
    @hasanyrole('Super Admin|Manager|Production Officer|Admin')
    @php
        $mfgActive = request()->routeIs('manufacturing.*') || request()->routeIs('manufacturing-orders.*') || request()->routeIs('recipes.*');
    @endphp
    <div x-data="{ open: {{ $mfgActive ? 'true' : 'false' }} }" class="sidebar-group">
        <button type="button" @click="open = !open"
                class="sidebar-group-toggle {{ $mfgActive ? 'text-purple-600 dark:text-purple-400 font-semibold' : '' }}">
            <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                </svg>
                <span>Manufacturing</span>
            </div>
            <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200"
                 :class="{ 'rotate-180': open, 'rotate-0': !open }"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="open" style="display: {{ $mfgActive ? 'block' : 'none' }};" class="space-y-1 pl-1 pt-1">
            <a href="{{ route('manufacturing.dashboard') }}" class="sidebar-subitem {{ request()->routeIs('manufacturing.dashboard') ? 'active' : '' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('manufacturing.dashboard') ? 'bg-purple-500' : 'bg-slate-400' }}"></span>
                <span>Production Floor</span>
            </a>
            <a href="{{ route('manufacturing-orders.index') }}" class="sidebar-subitem {{ request()->routeIs('manufacturing-orders.*') ? 'active' : '' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('manufacturing-orders.*') ? 'bg-purple-500' : 'bg-slate-400' }}"></span>
                <span>Production Orders</span>
            </a>
            <a href="{{ route('recipes.index') }}" class="sidebar-subitem {{ request()->routeIs('recipes.*') ? 'active' : '' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('recipes.*') ? 'bg-purple-500' : 'bg-slate-400' }}"></span>
                <span>Recipes / BOM</span>
            </a>
        </div>
    </div>
    @endhasanyrole

    {{-- ── 5. REPORTS & ANALYTICS (Collapsible Group) ── --}}
    @hasanyrole('Super Admin|Manager|Auditor|Admin')
    @php
        $reportsActive = request()->routeIs('reports.*') && !request()->routeIs('reports.activity*');
    @endphp
    <div x-data="{ open: {{ $reportsActive ? 'true' : 'false' }} }" class="sidebar-group">
        <button type="button" @click="open = !open"
                class="sidebar-group-toggle {{ $reportsActive ? 'text-sky-600 dark:text-sky-400 font-semibold' : '' }}">
            <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-sky-600 dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span>Reports & Analytics</span>
            </div>
            <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200"
                 :class="{ 'rotate-180': open, 'rotate-0': !open }"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="open" style="display: {{ $reportsActive ? 'block' : 'none' }};" class="space-y-1 pl-1 pt-1">
            <a href="{{ route('reports.index') }}" class="sidebar-subitem {{ request()->routeIs('reports.index') ? 'active' : '' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('reports.index') ? 'bg-sky-500' : 'bg-slate-400' }}"></span>
                <span>Executive Overview</span>
            </a>
            <a href="{{ route('reports.sales') }}" class="sidebar-subitem {{ request()->routeIs('reports.sales*') ? 'active' : '' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('reports.sales*') ? 'bg-cyan-500' : 'bg-slate-400' }}"></span>
                <span>Sales & Revenue</span>
            </a>
            <a href="{{ route('reports.inventory') }}" class="sidebar-subitem {{ request()->routeIs('reports.inventory*') ? 'active' : '' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('reports.inventory*') ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                <span>Stock & Valuation</span>
            </a>
            <a href="{{ route('reports.production') }}" class="sidebar-subitem {{ request()->routeIs('reports.production*') ? 'active' : '' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('reports.production*') ? 'bg-purple-500' : 'bg-slate-400' }}"></span>
                <span>Mfg & Scrap</span>
            </a>
            <a href="{{ route('reports.branches') }}" class="sidebar-subitem {{ request()->routeIs('reports.branches*') ? 'active' : '' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('reports.branches*') ? 'bg-blue-500' : 'bg-slate-400' }}"></span>
                <span>Branch Performance</span>
            </a>
            <a href="{{ route('reports.transfers') }}" class="sidebar-subitem {{ request()->routeIs('reports.transfers*') ? 'active' : '' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('reports.transfers*') ? 'bg-amber-500' : 'bg-slate-400' }}"></span>
                <span>Transfers</span>
            </a>
        </div>
    </div>
    @endhasanyrole

    {{-- ── 6. SELLER REPORTS ── --}}
    @role('Seller')
    @php
        $sellerReportsActive = request()->routeIs('reports.*');
    @endphp
    <div x-data="{ open: {{ $sellerReportsActive ? 'true' : 'false' }} }" class="sidebar-group">
        <button type="button" @click="open = !open"
                class="sidebar-group-toggle {{ $sellerReportsActive ? 'text-cyan-600 dark:text-cyan-400 font-semibold' : '' }}">
            <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-cyan-600 dark:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span>My Reports</span>
            </div>
            <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200"
                 :class="{ 'rotate-180': open, 'rotate-0': !open }"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="open" style="display: {{ $sellerReportsActive ? 'block' : 'none' }};" class="space-y-1 pl-1 pt-1">
            <a href="{{ route('reports.sales') }}" class="sidebar-subitem {{ request()->routeIs('reports.sales*') ? 'active' : '' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('reports.sales*') ? 'bg-cyan-500' : 'bg-slate-400' }}"></span>
                <span>My Sales Analytics</span>
            </a>
            <a href="{{ route('reports.inventory') }}" class="sidebar-subitem {{ request()->routeIs('reports.inventory*') ? 'active' : '' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('reports.inventory*') ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                <span>Branch Stock Balances</span>
            </a>
        </div>
    </div>
    @endrole

    {{-- ── 7. AUDIT & COMPLIANCE ── --}}
    @hasanyrole('Super Admin|Auditor')
    <div class="sidebar-section">Audit & Compliance</div>
    <a href="{{ route('reports.activity') }}" class="sidebar-item {{ request()->routeIs('reports.activity*') || request()->routeIs('audit.*') ? 'active' : '' }}">
        <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
        </svg>
        <span>User Audit Trail</span>
    </a>
    @endhasanyrole

    {{-- ── 8. SYSTEM ADMINISTRATION & MASTER DATA (Collapsible Group) ── --}}
    @hasanyrole('Super Admin|Manager|Admin')
    @php
        $adminActive = request()->routeIs('users.*') || request()->routeIs('branches.*') || request()->routeIs('categories.*') || request()->routeIs('units.*') || request()->is('admin/storage-locations*');
    @endphp
    <div x-data="{ open: {{ $adminActive ? 'true' : 'false' }} }" class="sidebar-group">
        <button type="button" @click="open = !open"
                class="sidebar-group-toggle {{ $adminActive ? 'text-sky-600 dark:text-sky-400 font-semibold' : '' }}">
            <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span>Administration</span>
            </div>
            <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200"
                 :class="{ 'rotate-180': open, 'rotate-0': !open }"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="open" style="display: {{ $adminActive ? 'block' : 'none' }};" class="space-y-1 pl-1 pt-1">
            <a href="{{ route('users.index') }}" class="sidebar-subitem {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('users.*') ? 'bg-sky-500' : 'bg-slate-400' }}"></span>
                <span>Users & Roles</span>
            </a>
            <a href="{{ route('branches.index') }}" class="sidebar-subitem {{ request()->routeIs('branches.*') ? 'active' : '' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('branches.*') ? 'bg-sky-500' : 'bg-slate-400' }}"></span>
                <span>Branches</span>
            </a>
            <a href="/admin/storage-locations" class="sidebar-subitem {{ request()->is('admin/storage-locations*') ? 'active' : '' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->is('admin/storage-locations*') ? 'bg-sky-500' : 'bg-slate-400' }}"></span>
                <span>Storage Locations</span>
            </a>
            <a href="{{ route('categories.index') }}" class="sidebar-subitem {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('categories.*') ? 'bg-sky-500' : 'bg-slate-400' }}"></span>
                <span>Categories</span>
            </a>
            <a href="{{ route('units.index') }}" class="sidebar-subitem {{ request()->routeIs('units.*') ? 'active' : '' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('units.*') ? 'bg-sky-500' : 'bg-slate-400' }}"></span>
                <span>Units of Measure</span>
            </a>
            @hasanyrole('Super Admin|Admin')
            <a href="/admin" class="sidebar-subitem">
                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                <span>Filament Panel</span>
            </a>
            @endhasanyrole
        </div>
    </div>
    @endhasanyrole

</nav>
