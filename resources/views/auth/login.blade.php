<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login — AgriNova Admin</title>
    <meta name="description" content="Login ke Admin Dashboard AgriNova — Platform Digital Pertanian Cerdas Indonesia.">
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/AgriNova-App-Icon/icon-rounded-preview.svg') }}">
    <link rel="alternate icon" href="{{ asset('assets/images/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased bg-[#F8F9FA]">

<div class="min-h-screen flex">

    {{-- ── Left Panel — Brand (hidden on mobile) ── --}}
    <div class="hidden lg:flex lg:w-1/2 xl:w-3/5 relative overflow-hidden brand-gradient">

        {{-- Background pattern --}}
        <div class="absolute inset-0 opacity-10">
            <svg width="100%" height="100%" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="leaf-pattern" x="0" y="0" width="40" height="40" patternUnits="userSpaceOnUse">
                        <circle cx="20" cy="20" r="1.5" fill="white" opacity="0.5"/>
                        <circle cx="0" cy="0" r="1" fill="white" opacity="0.3"/>
                        <circle cx="40" cy="40" r="1" fill="white" opacity="0.3"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#leaf-pattern)"/>
            </svg>
        </div>

        {{-- Decorative circles matching mobile theme --}}
        <div class="absolute -bottom-24 -left-24 w-80 h-80 rounded-full bg-white/5"></div>
        <div class="absolute -top-16 -right-16 w-64 h-64 rounded-full bg-white/5"></div>
        <div class="absolute top-1/2 -right-12 w-48 h-48 rounded-full bg-white/5 pointer-events-none"></div>

        {{-- Content --}}
        <div class="relative z-10 flex flex-col justify-center items-center w-full px-12 xl:px-20 text-white">
            {{-- Official AgriNova Logo --}}
            <div class="relative mb-6 group">
                <div class="absolute -inset-1 rounded-3xl bg-gradient-to-r from-[#5DCAA5] to-[#D9A441] opacity-30 blur-md group-hover:opacity-50 transition duration-300"></div>
                <div class="relative w-24 h-24 rounded-3xl bg-white/15 backdrop-blur-md p-3.5 flex items-center justify-center border border-white/25 shadow-2xl">
                    <img src="{{ asset('assets/AgriNova-App-Icon/icon-rounded-preview.svg') }}" 
                         alt="AgriNova Logo" 
                         class="w-full h-full object-contain rounded-2xl drop-shadow-md"
                         onerror="this.onerror=null; this.src='{{ asset('assets/images/logo.png') }}'">
                </div>
            </div>

            <div class="flex items-center gap-2 mb-2">
                <h1 class="text-4xl xl:text-5xl font-extrabold text-center tracking-tight leading-tight">
                    AgriNova
                </h1>
                <span class="px-2 py-0.5 text-xs font-bold bg-[#DEF2E7] text-[#0F6E56] rounded-full shadow-sm">
                    v2.0
                </span>
            </div>

            <p class="text-base text-white/85 text-center max-w-md leading-relaxed mb-10 font-normal">
                Platform digital asisten pintar untuk membantu pertanian Indonesia lebih produktif, cerdas, dan efisien.
            </p>

            {{-- Feature list mirroring mobile onboarding pillars --}}
            <div class="space-y-3.5 w-full max-w-md bg-white/10 backdrop-blur-sm p-5 rounded-2xl border border-white/15 shadow-lg">
                @foreach([
                    ['icon' => 'bug',       'title' => 'Deteksi Hama & AI',         'desc' => 'Identifikasi hama dan penyakit via scan AI cerdas'],
                    ['icon' => 'cloud',     'title' => 'Cuaca & Prediksi Presisi',   'desc' => 'Prakiraan cuaca harian dan mitigasi pertanian'],
                    ['icon' => 'calendar',  'title' => 'Kalender Tanam Terpadu',    'desc' => 'Pengingat jadwal semai, pupuk, dan perawatan'],
                    ['icon' => 'lightbulb', 'title' => 'Tips & Panduan Tani',       'desc' => 'Edukasi dan rekomendasi obat tanaman resmi'],
                ] as $feature)
                    <div class="flex items-center gap-3.5 p-2 rounded-xl hover:bg-white/10 transition-colors">
                        <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center shrink-0 border border-white/20 text-[#DEF2E7]">
                            @if($feature['icon'] === 'lightbulb')
                                <svg class="w-5 h-5 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                </svg>
                            @elseif($feature['icon'] === 'bug')
                                <svg class="w-5 h-5 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6"/>
                                </svg>
                            @elseif($feature['icon'] === 'cloud')
                                <svg class="w-5 h-5 text-sky-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 00-9.78 2.096A4.001 4.001 0 003 15z"/>
                                </svg>
                            @else
                                <svg class="w-5 h-5 text-amber-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-white leading-tight">{{ $feature['title'] }}</p>
                            <p class="text-xs text-white/70 leading-tight mt-0.5 truncate">{{ $feature['desc'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── Right Panel — Login Form ── --}}
    <div class="flex-1 flex flex-col justify-center items-center px-6 sm:px-12 lg:px-16 xl:px-24 py-12">

        {{-- Mobile logo --}}
        <div class="lg:hidden mb-8 flex flex-col items-center text-center">
            <img src="{{ asset('assets/AgriNova-App-Icon/icon-rounded-preview.svg') }}" 
                 alt="AgriNova Logo" 
                 class="w-16 h-16 rounded-2xl shadow-md mb-2 object-contain"
                 onerror="this.onerror=null; this.src='{{ asset('assets/images/logo.png') }}'">
            <div class="flex items-center gap-1.5">
                <p class="text-xl font-bold text-[#1A1A1A]">AgriNova</p>
                <span class="px-1.5 py-0.2 text-[9px] font-bold bg-[#E8F5E9] text-[#0F6E56] rounded-full">v2.0</span>
            </div>
            <p class="text-xs text-[#9E9E9E] mt-0.5">Admin Dashboard Pertanian</p>
        </div>

        <div class="w-full max-w-md">

            {{-- Header --}}
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-[#1A1A1A] mb-1.5">Selamat Datang 👋</h2>
                <p class="text-sm text-[#9E9E9E]">Masuk ke panel admin AgriNova</p>
            </div>

            {{-- Dev mode notice --}}
            @if(! ($supabaseConfigured ?? true))
                <div class="mb-5 flex items-start gap-3 bg-[#FFF3E0] border border-[#FFCC80]
                            text-[#E65100] px-4 py-3 rounded-xl text-sm">
                    <svg class="w-4.5 h-4.5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <p class="font-semibold text-xs">Mode Development</p>
                        <p class="text-xs text-[#BF360C] mt-0.5">
                            Supabase belum dikonfigurasi. Login menggunakan kredensial <code>.env</code>.
                        </p>
                    </div>
                </div>
            @endif

            {{-- Error Alert --}}
            @if($errors->any())
                <div class="mb-5 flex items-start gap-3 bg-danger-50 border border-danger-400/30 text-danger-700 px-4 py-3 rounded-xl text-sm">
                    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            {{-- Login Form --}}
            <form method="POST" action="{{ route('admin.login.post') }}" class="space-y-5">
                @csrf

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-[#424242] mb-1.5">
                        Email
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="w-4.5 h-4.5 text-[#9E9E9E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                            </svg>
                        </div>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            autocomplete="email"
                            required
                            value="{{ old('email') }}"
                            placeholder="admin@agrinova.id"
                            class="w-full pl-10 pr-4 py-3 text-sm text-[#1A1A1A] bg-white
                                   border border-[#E0E0E0] rounded-xl
                                   placeholder:text-[#BDBDBD]
                                   focus:outline-none focus:ring-2 focus:ring-primary-600/20 focus:border-primary-600
                                   transition-colors duration-150
                                   @error('email') border-danger-400 focus:ring-danger-400/20 @enderror"
                        >
                    </div>
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-[#424242] mb-1.5">
                        Password
                    </label>
                    <div class="relative" x-data="{ show: false }">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="w-4.5 h-4.5 text-[#9E9E9E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input
                            id="password"
                            name="password"
                            :type="show ? 'text' : 'password'"
                            autocomplete="current-password"
                            required
                            placeholder="••••••••"
                            class="w-full pl-10 pr-11 py-3 text-sm text-[#1A1A1A] bg-white
                                   border border-[#E0E0E0] rounded-xl
                                   placeholder:text-[#BDBDBD]
                                   focus:outline-none focus:ring-2 focus:ring-primary-600/20 focus:border-primary-600
                                   transition-colors duration-150"
                        >
                        {{-- Toggle password visibility --}}
                        <button
                            type="button"
                            @click="show = !show"
                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-[#9E9E9E] hover:text-[#616161]">
                            <svg x-show="!show" class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="show" class="w-4.5 h-4.5" style="display:none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Submit --}}
                <button
                    type="submit"
                    class="w-full py-3 px-4 bg-primary-700 hover:bg-primary-600 text-white
                           text-sm font-semibold rounded-xl
                           focus:outline-none focus:ring-2 focus:ring-primary-600/40 focus:ring-offset-1
                           transition-colors duration-150 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    Masuk ke Dashboard
                </button>
            </form>

            {{-- Footer --}}
            <p class="mt-8 text-center text-xs text-[#BDBDBD]">
                &copy; {{ date('Y') }} AgriNova. All rights reserved.
            </p>
        </div>
    </div>

</div>

{{-- Alpine.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

</body>
</html>
