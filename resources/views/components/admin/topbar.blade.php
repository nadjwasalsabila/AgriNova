{{--
    Admin Topbar Component
    - Height: 64px
    - Left: hamburger (mobile) + breadcrumb/page title
    - Right: notification bell + admin avatar
--}}

<header class="sticky top-0 z-10 h-16 bg-white border-b border-[#E0E0E0] flex items-center px-4 lg:px-6 gap-4 shrink-0">

    {{-- ── Hamburger (mobile only) ── --}}
    <button
        @click="toggleSidebar()"
        class="lg:hidden w-9 h-9 flex items-center justify-center rounded-lg
               text-[#616161] hover:bg-[#F8F9FA] transition-colors duration-150">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
    </button>

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

        {{-- Notification Bell --}}
        <button
            class="relative w-9 h-9 flex items-center justify-center rounded-lg
                   text-[#616161] hover:bg-[#F8F9FA] hover:text-primary-700
                   transition-colors duration-150">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            {{-- Dot indicator --}}
            <span class="absolute top-2 right-2 w-2 h-2 bg-danger-400 rounded-full ring-2 ring-white"></span>
        </button>

        {{-- Divider --}}
        <div class="w-px h-6 bg-[#E0E0E0]"></div>

        {{-- Admin Avatar + Name --}}
        <button class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg
                        hover:bg-[#F8F9FA] transition-colors duration-150 group">
            <div class="w-8 h-8 rounded-full bg-primary-700 flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <span class="hidden sm:block text-sm font-medium text-[#1A1A1A] max-w-[120px] truncate">
                {{ session('admin_name', 'Admin') }}
            </span>
            <svg class="hidden sm:block w-3.5 h-3.5 text-[#9E9E9E] group-hover:text-primary-700 transition-colors"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

    </div>
</header>
