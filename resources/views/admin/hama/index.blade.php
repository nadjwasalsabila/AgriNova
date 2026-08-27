@extends('layouts.admin', ['title' => $title])

@section('content')
    {{-- ── Header Area ── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-[#1A1A1A]">Database Hama &amp; Penyakit</h2>
            <p class="text-xs text-[#9E9E9E] mt-0.5">Lihat daftar hama dan penyakit tanaman berdasarkan kategori jenis tanaman.</p>
        </div>
    </div>

    {{-- ── Category Navbar (5 tabs) ── --}}
    <div class="bg-white rounded-2xl border border-[#E0E0E0] card-shadow p-1.5 mb-4 flex gap-1 overflow-x-auto">
        @php
            $tabs = [
                ['label' => 'Hama',          'value' => 'hama',          'icon' => '🐛'],
                ['label' => 'Semua Penyakit','value' => 'semua_penyakit','icon' => '🦠'],
                ['label' => 'Padi',          'value' => 'padi',          'icon' => '🌾'],
                ['label' => 'Teh',           'value' => 'teh',           'icon' => '🍵'],
                ['label' => 'Tomat',         'value' => 'tomat',         'icon' => '🍅'],
            ];
        @endphp

        @foreach($tabs as $t)
            @php
                $isActive = ($activeTab ?? 'hama') === $t['value'];
                $href = route('admin.hama.index', array_filter([
                    'tab'    => $t['value'],
                    'search' => $search ?? '',
                ]));
            @endphp
            <a href="{{ $href }}"
               class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold whitespace-nowrap transition-all duration-200
                      {{ $isActive
                         ? 'bg-primary-600 text-white shadow-sm'
                         : 'text-[#616161] hover:bg-[#F0F4F0] hover:text-primary-700' }}">
                <span>{{ $t['icon'] }}</span>
                <span>{{ $t['label'] }}</span>
                @if($isActive)
                    <span class="w-1.5 h-1.5 rounded-full bg-white/70 ml-0.5"></span>
                @endif
            </a>
        @endforeach
    </div>

    {{-- ── Filter & Search Bar ── --}}
    <div class="bg-white rounded-2xl border border-[#E0E0E0] card-shadow p-4 mb-6">
        <form method="GET" action="{{ route('admin.hama.index') }}" class="flex flex-col sm:flex-row gap-3">
            {{-- Preserve active tab when searching --}}
            <input type="hidden" name="tab" value="{{ $activeTab ?? 'hama' }}">

            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-[#9E9E9E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input
                    type="text"
                    name="search"
                    value="{{ $search ?? '' }}"
                    placeholder="Cari berdasarkan nama atau deskripsi..."
                    class="w-full pl-9 pr-4 py-2.5 text-sm text-[#1A1A1A] bg-[#F8F9FA] border border-[#E0E0E0] rounded-xl
                           focus:outline-none focus:ring-2 focus:ring-primary-600/20 focus:border-primary-600 transition-colors"
                >
            </div>
            <div class="flex gap-2">
                @if(! empty($search))
                    <a href="{{ route('admin.hama.index', ['tab' => $activeTab ?? 'hama']) }}"
                       class="px-4 py-2.5 border border-[#E0E0E0] hover:bg-[#F8F9FA] text-[#616161]
                              text-sm font-medium rounded-xl transition-colors flex items-center justify-center">
                        Reset
                    </a>
                @endif
                <button type="submit"
                        class="px-5 py-2.5 bg-[#E8F5E9] hover:bg-[#C8E6C9] text-primary-700 text-sm font-bold rounded-xl transition-colors">
                    Cari
                </button>
            </div>
        </form>
    </div>

    {{-- ── Result count label ── --}}
    <div class="flex items-center gap-2 mb-3 px-1">
        <span class="text-xs text-[#9E9E9E]">Menampilkan <strong class="text-[#424242]">{{ count($hamaList) }}</strong> data</span>
        @if(! empty($search))
            <span class="text-xs text-[#9E9E9E]">untuk pencarian "<em>{{ $search }}</em>"</span>
        @endif
    </div>

    {{-- ── Data Table ── --}}
    <div class="bg-white rounded-2xl border border-[#E0E0E0] card-shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#F8F9FA] border-b border-[#E0E0E0]">
                        <th class="px-6 py-4 text-xs font-semibold text-[#9E9E9E] uppercase tracking-wider w-20">Gambar</th>
                        <th class="px-6 py-4 text-xs font-semibold text-[#9E9E9E] uppercase tracking-wider">Nama</th>

                        @if($dataSource === 'hama')
                            <th class="px-6 py-4 text-xs font-semibold text-[#9E9E9E] uppercase tracking-wider">Kategori</th>
                            <th class="px-6 py-4 text-xs font-semibold text-[#9E9E9E] uppercase tracking-wider">Ciri-Ciri</th>
                            <th class="px-6 py-4 text-xs font-semibold text-[#9E9E9E] uppercase tracking-wider">Cara Mengatasi</th>
                            <th class="px-6 py-4 text-xs font-semibold text-[#9E9E9E] uppercase tracking-wider text-right w-36">Aksi</th>
                        @else
                            <th class="px-6 py-4 text-xs font-semibold text-[#9E9E9E] uppercase tracking-wider">Jenis Tanaman</th>
                            <th class="px-6 py-4 text-xs font-semibold text-[#9E9E9E] uppercase tracking-wider">Deskripsi</th>
                            <th class="px-6 py-4 text-xs font-semibold text-[#9E9E9E] uppercase tracking-wider">Penanganan</th>
                            <th class="px-6 py-4 text-xs font-semibold text-[#9E9E9E] uppercase tracking-wider">Obat</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F0F0F0]">
                    @forelse($hamaList as $item)
                        <tr class="hover:bg-[#F8F9FA]/50 transition-colors group">

                            {{-- Gambar --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="w-12 h-12 rounded-xl bg-[#F0F4F0] border border-[#E0E0E0] overflow-hidden flex items-center justify-center shrink-0">
                                    @if(! empty($item['gambar_url']))
                                        <img src="{{ $item['gambar_url'] }}" alt="{{ $item['nama'] }}"
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200"
                                             onerror="this.style.display='none';this.parentElement.innerHTML='<span class=\'text-[10px] text-[#9E9E9E] font-medium p-1 text-center\'>No img</span>'">
                                    @else
                                        <svg class="w-5 h-5 text-[#BDBDBD]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    @endif
                                </div>
                            </td>

                            {{-- Nama + Deskripsi --}}
                            <td class="px-6 py-4">
                                <div class="font-bold text-[#1A1A1A] text-sm">{{ $item['nama'] }}</div>
                                <div class="text-xs text-[#9E9E9E] max-w-xs truncate mt-0.5">{{ $item['deskripsi'] ?? '-' }}</div>
                            </td>

                            @if($dataSource === 'hama')
                                {{-- Kategori badge --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $kat = $item['kategori'] ?? 'Umum';
                                        $katColor = match($kat) {
                                            'Serangga' => 'bg-amber-50 text-amber-700 border-amber-100',
                                            'Moluska'  => 'bg-blue-50 text-blue-700 border-blue-100',
                                            'Mamalia'  => 'bg-orange-50 text-orange-700 border-orange-100',
                                            'Ulat'     => 'bg-lime-50 text-lime-700 border-lime-100',
                                            default    => 'bg-gray-50 text-gray-600 border-gray-100',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $katColor }}">
                                        {{ $kat }}
                                    </span>
                                </td>

                                {{-- Ciri-Ciri --}}
                                <td class="px-6 py-4 text-sm text-[#616161]">
                                    <div class="max-w-xs truncate">{{ $item['ciri_ciri'] ?? '-' }}</div>
                                </td>

                                {{-- Cara Mengatasi --}}
                                <td class="px-6 py-4 text-sm text-[#616161]">
                                    <div class="max-w-xs truncate">{{ $item['cara_mengatasi'] ?? '-' }}</div>
                                </td>

                                {{-- Aksi --}}
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <a href="{{ route('admin.hama.show', $item['id']) }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#E8F5E9] hover:bg-[#C8E6C9]
                                              text-primary-700 text-xs font-bold rounded-lg transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                        </svg>
                                        Kelola Obat
                                    </a>
                                </td>

                            @else
                                {{-- Jenis Tanaman badge (penyakit) --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $kat = $item['kategori'] ?? '-';
                                        $katIcon = match($kat) {
                                            'Padi'  => '🌾',
                                            'Teh'   => '🍵',
                                            'Tomat' => '🍅',
                                            default => '🌿',
                                        };
                                        $katColor = match($kat) {
                                            'Padi'  => 'bg-yellow-50 text-yellow-700 border-yellow-100',
                                            'Teh'   => 'bg-green-50 text-green-700 border-green-100',
                                            'Tomat' => 'bg-red-50 text-red-700 border-red-100',
                                            default => 'bg-gray-50 text-gray-600 border-gray-100',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold border {{ $katColor }}">
                                        {{ $katIcon }} {{ $kat }}
                                    </span>
                                </td>

                                {{-- Deskripsi --}}
                                <td class="px-6 py-4 text-sm text-[#616161]">
                                    <div class="max-w-xs truncate">{{ $item['deskripsi'] ?? '-' }}</div>
                                </td>

                                {{-- Penanganan --}}
                                <td class="px-6 py-4 text-sm text-[#616161]">
                                    <div class="max-w-xs truncate">{{ $item['cara_mengatasi'] ?? '-' }}</div>
                                </td>

                                {{-- Obat --}}
                                <td class="px-6 py-4 text-sm text-[#616161]">
                                    <div class="max-w-xs truncate">{{ $item['obat'] ?? '-' }}</div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-sm text-[#9E9E9E]">
                                <svg class="w-10 h-10 text-[#BDBDBD] mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p>Tidak ada data yang ditemukan.</p>
                                @if(! empty($search))
                                    <p class="mt-1 text-xs">Coba hapus filter pencarian.</p>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
