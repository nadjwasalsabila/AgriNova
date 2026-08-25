@extends('layouts.admin', ['title' => $title])

@section('content')

{{-- ── Welcome Banner ── --}}
<div class="brand-gradient rounded-2xl p-6 mb-6 card-shadow-primary relative overflow-hidden">
    <div class="absolute -right-8 -top-8 w-40 h-40 rounded-full bg-white/5 pointer-events-none"></div>
    <div class="absolute -right-4 bottom-0 w-24 h-24 rounded-full bg-white/5 pointer-events-none"></div>

    <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="text-white/70 text-sm font-medium mb-1">
                {{ now()->locale('id')->translatedFormat('l, d F Y') }}
            </p>
            <h2 class="text-white text-xl font-bold">
                Selamat datang, {{ session('admin_name', 'Admin') }}! 👋
            </h2>
            <p class="text-white/70 text-sm mt-1">
                Berikut ringkasan data terkini aplikasi Petani Maju.
            </p>
        </div>
        <div class="shrink-0 flex items-center gap-2">
            {{-- Supabase status --}}
            @if($supabaseOnline)
                <span class="inline-flex items-center gap-2 bg-white/15 text-white text-xs font-semibold
                             px-3 py-1.5 rounded-full border border-white/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-300 animate-pulse"></span>
                    Supabase Terhubung
                </span>
            @else
                <span class="inline-flex items-center gap-2 bg-white/10 text-white/70 text-xs font-semibold
                             px-3 py-1.5 rounded-full border border-white/10">
                    <span class="w-1.5 h-1.5 rounded-full bg-yellow-300"></span>
                    Mode Offline
                </span>
            @endif

            {{-- Auth method badge --}}
            @if(session('auth_method') === 'supabase')
                <span class="hidden sm:inline-flex items-center gap-1.5 bg-white/15 text-white text-xs font-semibold
                             px-3 py-1.5 rounded-full border border-white/20">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    Admin Verified
                </span>
            @endif
        </div>
    </div>
</div>

