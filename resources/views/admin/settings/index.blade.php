@extends('layouts.admin', ['title' => $title])

@section('content')
    {{-- ── Header ── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-[#1A1A1A]">Pengaturan Akun</h2>
            <p class="text-xs text-[#9E9E9E] mt-0.5">Kelola informasi profil admin dan keamanan kata sandi akun Anda.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ── Left Column: Profile Card Overview ── --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl border border-[#E0E0E0] card-shadow p-6 text-center">
                <div class="w-20 h-20 rounded-full overflow-hidden bg-[#DFE5E7] mx-auto flex items-center justify-center mb-4 shadow-sm border-2 border-white ring-2 ring-[#E0E0E0]">
                    <img src="{{ asset('assets/images/default-avatar.svg') }}" 
                         alt="{{ $adminName }}"
                         class="w-full h-full object-cover"
                         onerror="this.src='{{ asset('assets/images/default-avatar.png') }}'">
                </div>
                <h3 class="text-base font-bold text-[#1A1A1A]">{{ $adminName }}</h3>
                <p class="text-xs text-[#9E9E9E] mt-0.5 mb-4">{{ $adminEmail }}</p>

                <div
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-primary-50 text-primary-700 border border-primary-100">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <span>Administrator</span>
                </div>

                <div class="mt-6 pt-6 border-t border-[#F0F0F0] text-left space-y-3">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-[#9E9E9E]">Status Akun</span>
                        <span class="font-bold text-green-600 bg-green-50 px-2 py-0.5 rounded-md">Aktif</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Right Column: Edit Profile & Password Forms ── --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Form Edit Profil --}}
            <div class="bg-white rounded-2xl border border-[#E0E0E0] card-shadow p-6">
                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-[#F0F0F0]">
                    <div
                        class="w-10 h-10 rounded-xl bg-primary-50 text-primary-700 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-[#1A1A1A]">Informasi Profil</h3>
                        <p class="text-xs text-[#9E9E9E]">Perbarui nama dan alamat email akun admin Anda.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.settings.profile') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="name" class="block text-xs font-bold text-[#424242] uppercase tracking-wider mb-2">
                            Nama Lengkap
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name', $adminName) }}"
                            class="w-full px-4 py-2.5 text-sm text-[#1A1A1A] bg-[#F8F9FA] border border-[#E0E0E0] rounded-xl
                                                          focus:outline-none focus:ring-2 focus:ring-primary-600/20 focus:border-primary-600 transition-colors @error('name') border-red-500 @enderror"
                            placeholder="Masukkan nama lengkap">
                        @error('name')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-bold text-[#424242] uppercase tracking-wider mb-2">
                            Alamat Email
                        </label>
                        <input type="email" id="email" name="email" value="{{ old('email', $adminEmail) }}"
                            class="w-full px-4 py-2.5 text-sm text-[#1A1A1A] bg-[#F8F9FA] border border-[#E0E0E0] rounded-xl
                                                          focus:outline-none focus:ring-2 focus:ring-primary-600/20 focus:border-primary-600 transition-colors @error('email') border-red-500 @enderror"
                            placeholder="Masukkan alamat email">
                        @error('email')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit"
                            class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold rounded-xl transition-colors shadow-sm">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>

            {{-- Form Ubah Password --}}
            <div class="bg-white rounded-2xl border border-[#E0E0E0] card-shadow p-6">
                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-[#F0F0F0]">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-[#1A1A1A]">Ubah Kata Sandi</h3>
                        <p class="text-xs text-[#9E9E9E]">Pastikan kata sandi baru Anda minimal 6 karakter demi keamanan.
                        </p>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.settings.password') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="current_password"
                            class="block text-xs font-bold text-[#424242] uppercase tracking-wider mb-2">
                            Password Saat Ini
                        </label>
                        <input type="password" id="current_password" name="current_password"
                            class="w-full px-4 py-2.5 text-sm text-[#1A1A1A] bg-[#F8F9FA] border border-[#E0E0E0] rounded-xl
                                                          focus:outline-none focus:ring-2 focus:ring-primary-600/20 focus:border-primary-600 transition-colors @error('current_password') border-red-500 @enderror"
                            placeholder="Masukkan password saat ini">
                        @error('current_password')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="new_password"
                                class="block text-xs font-bold text-[#424242] uppercase tracking-wider mb-2">
                                Password Baru
                            </label>
                            <input type="password" id="new_password" name="new_password"
                                class="w-full px-4 py-2.5 text-sm text-[#1A1A1A] bg-[#F8F9FA] border border-[#E0E0E0] rounded-xl
                                                              focus:outline-none focus:ring-2 focus:ring-primary-600/20 focus:border-primary-600 transition-colors @error('new_password') border-red-500 @enderror"
                                placeholder="Minimal 6 karakter">
                            @error('new_password')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="new_password_confirmation"
                                class="block text-xs font-bold text-[#424242] uppercase tracking-wider mb-2">
                                Konfirmasi Password Baru
                            </label>
                            <input type="password" id="new_password_confirmation" name="new_password_confirmation"
                                class="w-full px-4 py-2.5 text-sm text-[#1A1A1A] bg-[#F8F9FA] border border-[#E0E0E0] rounded-xl
                                                              focus:outline-none focus:ring-2 focus:ring-primary-600/20 focus:border-primary-600 transition-colors" placeholder="Ulangi password baru">
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit"
                            class="px-5 py-2.5 bg-[#E8F5E9] hover:bg-[#C8E6C9] text-primary-700 text-sm font-bold rounded-xl transition-colors">
                            Perbarui Password
                        </button>
                    </div>
                </form>
            </div>

        </div>

    </div>
@endsection