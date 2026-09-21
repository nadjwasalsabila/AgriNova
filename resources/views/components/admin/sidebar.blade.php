{{--
Admin Sidebar Component
- Width: 240px (fixed desktop) / slide-in mobile
- Active: green bg + left border
- Profile section fixed at bottom
- Icon: Material-style SVG inline
--}}

@php
$navItems = [
[
'label' => 'Dashboard',
'route' => 'admin.dashboard',
'icon' => 'dashboard',
],
[
'label' => 'Artikel & Tips',
'route' => 'admin.artikel.index',
'icon' => 'lightbulb',
],
[
'label' => 'Daftar Hama',
'route' => 'admin.hama.index',
'icon' => 'bug',
],
[
'label' => 'Daftar Obat',
'route' => 'admin.obat.index',
'icon' => 'pill',
],
    [
        'label' => 'Riwayat Scan AI',
        'route' => 'admin.riwayat-scan.index',
        'icon'  => 'camera',
    ],
    [
        'label' => 'Kelola Pengguna',
        'route' => 'admin.users.index',
        'icon'  => 'users',
    ],
    [
        'label' => 'Pengaturan',
        'route' => 'admin.settings.index',
        'icon'  => 'settings',
    ],
];

$icons = [
    'dashboard' => '
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
',
    'lightbulb' => '
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
    d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
',
    'bug' => '
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
    d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6" />',
    'camera' => '
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
    d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />',
    'calendar' => '
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />',
    'settings' => '
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />',
    'pill' => '
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
    d="M4.5 12.5l8-8a6 6 0 118.5 8.5l-8 8a6 6 0 01-8.5-8.5z" />
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9l6 6" />',
    'users' => '
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
',
];
@endphp

{{-- ─── Sidebar Container ─── --}}
<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'" class="fixed top-0 left-0 z-30 h-full w-60 bg-white flex flex-col
           border-r border-[#E0E0E0] transition-transform duration-300 ease-in-out
           lg:static lg:inset-auto lg:translate-x-0">
    {{-- ─── Logo ─── --}}
    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-5 py-4.5 border-b border-[#E0E0E0] hover:bg-[#F8F9FA]/60 transition-colors">
        {{-- AgriNova App Icon --}}
        <img src="{{ asset('assets/AgriNova-App-Icon/icon-rounded-preview.svg') }}" 
             alt="AgriNova Logo" 
             class="w-9 h-9 rounded-xl shadow-sm shrink-0 object-contain"
             onerror="this.onerror=null; this.src='{{ asset('assets/images/logo.png') }}'">
        <div class="min-w-0">
            <div class="flex items-center gap-1.5">
                <p class="text-base font-bold text-[#1A1A1A] leading-tight tracking-tight">AgriNova</p>
                <span class="inline-flex items-center px-1.5 py-0.2 text-[9px] font-bold bg-[#E8F5E9] text-[#0F6E56] rounded-full">v2.0</span>
            </div>
            <p class="text-[10px] text-[#9E9E9E] font-medium leading-tight mt-0.5 truncate">Admin Dashboard</p>
        </div>
    </a>

    {{-- ─── Navigation ─── --}}
    <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-0.5">
        @foreach($navItems as $item)
        @php
        $isActive = $item['route'] !== '#' && request()->routeIs($item['route']);
        $itemClass = $isActive ? 'nav-item-active' : 'nav-item-inactive';
        $href = $item['route'] !== '#' ? route($item['route']) : '#';
        @endphp
        <a href="{{ $href }}" class="{{ $itemClass }} flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm
                       transition-all duration-150 group" @if($item['route']==='#' ) onclick="event.preventDefault()"
            @endif>
            <svg class="w-[18px] h-[18px] shrink-0 {{ $isActive ? 'text-primary-700' : 'text-[#9E9E9E] group-hover:text-primary-600' }}"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {!! $icons[$item['icon']] !!}
            </svg>
            <span class="truncate">{{ $item['label'] }}</span>

            @if($item['route'] === '#')
            <span class="ml-auto text-[9px] font-semibold text-[#9E9E9E] bg-[#F0F0F0] px-1.5 py-0.5 rounded">
                Soon
            </span>
            @endif
        </a>
        @endforeach
    </nav>

    {{-- ─── Profile Section ─── --}}
    <div class="border-t border-[#E0E0E0] p-4">
        <div class="flex items-center gap-3">
            {{-- Avatar --}}
            <div class="w-9 h-9 rounded-full overflow-hidden bg-[#DFE5E7] flex items-center justify-center shrink-0 border border-[#E0E0E0]">
                <img src="{{ asset('assets/images/default-avatar.svg') }}" 
                     alt="{{ session('admin_name', 'Admin') }}"
                     class="w-full h-full object-cover"
                     onerror="this.src='{{ asset('assets/images/default-avatar.png') }}'">
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-[#1A1A1A] truncate leading-tight">
                    {{ session('admin_name', 'Administrator') }}
                </p>
                <p class="text-[11px] text-[#9E9E9E] truncate leading-tight mt-0.5">
                    {{ session('admin_email', '') }}
                </p>
            </div>
            {{-- Logout --}}
            <form method="POST" action="{{ route('admin.logout') }}" class="shrink-0">
                @csrf
                <button type="submit" title="Logout" class="w-8 h-8 flex items-center justify-center rounded-lg
                           text-[#9E9E9E] hover:text-danger-700 hover:bg-danger-50
                           transition-colors duration-150">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>