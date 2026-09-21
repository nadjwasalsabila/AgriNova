@extends('layouts.admin', ['title' => $title])

@section('content')
<div class="space-y-6">

    {{-- ── Header ── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-xl font-bold text-[#1A1A1A]">Kelola Pengguna & Langganan</h2>
                <span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold bg-[#DEF2E7] text-[#0F6E56] rounded-full">
                    Supabase Live
                </span>
            </div>
            <p class="text-xs text-[#9E9E9E] mt-0.5">
                Pantau seluruh akun pengguna terdaftar, status paket langganan aktif, dan rekaman transaksi aplikasi AgriNova.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs text-[#9E9E9E]">Paket Terpopuler:</span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-[#FFF8E1] text-[#B7811D] border border-[#FFE082]">
                ⭐ {{ $popularPlan }}
            </span>
        </div>
    </div>

    {{-- ── 4 Metric Cards Grid ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        {{-- Total Users --}}
        <div class="bg-white rounded-2xl border border-[#E0E0E0] p-5 card-shadow hover:border-primary-100 transition-all">
            <div class="flex items-center justify-between">
                <div class="w-11 h-11 rounded-xl bg-[#DEF2E7] flex items-center justify-center text-[#0F6E56]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <span class="text-xs font-semibold text-[#9E9E9E] bg-[#F8F9FA] px-2.5 py-1 rounded-full">
                    Semua Akun
                </span>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-bold text-[#1A1A1A]">{{ number_format($totalUsers) }}</p>
                <p class="text-xs text-[#9E9E9E] mt-0.5">Total Pengguna Terdaftar</p>
            </div>
        </div>

        {{-- Active Subscribers --}}
        <div class="bg-white rounded-2xl border border-[#E0E0E0] p-5 card-shadow hover:border-primary-100 transition-all">
            <div class="flex items-center justify-between">
                <div class="w-11 h-11 rounded-xl bg-[#E8F5E9] flex items-center justify-center text-[#1B5E20]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <span class="inline-flex items-center gap-1 text-xs font-semibold text-[#1B5E20] bg-[#E8F5E9] px-2.5 py-1 rounded-full">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#1B5E20] animate-pulse"></span>
                    PRO Aktif
                </span>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-bold text-[#1B5E20]">{{ number_format($activeSubscribers) }}</p>
                <p class="text-xs text-[#9E9E9E] mt-0.5">Pelanggan Paket Berbayar</p>
            </div>
        </div>

        {{-- Free Users --}}
        <div class="bg-white rounded-2xl border border-[#E0E0E0] p-5 card-shadow hover:border-primary-100 transition-all">
            <div class="flex items-center justify-between">
                <div class="w-11 h-11 rounded-xl bg-[#F5F5F5] flex items-center justify-center text-[#616161]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <span class="text-xs font-semibold text-[#757575] bg-[#F5F5F5] px-2.5 py-1 rounded-full">
                    Free Tier
                </span>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-bold text-[#424242]">{{ number_format($freeUsers) }}</p>
                <p class="text-xs text-[#9E9E9E] mt-0.5">Pengguna Versi Gratis</p>
            </div>
        </div>

        {{-- Total Revenue --}}
        <div class="bg-white rounded-2xl border border-[#E0E0E0] p-5 card-shadow hover:border-primary-100 transition-all">
            <div class="flex items-center justify-between">
                <div class="w-11 h-11 rounded-xl bg-[#FFF8E1] flex items-center justify-center text-[#D9A441]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-xs font-bold text-[#B7811D] bg-[#FFF8E1] px-2.5 py-1 rounded-full">
                    Omzet Total
                </span>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-bold text-[#1A1A1A]">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
                <p class="text-xs text-[#9E9E9E] mt-0.5">Akumulasi Dari Tabel Subscriptions</p>
            </div>
        </div>
    </div>

    {{-- ── Navigation Tabs ── --}}
    <div class="flex border-b border-[#E0E0E0] gap-6 text-sm font-semibold">
        <a href="{{ route('admin.users.index', array_merge(request()->query(), ['tab' => 'users'])) }}" 
           class="pb-3 border-b-2 flex items-center gap-2 transition-colors {{ $tab === 'users' ? 'border-[#0F6E56] text-[#0F6E56]' : 'border-transparent text-[#9E9E9E] hover:text-[#424242]' }}">
            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <span>Daftar Pengguna ({{ count($users) }})</span>
        </a>
        <a href="{{ route('admin.users.index', array_merge(request()->query(), ['tab' => 'transactions'])) }}" 
           class="pb-3 border-b-2 flex items-center gap-2 transition-colors {{ $tab === 'transactions' ? 'border-[#0F6E56] text-[#0F6E56]' : 'border-transparent text-[#9E9E9E] hover:text-[#424242]' }}">
            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
            <span>Riwayat Transaksi Subscriptions ({{ count($transactions) }})</span>
        </a>
    </div>

    {{-- ── Filter & Search Bar ── --}}
    <div class="bg-white rounded-2xl border border-[#E0E0E0] card-shadow p-4">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col sm:flex-row gap-3">
            <input type="hidden" name="tab" value="{{ $tab }}">

            {{-- Search input --}}
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-[#9E9E9E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" name="search" value="{{ $search ?? '' }}" 
                       placeholder="Cari nama, email, user ID, atau order ID..."
                       class="w-full pl-10 pr-4 py-2.5 text-sm text-[#1A1A1A] bg-[#F8F9FA] border border-[#E0E0E0] rounded-xl
                              focus:outline-none focus:ring-2 focus:ring-primary-600/20 focus:border-primary-600 transition-colors">
            </div>

            {{-- Status filter --}}
            <div class="sm:w-48">
                <select name="status" class="w-full px-3 py-2.5 text-sm text-[#424242] bg-[#F8F9FA] border border-[#E0E0E0] rounded-xl
                                             focus:outline-none focus:ring-2 focus:ring-primary-600/20 focus:border-primary-600 transition-colors">
                    <option value="all" {{ ($status ?? 'all') === 'all' ? 'selected' : '' }}>Semua Status</option>
                    <option value="active" {{ ($status ?? '') === 'active' ? 'selected' : '' }}>🟢 Aktif (PRO)</option>
                    <option value="free" {{ ($status ?? '') === 'free' ? 'selected' : '' }}>⚪ Versi Gratis</option>
                    <option value="expired" {{ ($status ?? '') === 'expired' ? 'selected' : '' }}>🔴 Kadaluarsa</option>
                </select>
            </div>

            {{-- Plan filter --}}
            <div class="sm:w-48">
                <select name="plan" class="w-full px-3 py-2.5 text-sm text-[#424242] bg-[#F8F9FA] border border-[#E0E0E0] rounded-xl
                                           focus:outline-none focus:ring-2 focus:ring-primary-600/20 focus:border-primary-600 transition-colors">
                    <option value="all" {{ ($plan ?? 'all') === 'all' ? 'selected' : '' }}>Semua Paket</option>
                    <option value="Nova Basic" {{ ($plan ?? '') === 'Nova Basic' ? 'selected' : '' }}>Nova Basic (Rp 29k)</option>
                    <option value="Nova Pro" {{ ($plan ?? '') === 'Nova Pro' ? 'selected' : '' }}>Nova Pro (Rp 69k)</option>
                    <option value="Nova Ultimate" {{ ($plan ?? '') === 'Nova Ultimate' ? 'selected' : '' }}>Nova Ultimate (Rp 129k)</option>
                    <option value="Gratis" {{ ($plan ?? '') === 'Gratis' ? 'selected' : '' }}>Gratis / Free</option>
                </select>
            </div>

            {{-- Buttons --}}
            <div class="flex gap-2">
                <button type="submit" 
                        class="px-5 py-2.5 bg-[#0F6E56] hover:bg-[#1B5E20] text-white text-sm font-semibold rounded-xl
                               transition-colors shadow-sm">
                    Filter
                </button>
                @if(! empty($search) || ($status ?? 'all') !== 'all' || ($plan ?? 'all') !== 'all')
                    <a href="{{ route('admin.users.index', ['tab' => $tab]) }}" 
                       class="px-4 py-2.5 border border-[#E0E0E0] hover:bg-[#F8F9FA] text-[#616161] text-sm font-medium rounded-xl transition-colors flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- ── TAB 1: DAFTAR PENGGUNA ── --}}
    @if($tab === 'users')
        <div class="bg-white rounded-2xl border border-[#E0E0E0] card-shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#F8F9FA] border-b border-[#E0E0E0] text-[11px] font-bold text-[#616161] uppercase tracking-wider">
                            <th class="py-3.5 px-5">Pengguna</th>
                            <th class="py-3.5 px-4">Paket Langganan</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-4">Masa Berlaku</th>
                            <th class="py-3.5 px-4">Total Order</th>
                            <th class="py-3.5 px-4">Terdaftar</th>
                            <th class="py-3.5 px-5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F0F0F0] text-sm">
                        @forelse($users as $u)
                            <tr class="hover:bg-[#F8F9FA]/70 transition-colors group">
                                {{-- User Name & Email --}}
                                <td class="py-4 px-5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full overflow-hidden bg-[#DEF2E7] flex items-center justify-center text-[#0F6E56] font-bold text-sm shrink-0 border border-[#C8E6C9]">
                                            @if(! empty($u['avatar']))
                                                <img src="{{ $u['avatar'] }}" alt="{{ $u['name'] }}" class="w-full h-full object-cover">
                                            @else
                                                <img src="{{ asset('assets/images/default-avatar.svg') }}" 
                                                     alt="{{ $u['name'] }}" 
                                                     class="w-full h-full object-cover"
                                                     onerror="this.src='{{ asset('assets/images/default-avatar.png') }}'">
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1.5">
                                                <p class="font-bold text-[#1A1A1A] truncate">{{ $u['name'] }}</p>
                                                @if($u['is_active'])
                                                    <span class="inline-flex items-center px-1.5 py-0.2 text-[9px] font-bold bg-[#E8F5E9] text-[#1B5E20] rounded-full">
                                                        PRO
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="text-xs text-[#9E9E9E] truncate">{{ $u['email'] }}</p>
                                            <p class="text-[10px] text-[#BDBDBD] font-mono mt-0.5 truncate max-w-[200px]" title="{{ $u['id'] }}">
                                                ID: {{ substr($u['id'], 0, 13) }}...
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Plan Name --}}
                                <td class="py-4 px-4">
                                    @php
                                        $planLower = strtolower($u['plan_name']);
                                    @endphp
                                    @if(str_contains($planLower, 'basic'))
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-[#DEF2E7] text-[#0F6E56] border border-[#A3E3CA]">
                                            🌱 {{ $u['plan_name'] }}
                                        </span>
                                    @elseif(str_contains($planLower, 'pro') || str_contains($planLower, 'bulan'))
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-[#E8F5E9] text-[#1B5E20] border border-[#C8E6C9]">
                                            ⚡ {{ $u['plan_name'] }}
                                        </span>
                                    @elseif(str_contains($planLower, 'ultimate') || str_contains($planLower, 'bisnis'))
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-[#FFF8E1] text-[#B7811D] border border-[#FFE082]">
                                            👑 {{ $u['plan_name'] }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-[#F5F5F5] text-[#757575] border border-[#E0E0E0]">
                                            ⚪ Gratis
                                        </span>
                                    @endif
                                </td>

                                {{-- Status --}}
                                <td class="py-4 px-4">
                                    @if($u['status'] === 'active')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-[#E8F5E9] text-[#1B5E20]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#1B5E20] animate-pulse"></span>
                                            Aktif
                                        </span>
                                    @elseif($u['status'] === 'expired')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-[#FFEBEE] text-[#B71C1C]">
                                            Kedaluwarsa
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-[#F5F5F5] text-[#757575]">
                                            Free User
                                        </span>
                                    @endif
                                </td>

                                {{-- Expiry / Period --}}
                                <td class="py-4 px-4 text-xs">
                                    @if(! empty($u['expires_at']))
                                        @php
                                            $exp = \Carbon\Carbon::parse($u['expires_at']);
                                            $isExpired = $exp->isPast();
                                        @endphp
                                        <div>
                                            <p class="font-medium {{ $isExpired ? 'text-[#B71C1C]' : 'text-[#1A1A1A]' }}">
                                                {{ $exp->locale('id')->translatedFormat('d M Y') }}
                                            </p>
                                            <p class="text-[10px] text-[#9E9E9E] mt-0.5">
                                                {{ $isExpired ? 'Lewat ' . $exp->diffForHumans() : 'Sisa ' . $exp->diffForHumans(['parts' => 1]) }}
                                            </p>
                                        </div>
                                    @else
                                        <span class="text-[#BDBDBD]">—</span>
                                    @endif
                                </td>

                                {{-- Total Orders & Amount --}}
                                <td class="py-4 px-4 text-xs">
                                    <p class="font-bold text-[#1A1A1A]">{{ $u['total_orders'] }} Transaksi</p>
                                    @if($u['total_spent'] > 0)
                                        <p class="text-[11px] font-semibold text-[#0F6E56] mt-0.5">
                                            Rp {{ number_format($u['total_spent'], 0, ',', '.') }}
                                        </p>
                                    @else
                                        <p class="text-[11px] text-[#BDBDBD] mt-0.5">Rp 0</p>
                                    @endif
                                </td>

                                {{-- Created At --}}
                                <td class="py-4 px-4 text-xs text-[#616161]">
                                    {{ ! empty($u['created_at']) ? \Carbon\Carbon::parse($u['created_at'])->locale('id')->translatedFormat('d M Y') : '—' }}
                                </td>

                                {{-- Action --}}
                                <td class="py-4 px-5 text-right">
                                    <a href="{{ route('admin.users.show', $u['id']) }}" 
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#F8F9FA] hover:bg-[#DEF2E7] hover:text-[#0F6E56] text-[#424242] text-xs font-semibold rounded-lg border border-[#E0E0E0] transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <span>Detail</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-[#9E9E9E] text-sm">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-12 h-12 text-[#BDBDBD] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        <p class="font-semibold text-[#616161]">Tidak ada data pengguna ditemukan</p>
                                        <p class="text-xs text-[#9E9E9E] mt-1">Coba sesuaikan kata kunci pencarian atau filter status Anda.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    {{-- ── TAB 2: RIWAYAT TRANSAKSI SUBSCRIPTIONS ── --}}
    @else
        <div class="bg-white rounded-2xl border border-[#E0E0E0] card-shadow overflow-hidden">
            <div class="p-4 bg-[#F8F9FA] border-b border-[#E0E0E0] flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-[#1A1A1A]">Rekaman Tabel Subscriptions</h3>
                    <p class="text-xs text-[#9E9E9E] mt-0.5">Seluruh transaksi paket langganan yang tersimpan pada tabel <code>public.subscriptions</code> di Supabase.</p>
                </div>
                <span class="text-xs font-semibold text-[#0F6E56] bg-[#DEF2E7] px-3 py-1 rounded-full">
                    Total: {{ count($transactions) }} Transaksi
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#FAFAFA] border-b border-[#E0E0E0] text-[11px] font-bold text-[#616161] uppercase tracking-wider">
                            <th class="py-3.5 px-5">Order ID</th>
                            <th class="py-3.5 px-4">Pengguna</th>
                            <th class="py-3.5 px-4">Paket</th>
                            <th class="py-3.5 px-4">Nominal</th>
                            <th class="py-3.5 px-4">Pembayaran</th>
                            <th class="py-3.5 px-4">Masa Berlaku</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F0F0F0] text-sm">
                        @forelse($transactions as $tx)
                            <tr class="hover:bg-[#F8F9FA]/70 transition-colors">
                                {{-- Order ID --}}
                                <td class="py-4 px-5">
                                    <p class="font-mono text-xs font-bold text-[#1A1A1A]">{{ $tx['order_id'] ?? '-' }}</p>
                                    <p class="text-[10px] text-[#9E9E9E] mt-0.5">
                                        {{ ! empty($tx['created_at']) ? \Carbon\Carbon::parse($tx['created_at'])->locale('id')->translatedFormat('d M Y, H:i') : '-' }}
                                    </p>
                                </td>

                                {{-- User --}}
                                <td class="py-4 px-4">
                                    <p class="font-bold text-[#1A1A1A] text-xs">{{ $tx['user_name'] }}</p>
                                    <p class="text-xs text-[#9E9E9E]">{{ $tx['user_email'] }}</p>
                                </td>

                                {{-- Plan --}}
                                <td class="py-4 px-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#DEF2E7] text-[#0F6E56]">
                                        {{ $tx['plan_name'] ?? 'Nova Basic' }}
                                    </span>
                                </td>

                                {{-- Amount --}}
                                <td class="py-4 px-4 font-bold text-[#1A1A1A]">
                                    Rp {{ number_format($tx['amount'] ?? 0, 0, ',', '.') }}
                                </td>

                                {{-- Payment Method --}}
                                <td class="py-4 px-4 text-xs font-medium text-[#424242] uppercase">
                                    {{ $tx['payment_type'] ?? 'gopay' }}
                                </td>

                                {{-- Period --}}
                                <td class="py-4 px-4 text-xs">
                                    @if(! empty($tx['started_at']) && ! empty($tx['expires_at']))
                                        <p class="text-[#1A1A1A]">
                                            {{ \Carbon\Carbon::parse($tx['started_at'])->locale('id')->translatedFormat('d M') }} — 
                                            {{ \Carbon\Carbon::parse($tx['expires_at'])->locale('id')->translatedFormat('d M Y') }}
                                        </p>
                                        <p class="text-[10px] text-[#9E9E9E] mt-0.5">
                                            Durasi 30 Hari
                                        </p>
                                    @else
                                        <span class="text-[#BDBDBD]">—</span>
                                    @endif
                                </td>

                                {{-- Status --}}
                                <td class="py-4 px-4">
                                    @if(strtolower($tx['status'] ?? '') === 'active')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-[#E8F5E9] text-[#1B5E20]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#1B5E20] animate-pulse"></span>
                                            Active
                                        </span>
                                    @elseif(strtolower($tx['status'] ?? '') === 'expired')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-[#FFEBEE] text-[#B71C1C]">
                                            Expired
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-[#F5F5F5] text-[#757575]">
                                            {{ ucfirst($tx['status'] ?? 'pending') }}
                                        </span>
                                    @endif
                                </td>

                                {{-- Action --}}
                                <td class="py-4 px-5 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('admin.users.show', $tx['user_id']) }}" 
                                           class="p-1.5 text-[#616161] hover:text-[#0F6E56] hover:bg-[#DEF2E7] rounded-lg transition-colors"
                                           title="Lihat Pengguna">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>

                                        @if(strtolower($tx['status'] ?? '') === 'active')
                                            <form method="POST" action="{{ route('admin.users.subscription.status', $tx['id']) }}" 
                                                  onsubmit="return confirm('Apakah Anda yakin ingin membatalkan status aktif langganan ini?')">
                                                @csrf
                                                <input type="hidden" name="status" value="cancelled">
                                                <button type="submit" 
                                                        class="px-2.5 py-1 text-[11px] font-semibold text-[#B71C1C] hover:bg-[#FFEBEE] rounded-md transition-colors"
                                                        title="Batalkan Langganan">
                                                    Batalkan
                                                </button>
                                            </form>
                                        @elseif(strtolower($tx['status'] ?? '') === 'cancelled' || strtolower($tx['status'] ?? '') === 'expired')
                                            <form method="POST" action="{{ route('admin.users.subscription.status', $tx['id']) }}">
                                                @csrf
                                                <input type="hidden" name="status" value="active">
                                                <button type="submit" 
                                                        class="px-2.5 py-1 text-[11px] font-semibold text-[#0F6E56] hover:bg-[#DEF2E7] rounded-md transition-colors"
                                                        title="Aktifkan Kembali">
                                                    Aktifkan
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-[#9E9E9E] text-sm">
                                    Belum ada rekaman transaksi langganan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</div>
@endsection
