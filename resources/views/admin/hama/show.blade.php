@extends('layouts.admin', ['title' => $title])

@section('content')
    {{-- ── Header Area ── --}}
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('admin.hama.index') }}"
           class="w-9 h-9 flex items-center justify-center rounded-xl bg-white border border-[#E0E0E0] text-[#616161] hover:text-[#1B5E20] hover:bg-primary-50 transition-colors"
           title="Kembali ke Database Hama">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div>
            <h2 class="text-xl font-bold text-[#1A1A1A]">Kelola Rekomendasi Obat</h2>
            <p class="text-xs text-[#9E9E9E] mt-0.5">Atur obat-obatan yang disarankan untuk membasmi hama atau menyembuhkan penyakit ini.</p>
        </div>
    </div>

    {{-- ── Error Notification ── --}}
    @if($errors->any())
        <div class="mb-5 flex items-start gap-3 bg-danger-50 border border-danger-400/30 text-danger-700 px-4 py-3 rounded-xl text-sm">
            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <p class="font-semibold text-xs">Aksi gagal dilakukan</p>
                <p class="text-xs mt-0.5 text-danger-700/80">{{ $errors->first() }}</p>
            </div>
        </div>
    @endif

    {{-- ── Layout Grid ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ── Left Side: Disease/Pest Detail Context ── --}}
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-2xl border border-[#E0E0E0] card-shadow overflow-hidden">
                {{-- Pest Image --}}
                <div class="w-full aspect-[4/3] bg-gray-100 relative">
                    @if(! empty($hama['gambar_url']))
                        <img src="{{ $hama['gambar_url'] }}" alt="{{ $hama['nama'] }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex flex-col items-center justify-center text-[#9E9E9E]">
                            <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-xs font-semibold">Tidak ada gambar</span>
                        </div>
                    @endif
                    <span class="absolute top-3 left-3 inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-[#B71C1C] text-white shadow-sm">
                        {{ $hama['kategori'] ?? 'Umum' }}
                    </span>
                </div>

                {{-- Details Content --}}
                <div class="p-5 space-y-4">
                    <div>
                        <h3 class="text-lg font-bold text-[#1A1A1A]">{{ $hama['nama'] }}</h3>
                        <p class="text-[11px] text-[#9E9E9E] mt-1">Dibuat pada: {{ date('d F Y', strtotime($hama['created_at'])) }}</p>
                    </div>

                    <div class="border-t border-[#F0F0F0] pt-3">
                        <h4 class="text-xs font-bold text-[#424242] uppercase tracking-wider">Deskripsi</h4>
                        <p class="text-xs text-[#616161] leading-relaxed mt-1">{{ $hama['deskripsi'] ?? 'Tidak ada deskripsi.' }}</p>
                    </div>

                    @if(! empty($hama['ciri_ciri']))
                        <div class="border-t border-[#F0F0F0] pt-3">
                            <h4 class="text-xs font-bold text-[#424242] uppercase tracking-wider">Ciri-Ciri</h4>
                            <p class="text-xs text-[#616161] leading-relaxed mt-1">{{ $hama['ciri_ciri'] }}</p>
                        </div>
                    @endif

                    @if(! empty($hama['dampak']))
                        <div class="border-t border-[#F0F0F0] pt-3">
                            <h4 class="text-xs font-bold text-[#424242] uppercase tracking-wider">Dampak</h4>
                            <ul class="list-disc pl-4 text-xs text-[#616161] leading-relaxed mt-1 space-y-1">
                                @foreach(explode("\n", $hama['dampak']) as $dampak)
                                    @if(trim($dampak) !== '')
                                        <li>{{ trim($dampak) }}</li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(! empty($hama['cara_mengatasi']))
                        <div class="border-t border-[#F0F0F0] pt-3">
                            <h4 class="text-xs font-bold text-[#424242] uppercase tracking-wider">Cara Mengatasi secara Umum</h4>
                            <p class="text-xs text-[#616161] leading-relaxed mt-1">{{ $hama['cara_mengatasi'] }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── Right Side: Relation Management ── --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- 1. Form to Add Recommendation --}}
            <div class="bg-white rounded-2xl border border-[#E0E0E0] card-shadow p-5">
                <h3 class="text-sm font-bold text-[#1A1A1A] mb-4">Tambah Rekomendasi Obat Baru</h3>

                @if($availableObat->isNotEmpty())
                    <form method="POST" action="{{ route('admin.hama.rekomendasi.store', $hama['id']) }}" class="flex flex-col sm:flex-row gap-3">
                        @csrf
                        <div class="flex-1">
                            <select
                                name="obat_id"
                                id="obat_id"
                                required
                                class="w-full px-4 py-2.5 text-sm text-[#1A1A1A] bg-white border border-[#E0E0E0] rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-600/20 focus:border-primary-600 transition-colors"
                            >
                                <option value="" disabled selected>Pilih obat untuk direkomendasikan...</option>
                                @foreach($availableObat as $obat)
                                    <option value="{{ $obat['id'] }}">
                                        {{ $obat['nama'] }} ({{ $obat['kategori'] }}) - Rp {{ number_format($obat['harga'], 0, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit"
                                class="px-5 py-2.5 bg-primary-700 hover:bg-primary-600 text-white text-sm font-bold rounded-xl card-shadow transition-colors whitespace-nowrap">
                            + Tambah Rekomendasi
                        </button>
                    </form>
                @else
                    <div class="bg-[#F8F9FA] rounded-xl border border-[#E0E0E0] p-4 text-center text-xs text-[#9E9E9E] font-medium">
                        Semua obat yang aktif sudah direkomendasikan untuk hama/penyakit ini.
                    </div>
                @endif
            </div>

            {{-- 2. Current Recommendations List --}}
            <div class="bg-white rounded-2xl border border-[#E0E0E0] card-shadow overflow-hidden">
                <div class="p-5 border-b border-[#E0E0E0]">
                    <h3 class="text-sm font-bold text-[#1A1A1A]">Daftar Obat yang Direkomendasikan</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-[#F8F9FA] border-b border-[#E0E0E0]">
                                <th class="px-5 py-3.5 text-xs font-semibold text-[#9E9E9E] uppercase tracking-wider w-16">Gambar</th>
                                <th class="px-5 py-3.5 text-xs font-semibold text-[#9E9E9E] uppercase tracking-wider">Nama Obat</th>
                                <th class="px-5 py-3.5 text-xs font-semibold text-[#9E9E9E] uppercase tracking-wider">Kategori</th>
                                <th class="px-5 py-3.5 text-xs font-semibold text-[#9E9E9E] uppercase tracking-wider">Harga</th>
                                <th class="px-5 py-3.5 text-xs font-semibold text-[#9E9E9E] uppercase tracking-wider">Status</th>
                                <th class="px-5 py-3.5 text-xs font-semibold text-[#9E9E9E] uppercase tracking-wider text-right w-20">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F0F0F0]">
                            @forelse($recommendations as $rec)
                                @php
                                    $obat = $rec['obat'] ?? null;
                                @endphp
                                @if($obat)
                                    <tr class="hover:bg-[#F8F9FA]/50 transition-colors group">
                                        {{-- Image --}}
                                        <td class="px-5 py-3 whitespace-nowrap">
                                            <div class="w-10 h-10 rounded-lg bg-[#F0F4F0] border border-[#E0E0E0] overflow-hidden flex items-center justify-center shrink-0">
                                                @if(! empty($obat['gambar']))
                                                    <img src="{{ $obat['gambar'] }}" alt="{{ $obat['nama'] }}" class="w-full h-full object-cover">
                                                @else
                                                    <svg class="w-4 h-4 text-[#9E9E9E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                              d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 9.172V5L8 4z"/>
                                                    </svg>
                                                @endif
                                            </div>
                                        </td>

                                        {{-- Name --}}
                                        <td class="px-5 py-3">
                                            <div class="font-bold text-[#1A1A1A] text-sm">{{ $obat['nama'] }}</div>
                                        </td>

                                        {{-- Kategori --}}
                                        <td class="px-5 py-3 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-primary-50 text-primary-700 border border-primary-100">
                                                {{ $obat['kategori'] }}
                                            </span>
                                        </td>

                                        {{-- Harga --}}
                                        <td class="px-5 py-3 whitespace-nowrap text-sm text-[#424242]">
                                            Rp {{ number_format($obat['harga'], 0, ',', '.') }}
                                        </td>

                                        {{-- Status --}}
                                        <td class="px-5 py-3 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $obat['status'] === 'Aktif' ? 'bg-[#E8F5E9] text-[#2E7D32]' : 'bg-[#FFEBEE] text-[#C62828]' }}">
                                                {{ $obat['status'] }}
                                            </span>
                                        </td>

                                        {{-- Action (Delete relation) --}}
                                        <td class="px-5 py-3 whitespace-nowrap text-right text-sm">
                                            <form method="POST" action="{{ route('admin.hama.rekomendasi.destroy', [$hama['id'], $rec['id']]) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus rekomendasi obat {{ $obat['nama'] }} untuk hama ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="w-8 h-8 flex items-center justify-center rounded-lg text-[#9E9E9E] hover:text-danger-700 hover:bg-danger-50 transition-colors"
                                                        title="Hapus Rekomendasi">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-10 text-center text-sm text-[#9E9E9E]">
                                        <svg class="w-8 h-8 text-[#BDBDBD] mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                  d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 9.172V5L8 4z"/>
                                        </svg>
                                        Belum ada obat yang direkomendasikan untuk hama/penyakit ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>
@endsection
