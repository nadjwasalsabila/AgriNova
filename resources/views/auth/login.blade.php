<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login — Petani Maju Admin</title>
    <meta name="description" content="Login ke Admin Dashboard Petani Maju.">
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

        {{-- Content --}}
        <div class="relative z-10 flex flex-col justify-center items-center w-full px-12 xl:px-20 text-white">
            {{-- Logo --}}
            <div class="w-20 h-20 rounded-3xl bg-white/15 backdrop-blur-sm flex items-center justify-center mb-8 border border-white/20">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M5 3s4.5 0 9 4.5C18.5 12 19 17 19 17s-5-.5-9.5-5C5 7.5 5 3 5 3z
                           M5 3c0 0 0 7 7 11"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M12 14c0 3.866-3 7-3 7"/>
                </svg>
            </div>

            <h1 class="text-4xl xl:text-5xl font-bold text-center leading-tight mb-4">
                Petani Maju
            </h1>
            <p class="text-lg text-white/80 text-center max-w-sm leading-relaxed mb-12">
                Platform digital untuk pertanian cerdas Indonesia
            </p>

            {{-- Feature list --}}
            <div class="space-y-4 w-full max-w-sm">
                @foreach([
                    ['icon' => 'lightbulb', 'text' => 'Kelola artikel & tips pertanian'],
                    ['icon' => 'bug',       'text' => 'Pantau database hama & penyakit'],
                    ['icon' => 'camera',    'text' => 'Monitor riwayat scan AI petani'],
                    ['icon' => 'calendar',  'text' => 'Atur jadwal tanam terintegrasi'],
                ] as $feature)
                    <div class="flex items-center gap-4">
                        <div class="w-9 h-9 rounded-xl bg-white/15 flex items-center justify-center shrink-0">
                            @if($feature['icon'] === 'lightbulb')
                                <svg class="w-4.5 h-4.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                </svg>
                            @elseif($feature['icon'] === 'bug')
                                <svg class="w-4.5 h-4.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            @elseif($feature['icon'] === 'camera')
                                <svg class="w-4.5 h-4.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            @else
                                <svg class="w-4.5 h-4.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            @endif
                        </div>
                        <span class="text-sm text-white/90 font-medium">{{ $feature['text'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Decorative circles --}}
        <div class="absolute -bottom-24 -left-24 w-80 h-80 rounded-full bg-white/5"></div>
        <div class="absolute -top-16 -right-16 w-64 h-64 rounded-full bg-white/5"></div>
    </div>

    {{-- ── Right Panel — Login Form ── --}}
    <div class="flex-1 flex flex-col justify-center items-center px-6 sm:px-12 lg:px-16 xl:px-24 py-12">

        {{-- Mobile logo --}}
        <div class="lg:hidden mb-8 flex flex-col items-center">
            <div class="w-14 h-14 rounded-2xl brand-gradient flex items-center justify-center mb-3">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M5 3s4.5 0 9 4.5C18.5 12 19 17 19 17s-5-.5-9.5-5C5 7.5 5 3 5 3z
                           M5 3c0 0 0 7 7 11"/>
                </svg>
            </div>
            <p class="text-lg font-bold text-[#1A1A1A]">Petani Maju</p>
        </div>

        <div class="w-full max-w-md">

            {{-- Header --}}
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-[#1A1A1A] mb-2">Selamat Datang 👋</h2>
                <p class="text-sm text-[#9E9E9E]">Masuk ke panel admin Petani Maju</p>
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
                            placeholder="admin@petanimaju.com"
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
                &copy; {{ date('Y') }} Petani Maju. All rights reserved.
            </p>
        </div>
    </div>

</div>

{{-- Alpine.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

</body>
</html>
