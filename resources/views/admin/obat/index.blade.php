@extends('layouts.admin', ['title' => $title])

@section('content')
    <div x-data="{ showDeleteModal: false, deleteActionUrl: '', deleteObatNama: '' }">
        {{-- ── Header Area ── --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h2 class="text-xl font-bold text-[#1A1A1A]">Daftar Obat Pertanian</h2>
                <p class="text-xs text-[#9E9E9E] mt-0.5">Kelola daftar obat, harga, dan kategori untuk aplikasi mobile.</p>
            </div>
            <a href="{{ route('admin.obat.create') }}"
               class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-primary-700 hover:bg-primary-600
                      text-white text-sm font-semibold rounded-xl card-shadow transition-colors duration-150 shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Obat Baru
            </a>
        </div>

        {{-- ── Filter & Search Bar ── --}}
        <div class="bg-white rounded-2xl border border-[#E0E0E0] card-shadow p-4 mb-6">
            <form method="GET" action="{{ route('admin.obat.index') }}" class="flex flex-col sm:flex-row gap-3">
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
                        placeholder="Cari berdasarkan nama, deskripsi, atau kategori..."
                        class="w-full pl-9 pr-4 py-2.5 text-sm text-[#1A1A1A] bg-[#F8F9FA] border border-[#E0E0E0] rounded-xl
                               focus:outline-none focus:ring-2 focus:ring-primary-600/20 focus:border-primary-600 transition-colors"
                    >
                </div>
                <div class="flex gap-2">
                    @if(! empty($search))
                        <a href="{{ route('admin.obat.index') }}"
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
            <span class="text-xs text-[#9E9E9E]">Menampilkan <strong class="text-[#424242]">{{ count($obatList) }}</strong> data</span>
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
                            <th class="px-6 py-4 text-xs font-semibold text-[#9E9E9E] uppercase tracking-wider">Nama Obat</th>
                            <th class="px-6 py-4 text-xs font-semibold text-[#9E9E9E] uppercase tracking-wider w-36">Kategori</th>
                            <th class="px-6 py-4 text-xs font-semibold text-[#9E9E9E] uppercase tracking-wider w-40">Harga</th>
                            <th class="px-6 py-4 text-xs font-semibold text-[#9E9E9E] uppercase tracking-wider w-44">Penyakit</th>
                            <th class="px-6 py-4 text-xs font-semibold text-[#9E9E9E] uppercase tracking-wider w-36">Status</th>
                            <th class="px-6 py-4 text-xs font-semibold text-[#9E9E9E] uppercase tracking-wider text-right w-24">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F0F0F0]">
                        @forelse($obatList as $obat)
                            <tr class="hover:bg-[#F8F9FA]/50 transition-colors group">
                                {{-- Gambar --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="w-12 h-12 rounded-xl bg-[#F0F4F0] border border-[#E0E0E0] overflow-hidden flex items-center justify-center shrink-0">
                                        @if(! empty($obat['gambar']))
                                            <img src="{{ $obat['gambar'] }}" alt="{{ $obat['nama'] }}"
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
                                    <div class="font-bold text-[#1A1A1A] text-sm">{{ $obat['nama'] }}</div>
                                    <div class="text-xs text-[#9E9E9E] max-w-xs truncate mt-0.5">{{ $obat['deskripsi'] ?? '-' }}</div>
                                </td>

                                {{-- Kategori --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $kat = $obat['kategori'] ?? 'Umum';
                                        $katColor = match($kat) {
                                            'Pestisida'        => 'bg-[#E8F5E9] text-[#1B5E20] border-[#C8E6C9]',
                                            'Fungisida'        => 'bg-blue-50 text-blue-700 border-blue-100',
                                            'Insektisida'      => 'bg-amber-50 text-amber-700 border-amber-100',
                                            'Pupuk Cair'       => 'bg-purple-50 text-purple-700 border-purple-100',
                                            'Pupuk Organik'    => 'bg-teal-50 text-teal-700 border-teal-100',
                                            'Nutrisi Tanaman'  => 'bg-orange-50 text-orange-700 border-orange-100',
                                            default            => 'bg-gray-50 text-gray-600 border-gray-100',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $katColor }}">
                                        {{ $kat }}
                                    </span>
                                </td>

                                {{-- Harga --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-bold text-[#1A1A1A] text-sm">
                                        Rp {{ number_format((float) ($obat['harga'] ?? 0), 0, ',', '.') }}
                                    </div>
                                </td>

                                {{-- Penyakit Count --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if(! empty($obat['penyakit_count']) && $obat['penyakit_count'] > 0)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-primary-50 text-primary-700 border border-primary-100">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            {{ $obat['penyakit_count'] }} penyakit
                                        </span>
                                    @else
                                        <span class="text-sm text-[#BDBDBD]">—</span>
                                    @endif
                                </td>

                                {{-- Status --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if(($obat['status'] ?? 'Aktif') === 'Aktif')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-[#E8F5E9] text-[#1B5E20] border border-[#C8E6C9]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#2E7D32]"></span>
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-[#F5F5F5] text-[#616161] border border-[#E0E0E0]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#9E9E9E]"></span>
                                            Tidak Aktif
                                        </span>
                                    @endif
                                </td>

                                {{-- Action --}}
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-1.5">
                                        {{-- Edit --}}
                                        <a href="{{ route('admin.obat.edit', $obat['id']) }}"
                                           class="w-8 h-8 flex items-center justify-center rounded-lg text-[#616161] hover:text-[#1B5E20] hover:bg-primary-50 transition-colors"
                                           title="Edit">
                                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>

                                        {{-- Delete Button (Trigger Modal) --}}
                                        <button type="button"
                                                @click="deleteActionUrl = '{{ route('admin.obat.destroy', $obat['id']) }}'; deleteObatNama = '{{ $obat['nama'] }}'; showDeleteModal = true"
                                                class="w-8 h-8 flex items-center justify-center rounded-lg text-[#616161] hover:text-danger-700 hover:bg-danger-50 transition-colors"
                                                title="Hapus">
                                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-16 text-center">
                                    <div class="w-14 h-14 rounded-2xl bg-[#F0F4F0] flex items-center justify-center mx-auto mb-4 border border-[#E0E0E0]">
                                        <svg class="w-7 h-7 text-[#9E9E9E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                  d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547"/>
                                        </svg>
                                    </div>
                                    <p class="text-sm font-semibold text-[#424242]">Data obat tidak ditemukan</p>
                                    <p class="text-xs text-[#9E9E9E] mt-1 max-w-xs mx-auto">
                                        Silakan tambah obat baru atau ganti kata kunci pencarian Anda.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── Custom Delete Confirmation Modal ── --}}
        <div x-show="showDeleteModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/55"
             style="display: none;"
             @keydown.escape.window="showDeleteModal = false">
            
            <div x-show="showDeleteModal"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative w-full max-w-md bg-white rounded-3xl shadow-xl overflow-hidden border border-[#E0E0E0]"
                 @click.away="showDeleteModal = false">
                
                <div class="p-6 text-center">
                    {{-- Warning Icon --}}
                    <div class="w-14 h-14 rounded-2xl bg-danger-50 border border-danger-100 flex items-center justify-center mx-auto mb-4 text-danger-700">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>

                    <h3 class="text-base font-bold text-[#1A1A1A] mb-2">Hapus Obat Pertanian?</h3>
                    <p class="text-xs text-[#9E9E9E] leading-relaxed px-2">
                        Apakah Anda yakin ingin menghapus data obat <strong class="text-[#424242]" x-text="deleteObatNama"></strong>? 
                        Tindakan ini juga akan memutuskan hubungan obat ini dengan semua penyakit yang terikat.
                    </p>
                </div>

                {{-- Footer Actions --}}
                <div class="px-6 py-4 bg-[#F8F9FA] border-t border-[#E0E0E0] flex items-center justify-end gap-3">
                    <button type="button"
                            @click="showDeleteModal = false"
                            class="px-4 py-2 border border-[#E0E0E0] hover:bg-white text-[#616161] hover:text-[#1A1A1A] text-xs font-semibold rounded-xl transition-all">
                        Batal
                    </button>
                    
                    <form :action="deleteActionUrl" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="px-5 py-2 bg-danger-600 hover:bg-danger-500 text-white text-xs font-semibold rounded-xl shadow-sm hover:shadow transition-all">
                            Ya, Hapus Obat
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
