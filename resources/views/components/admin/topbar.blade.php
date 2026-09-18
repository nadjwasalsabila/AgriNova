{{--
    Admin Topbar Component
    - Height: 64px
    - Left: hamburger (mobile) + breadcrumb/page title
    - Right: notification bell dropdown + admin avatar
--}}

@php
    $supabaseService = app(\App\Services\SupabaseService::class);
    $notifications   = \App\Services\NotificationService::getNotifications($supabaseService);
    $unreadCount     = \App\Services\NotificationService::countUnread();
@endphp

<header class="sticky top-0 z-10 h-16 bg-white border-b border-[#E0E0E0] flex items-center px-4 lg:px-6 gap-4 shrink-0">

    {{-- ── Hamburger + Mobile Brand ── --}}
    <div class="flex items-center gap-2 lg:hidden">
        <button
            @click="toggleSidebar()"
            class="w-9 h-9 flex items-center justify-center rounded-lg
                   text-[#616161] hover:bg-[#F8F9FA] transition-colors duration-150">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
        <img src="{{ asset('assets/AgriNova-App-Icon/icon-rounded-preview.svg') }}" 
             alt="AgriNova" 
             class="w-7 h-7 rounded-lg object-contain"
             onerror="this.onerror=null; this.src='{{ asset('assets/images/logo.png') }}'">
    </div>

    {{-- ── Page Title / Breadcrumb ── --}}
    <div class="flex-1 min-w-0">
        <h1 class="text-base font-semibold text-[#1A1A1A] truncate">
            {{ $title ?? 'Dashboard' }}
        </h1>
        @isset($breadcrumb)
            <p class="text-xs text-[#9E9E9E] mt-0.5">{{ $breadcrumb }}</p>
        @endisset
    </div>

    {{-- ── Right Actions ── --}}
    <div class="flex items-center gap-2 shrink-0">

        {{-- Notification Bell Dropdown (Alpine.js) --}}
        <div class="relative" x-data="{ notifOpen: false }">
            <button
                @click="notifOpen = !notifOpen"
                class="relative w-9 h-9 flex items-center justify-center rounded-lg
                       text-[#616161] hover:bg-[#F8F9FA] hover:text-primary-700
                       transition-colors duration-150">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                
                {{-- Dot / Badge indicator --}}
                @if($unreadCount > 0)
                    <span class="absolute top-1.5 right-1.5 flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-danger-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-danger-500"></span>
                    </span>
                @endif
            </button>

            {{-- Notification Popup Menu --}}
            <div
                x-show="notifOpen"
                @click.outside="notifOpen = false"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-2xl border border-[#E0E0E0] card-shadow z-50 overflow-hidden"
                style="display: none;"
            >
                {{-- Header popup --}}
                <div class="px-4 py-3.5 border-b border-[#F0F0F0] flex items-center justify-between bg-[#FAFAFA]">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-sm text-[#1A1A1A]">Notifikasi</span>
                        @if($unreadCount > 0)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-danger-50 text-danger-700 border border-danger-100">
                                {{ $unreadCount }} Baru
                            </span>
                        @endif
                    </div>
                    @if($unreadCount > 0)
                        <form method="POST" action="{{ route('admin.notifications.read') }}">
                            @csrf
                            <button type="submit" class="text-xs text-primary-700 hover:text-primary-800 font-semibold transition-colors">
                                Tandai Dibaca
                            </button>
                        </form>
                    @endif
                </div>

                {{-- List Notifikasi --}}
                <div class="max-h-80 overflow-y-auto divide-y divide-[#F0F0F0]">
                    @forelse($notifications as $n)
                        @php
                            $bgClass = ! $n['is_read'] ? 'bg-primary-50/40 hover:bg-primary-50/70' : 'hover:bg-[#F8F9FA]';
                            $iconBg  = match($n['type']) {
                                'scan'        => 'bg-blue-50 text-blue-600',
                                'obat_create' => 'bg-green-50 text-green-600',
                                'obat_update' => 'bg-amber-50 text-amber-600',
                                'obat_delete' => 'bg-red-50 text-red-600',
                                default       => 'bg-gray-50 text-gray-600',
                            };
                        @endphp
                        <a href="{{ $n['link'] }}" class="block px-4 py-3 transition-colors {{ $bgClass }}">
                            <div class="flex items-start gap-3">
                                {{-- Icon --}}
                                <div class="w-8 h-8 rounded-xl {{ $iconBg }} flex items-center justify-center shrink-0 text-xs font-bold mt-0.5">
                                    @if($n['type'] === 'scan') 🔍
                                    @elseif($n['type'] === 'obat_create') 💊
                                    @elseif($n['type'] === 'obat_update') ✏️
                                    @elseif($n['type'] === 'obat_delete') 🗑️
                                    @else 🔔 @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-1">
                                        <p class="text-xs font-bold text-[#1A1A1A] truncate">{{ $n['title'] }}</p>
                                        <span class="text-[10px] text-[#9E9E9E] shrink-0">{{ $n['time'] }}</span>
                                    </div>
                                    <p class="text-xs text-[#616161] mt-0.5 line-clamp-2 leading-relaxed">
                                        {{ $n['message'] }}
                                    </p>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="py-8 text-center text-xs text-[#9E9E9E]">
                            <svg class="w-8 h-8 text-[#BDBDBD] mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            Belum ada notifikasi
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Divider --}}
        <div class="w-px h-6 bg-[#E0E0E0]"></div>

        {{-- Admin Avatar + Name --}}
        <a href="{{ route('admin.settings.index') }}" class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg
                        hover:bg-[#F8F9FA] transition-colors duration-150 group">
            <div class="w-8 h-8 rounded-full overflow-hidden bg-[#0F6E56] flex items-center justify-center border border-[#DEF2E7] shrink-0">
                <img src="{{ asset('assets/images/profiles.png') }}" 
                     alt="{{ session('admin_name', 'Admin') }}"
                     class="w-full h-full object-cover"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                <span style="display: none;" class="w-full h-full items-center justify-center text-white text-xs font-bold bg-[#0F6E56]">
                    {{ strtoupper(substr(session('admin_name', 'A'), 0, 1)) }}
                </span>
            </div>
            <span class="hidden sm:block text-sm font-medium text-[#1A1A1A] max-w-[120px] truncate">
                {{ session('admin_name', 'Admin') }}
            </span>
        </a>

    </div>
</header>
