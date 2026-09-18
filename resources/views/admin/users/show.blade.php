@extends('layouts.admin', ['title' => $title])

@section('content')
<div class="space-y-6">

    {{-- ── Top Navigation & Back Button ── --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.users.index') }}" 
               class="w-9 h-9 rounded-xl bg-white border border-[#E0E0E0] flex items-center justify-center text-[#616161] hover:text-[#0F6E56] hover:border-primary-100 transition-colors card-shadow">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h2 class="text-xl font-bold text-[#1A1A1A]">{{ $user['name'] }}</h2>
                <p class="text-xs text-[#9E9E9E]">ID Pengguna: <span class="font-mono text-[#616161]">{{ $user['id'] }}</span></p>
            </div>
        </div>
        <div>
            @if($user['is_active'])
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold bg-[#E8F5E9] text-[#1B5E20] border border-[#C8E6C9] shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-[#1B5E20] animate-pulse"></span>
                    PRO Aktif ({{ $user['plan_name'] }})
                </span>
            @else
                <span class="inline-flex items-center px-3.5 py-1.5 rounded-full text-xs font-semibold bg-[#F5F5F5] text-[#757575] border border-[#E0E0E0]">
                    Pengguna Gratis
                </span>
            @endif
        </div>
    </div>

    {{-- ── Grid 2 Kolom ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ── Kolom Kiri: Profil Pengguna ── --}}
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-2xl border border-[#E0E0E0] card-shadow p-6 text-center">
                {{-- Avatar --}}
                <div class="w-20 h-20 rounded-full overflow-hidden brand-gradient mx-auto flex items-center justify-center text-white text-2xl font-bold mb-4 shadow-md border-2 border-white ring-2 ring-[#DEF2E7]">
                    @if(! empty($user['avatar']))
                        <img src="{{ $user['avatar'] }}" alt="{{ $user['name'] }}" class="w-full h-full object-cover">
                    @else
                        <img src="{{ asset('assets/images/profiles.png') }}" 
                             alt="{{ $user['name'] }}" 
                             class="w-full h-full object-cover"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                        <span style="display: none;" class="w-full h-full items-center justify-center text-white text-2xl font-bold">
                            {{ strtoupper(substr($user['name'], 0, 1)) }}
                        </span>
                    @endif
                </div>

                <h3 class="text-lg font-bold text-[#1A1A1A]">{{ $user['name'] }}</h3>
                <p class="text-xs text-[#9E9E9E] mt-0.5">{{ $user['email'] }}</p>

                <div class="mt-4 pt-4 border-t border-[#F0F0F0] text-left space-y-3 text-xs">
                    <div>
                        <span class="text-[#9E9E9E] block mb-0.5">Status Langganan</span>
                        @if($user['is_active'])
                            <span class="font-bold text-[#1B5E20] bg-[#E8F5E9] px-2.5 py-1 rounded-md inline-block">
                                ⭐ {{ $user['plan_name'] }}
                            </span>
                        @else
                            <span class="font-semibold text-[#757575] bg-[#F5F5F5] px-2.5 py-1 rounded-md inline-block">
                                Gratis / Free Plan
                            </span>
                        @endif
                    </div>

                    <div>
                        <span class="text-[#9E9E9E] block mb-0.5">No. Telepon</span>
                        <span class="font-semibold text-[#1A1A1A]">{{ $user['phone'] ?? 'Belum diisi' }}</span>
                    </div>

                    <div>
                        <span class="text-[#9E9E9E] block mb-0.5">Tanggal Registrasi</span>
                        <span class="font-semibold text-[#1A1A1A]">
                            {{ ! empty($user['created_at']) ? \Carbon\Carbon::parse($user['created_at'])->locale('id')->translatedFormat('l, d F Y H:i') : '—' }}
                        </span>
                    </div>

                    <div>
                        <span class="text-[#9E9E9E] block mb-0.5">Terakhir Masuk (Sign In)</span>
                        <span class="font-semibold text-[#1A1A1A]">
                            {{ ! empty($user['last_sign_in_at']) ? \Carbon\Carbon::parse($user['last_sign_in_at'])->locale('id')->translatedFormat('d F Y, H:i') : 'Belum pernah sign in' }}
                        </span>
                    </div>

                    <div>
                        <span class="text-[#9E9E9E] block mb-0.5">Total Dibelanjakan</span>
                        <span class="font-bold text-[#0F6E56] text-sm">
                            Rp {{ number_format($user['total_spent'], 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Metadata Raw Card --}}
            <div class="bg-white rounded-2xl border border-[#E0E0E0] card-shadow p-5">
                <h4 class="text-xs font-bold text-[#1A1A1A] uppercase tracking-wider mb-2">Metadata Supabase</h4>
                <div class="bg-[#F8F9FA] rounded-xl p-3 border border-[#E0E0E0] text-[11px] font-mono text-[#424242] overflow-x-auto max-h-48">
                    <pre>{{ json_encode($user['user_metadata'], JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>
        </div>

        {{-- ── Kolom Kanan: Detail Langganan & Riwayat ── --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Kartu Status Langganan Saat Ini --}}
            <div class="brand-gradient rounded-2xl p-6 text-white card-shadow-primary relative overflow-hidden">
                <div class="absolute -right-8 -top-8 w-40 h-40 rounded-full bg-white/5 pointer-events-none"></div>
                <div class="absolute -right-4 bottom-0 w-24 h-24 rounded-full bg-white/5 pointer-events-none"></div>

                <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold bg-white/15 text-white rounded-full border border-white/20 mb-2">
                            Paket Saat Ini
                        </span>
                        <h3 class="text-2xl font-bold">{{ $user['plan_name'] }}</h3>
                        @if($user['is_active'])
                            <p class="text-white/80 text-xs mt-1">
                                Paket aktif memberikan akses tak terbatas ke seluruh fitur cerdas AgriNova mobile.
                            </p>
                        @else
                            <p class="text-white/80 text-xs mt-1">
                                Pengguna saat ini berada di paket gratis standar.
                            </p>
                        @endif
                    </div>

                    <div class="shrink-0 text-left sm:text-right">
                        @if($user['is_active'] && ! empty($user['expires_at']))
                            @php
                                $expiry = \Carbon\Carbon::parse($user['expires_at']);
                            @endphp
                            <p class="text-xs text-white/70">Berlaku Hingga</p>
                            <p class="text-lg font-bold text-white">{{ $expiry->locale('id')->translatedFormat('d F Y') }}</p>
                            <p class="text-xs text-amber-300 font-semibold mt-0.5">
                                {{ $expiry->diffForHumans() }}
                            </p>
                        @else
                            <p class="text-xs text-white/70">Masa Berlaku</p>
                            <p class="text-sm font-bold text-white">Tidak terbatas (Gratis)</p>
                        @endif
                    </div>
                </div>

                {{-- Fitur Unggulan Paket --}}
                <div class="mt-6 pt-5 border-t border-white/15 grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Diagnosa Penyakit & Scan Tanpa Batas</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Konsultasi Asisten Tani AI Prioritas</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Jadwal & Notifikasi Cuaca Presisi</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Akses Rekomendasi Obat Lengkap</span>
                    </div>
                </div>
            </div>

            {{-- Tabel Riwayat Seluruh Transaksi Langganan Pengguna Ini --}}
            <div class="bg-white rounded-2xl border border-[#E0E0E0] card-shadow overflow-hidden">
                <div class="p-5 border-b border-[#E0E0E0] flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-bold text-[#1A1A1A]">Riwayat Transaksi Pengguna</h4>
                        <p class="text-xs text-[#9E9E9E] mt-0.5">Daftar rekaman pesanan dari tabel <code>subscriptions</code> milik pengguna ini.</p>
                    </div>
                    <span class="text-xs font-semibold text-[#0F6E56] bg-[#DEF2E7] px-3 py-1 rounded-full">
                        {{ count($user['subscriptions']) }} Catatan
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-[#F8F9FA] border-b border-[#E0E0E0] text-[11px] font-bold text-[#616161] uppercase tracking-wider">
                                <th class="py-3 px-4">Order ID</th>
                                <th class="py-3 px-4">Paket</th>
                                <th class="py-3 px-4">Nominal</th>
                                <th class="py-3 px-4">Metode</th>
                                <th class="py-3 px-4">Masa Aktif</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F0F0F0] text-xs">
                            @forelse($user['subscriptions'] as $sub)
                                <tr class="hover:bg-[#F8F9FA]/70 transition-colors">
                                    <td class="py-3.5 px-4 font-mono font-bold text-[#1A1A1A]">
                                        {{ $sub['order_id'] ?? '-' }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md font-bold bg-[#DEF2E7] text-[#0F6E56]">
                                            {{ $sub['plan_name'] ?? 'Nova Basic' }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 font-bold text-[#1A1A1A]">
                                        Rp {{ number_format($sub['amount'] ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3.5 px-4 uppercase text-[#616161]">
                                        {{ $sub['payment_type'] ?? 'gopay' }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        @if(! empty($sub['started_at']) && ! empty($sub['expires_at']))
                                            {{ \Carbon\Carbon::parse($sub['started_at'])->locale('id')->translatedFormat('d M') }} — 
                                            {{ \Carbon\Carbon::parse($sub['expires_at'])->locale('id')->translatedFormat('d M Y') }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4">
                                        @if(strtolower($sub['status'] ?? '') === 'active')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full font-bold bg-[#E8F5E9] text-[#1B5E20]">
                                                Active
                                            </span>
                                        @elseif(strtolower($sub['status'] ?? '') === 'expired')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full font-bold bg-[#FFEBEE] text-[#B71C1C]">
                                                Expired
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full font-medium bg-[#F5F5F5] text-[#757575]">
                                                {{ ucfirst($sub['status'] ?? 'pending') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        @if(strtolower($sub['status'] ?? '') === 'active')
                                            <form method="POST" action="{{ route('admin.users.subscription.status', $sub['id']) }}"
                                                  onsubmit="return confirm('Ubah status langganan ini menjadi cancelled?')">
                                                @csrf
                                                <input type="hidden" name="status" value="cancelled">
                                                <button type="submit" class="text-[#B71C1C] hover:underline font-semibold">
                                                    Batalkan
                                                </button>
                                            </form>
                                        @elseif(strtolower($sub['status'] ?? '') === 'cancelled' || strtolower($sub['status'] ?? '') === 'expired')
                                            <form method="POST" action="{{ route('admin.users.subscription.status', $sub['id']) }}">
                                                @csrf
                                                <input type="hidden" name="status" value="active">
                                                <button type="submit" class="text-[#0F6E56] hover:underline font-semibold">
                                                    Aktifkan
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-[#9E9E9E]">
                                        Pengguna ini belum memiliki riwayat pembelian di tabel <code>subscriptions</code>.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
