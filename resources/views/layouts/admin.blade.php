<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} — Petani Maju Admin</title>
    <meta name="description" content="Admin Dashboard Petani Maju — Kelola konten aplikasi pertanian Anda.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
{{-- PERBAIKAN: Hapus overflow-hidden dari body agar modal tidak freeze halaman --}}
<body class="h-full font-sans antialiased" x-data="adminLayout()">

    {{-- Sidebar Overlay (mobile) --}}
    <div
        x-show="sidebarOpen"
        x-transition:enter="transition-opacity ease-linear duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="sidebarOpen = false"
        class="fixed inset-0 bg-black/40 z-20 lg:hidden"
        style="display: none;"
    ></div>

    <div class="flex h-full">

        {{-- Sidebar --}}
        @include('components.admin.sidebar')

        {{-- Main Area --}}
        {{-- PERBAIKAN: overflow-hidden dihapus dari wrapper ini --}}
        <div class="flex flex-col flex-1 min-w-0">

            {{-- Topbar --}}
            @include('components.admin.topbar')

            {{-- Page Content --}}
            {{-- PERBAIKAN: main pakai overflow-y-auto saja, TANPA overflow-hidden --}}
            <main id="mainContent" class="flex-1 overflow-y-auto bg-[#F8F9FA] p-6">

                @if(session('success'))
                    <div class="mb-4 flex items-center gap-3 bg-[#E8F5E9] border border-[#C8E6C9]
                                text-[#1B5E20] px-4 py-3 rounded-xl text-sm">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 flex items-center gap-3 bg-[#FFEBEE] border border-[#FFCDD2]
                                text-[#B71C1C] px-4 py-3 rounded-xl text-sm">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @yield('content')

            </main>
        </div>
    </div>

    {{-- Alpine.js CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script>
        function adminLayout() {
            return {
                sidebarOpen: false,
                toggleSidebar() { this.sidebarOpen = !this.sidebarOpen; }
            }
        }
    </script>

    @stack('scripts')

</body>
</html>