{{-- ── Stat Cards Grid (2x3) ── --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 mb-6">
    @foreach($stats as $stat)
        @php
            $iconPaths = [
                'lightbulb' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>',
                'bug'       => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6"/>',
                'camera'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>',
                'users'     => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>',
                'plant'     => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3s4.5 0 9 4.5C18.5 12 19 17 19 17s-5-.5-9.5-5C5 7.5 5 3 5 3zM5 3c0 0 0 7 7 11"/>',
                'receipt'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>',
                'pill'      => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.5 12.5l8-8a6 6 0 118.5 8.5l-8 8a6 6 0 01-8.5-8.5z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9l6 6" />',
            ];
            $isNA = $stat['raw'] < 0;
        @endphp

        <div class="bg-white rounded-2xl border border-[#E0E0E0] p-5 card-shadow
                    hover:border-[#C8E6C9] hover:shadow-md transition-all duration-200 group">

            <div class="flex items-start justify-between mb-4">
                {{-- Icon --}}
                <div class="w-11 h-11 rounded-xl {{ $stat['icon_bg'] }} flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 {{ $stat['icon_color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        {!! $iconPaths[$stat['icon']] ?? '' !!}
                    </svg>
                </div>

                {{-- Status dot --}}
                @if($isNA)
                    <span class="text-[10px] font-semibold text-[#9E9E9E] bg-[#F5F5F5] px-2 py-0.5 rounded-full">
                        Tidak ada data
                    </span>
                @else
                    <span class="text-[10px] font-semibold text-[#2E7D32] bg-[#E8F5E9] px-2 py-0.5 rounded-full">
                        Live
                    </span>
                @endif
            </div>

            {{-- Value --}}
            <p class="text-3xl font-bold text-[#1A1A1A] leading-none tracking-tight mb-1
                       {{ $isNA ? 'text-[#BDBDBD]' : '' }}">
                {{ $stat['value'] }}
            </p>
            <p class="text-sm text-[#9E9E9E] font-medium">{{ $stat['label'] }}</p>
        </div>
    @endforeach
</div>

{{-- ── Lower Section ── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    {{-- Recent Scan Activity ── --}}
    <div class="lg:col-span-2 bg-white rounded-2xl border border-[#E0E0E0] card-shadow overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-[#E0E0E0]">
            <div>
                <h3 class="text-sm font-bold text-[#1A1A1A]">Aktivitas Scan AI Terbaru</h3>
                <p class="text-xs text-[#9E9E9E] mt-0.5">Deteksi penyakit dari pengguna mobile</p>
            </div>
            <span class="text-xs font-medium text-[#2E7D32] bg-[#E8F5E9] px-3 py-1 rounded-full cursor-pointer
                         hover:bg-[#C8E6C9] transition-colors">
                Lihat Semua
            </span>
        </div>

        @if(count($recentScans) > 0)
            <div class="divide-y divide-[#F5F5F5]">
                @foreach($recentScans as $scan)
                    @php
                        $isHealthy  = str_contains(strtolower($scan['disease_name'] ?? ''), 'sehat')
                                   || str_contains(strtolower($scan['disease_name'] ?? ''), 'healthy');
                        $confidence = isset($scan['confidence']) ? round($scan['confidence'] * 100) : null;
                        $createdAt  = isset($scan['created_at'])
                            ? \Carbon\Carbon::parse($scan['created_at'])->locale('id')->diffForHumans()
                            : '—';
                    @endphp
                    <div class="flex items-center gap-4 px-5 py-3.5 hover:bg-[#F8F9FA] transition-colors">
                        {{-- Image thumbnail --}}
                        <div class="w-10 h-10 rounded-lg overflow-hidden bg-[#F0F4F0] shrink-0">
                            @if(!empty($scan['image_url']))
                                <img src="{{ $scan['image_url'] }}" alt="scan"
                                     class="w-full h-full object-cover"
                                     onerror="this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center\'><svg class=\'w-5 h-5 text-[#BDBDBD]\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z\'/></svg></div>'">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-[#BDBDBD]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-[#1A1A1A] truncate">
                                {{ $scan['disease_name'] ?? 'Unknown' }}
                            </p>
                            <p class="text-xs text-[#9E9E9E] truncate">
                                {{ $scan['plant_type'] ?? '—' }} • {{ $createdAt }}
                            </p>
                        </div>

                        {{-- Confidence + Status --}}
                        <div class="text-right shrink-0">
                            @if($confidence !== null)
                                <p class="text-sm font-bold {{ $isHealthy ? 'text-[#2E7D32]' : 'text-[#B71C1C]' }}">
                                    {{ $confidence }}%
                                </p>
                            @endif
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full
                                {{ $isHealthy
                                    ? 'bg-[#E8F5E9] text-[#1B5E20]'
                                    : 'bg-[#FFEBEE] text-[#B71C1C]' }}">
                                {{ $isHealthy ? 'Sehat' : 'Sakit' }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-14 px-6 text-center">
                <div class="w-14 h-14 rounded-2xl bg-[#F0F4F0] flex items-center justify-center mb-3">
                    <svg class="w-7 h-7 text-[#BDBDBD]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-[#424242] mb-1">Belum ada aktivitas</p>
                <p class="text-xs text-[#9E9E9E] max-w-xs">
                    @if($supabaseOnline)
                        Belum ada data scan dari pengguna mobile.
                    @else
                        Konfigurasi Supabase diperlukan untuk menampilkan data.
                    @endif
                </p>
            </div>
        @endif
    </div>

    {{-- Info Panel ── --}}
    <div class="flex flex-col gap-4">

        {{-- Supabase Connection Status --}}
        <div class="bg-white rounded-2xl border border-[#E0E0E0] card-shadow p-5">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-9 h-9 rounded-xl {{ $supabaseOnline ? 'bg-[#E8F5E9]' : 'bg-[#FFF3E0]' }} flex items-center justify-center">
                    <svg class="w-5 h-5 {{ $supabaseOnline ? 'text-[#1B5E20]' : 'text-[#E65100]' }}"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-[#1A1A1A]">Koneksi Supabase</p>
                    <p class="text-xs text-[#9E9E9E]">Status database</p>
                </div>
            </div>

            @if($supabaseOnline)
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#2E7D32] animate-pulse"></span>
                    <span class="text-xs font-medium text-[#1B5E20]">Terhubung</span>
                </div>
                <p class="text-xs text-[#BDBDBD] mt-2">Data ditampilkan secara real-time dari Supabase.</p>
            @else
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#FF8F00]"></span>
                    <span class="text-xs font-medium text-[#E65100]">Belum dikonfigurasi</span>
                </div>
                <p class="text-xs text-[#BDBDBD] mt-2 leading-relaxed">
                    Isi <code class="bg-[#F8F9FA] px-1 rounded text-[#424242]">SUPABASE_URL</code>
                    dan <code class="bg-[#F8F9FA] px-1 rounded text-[#424242]">SUPABASE_ANON_KEY</code>
                    di file <code class="bg-[#F8F9FA] px-1 rounded text-[#424242]">.env</code>.
                </p>
            @endif
        </div>

        {{-- Auth Info --}}
        <div class="bg-white rounded-2xl border border-[#E0E0E0] card-shadow p-5">
            <p class="text-xs font-semibold text-[#9E9E9E] uppercase tracking-wide mb-3">Sesi Admin</p>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-[#9E9E9E]">Metode Auth</span>
                    <span class="text-xs font-semibold text-[#424242] bg-[#F8F9FA] px-2 py-0.5 rounded-md capitalize">
                        {{ session('auth_method', 'env') === 'supabase' ? 'Supabase' : 'Dev Mode' }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-[#9E9E9E]">Admin ID</span>
                    <span class="text-xs font-semibold text-[#424242] bg-[#F8F9FA] px-2 py-0.5 rounded-md max-w-[130px] truncate">
                        {{ session('admin_id') ? substr(session('admin_id'), 0, 8).'…' : 'N/A' }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-[#9E9E9E]">Token</span>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-md
                        {{ session('supabase_token') ? 'text-[#1B5E20] bg-[#E8F5E9]' : 'text-[#9E9E9E] bg-[#F8F9FA]' }}">
                        {{ session('supabase_token') ? 'Valid' : 'Tidak Ada' }}
                    </span>
                </div>
                @if(session('supabase_expires_at'))
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-[#9E9E9E]">Expired</span>
                        <span class="text-xs font-semibold text-[#424242] bg-[#F8F9FA] px-2 py-0.5 rounded-md">
                            {{ \Carbon\Carbon::createFromTimestamp(session('supabase_expires_at'))->locale('id')->diffForHumans() }}
                        </span>
                    </div>
                @endif
            </div>
        </div>

        {{-- System Info --}}
        <div class="bg-white rounded-2xl border border-[#E0E0E0] card-shadow p-5">
            <p class="text-xs font-semibold text-[#9E9E9E] uppercase tracking-wide mb-3">Informasi Sistem</p>
            <div class="space-y-3">
                @foreach([
                    ['label' => 'Laravel', 'value' => app()->version()],
                    ['label' => 'PHP',     'value' => PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION],
                    ['label' => 'Env',     'value' => app()->environment()],
                ] as $info)
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-[#9E9E9E]">{{ $info['label'] }}</span>
                        <span class="text-xs font-semibold text-[#424242] bg-[#F8F9FA] px-2 py-0.5 rounded-md">
                            {{ $info['value'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Mobile App Badge --}}
        <div class="rounded-2xl p-5 bg-[#E8F5E9] border border-[#C8E6C9]">
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-xl bg-[#1B5E20] flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-[#1B5E20]">Aplikasi Mobile</p>
                    <p class="text-xs text-[#2E7D32] mt-1 leading-relaxed">
                        Konten yang dikelola di sini akan tampil di aplikasi Petani Maju.
                    </p>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
