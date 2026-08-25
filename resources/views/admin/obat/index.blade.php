@extends('layouts.admin', ['title' => $title])

@section('content')
    {{-- ── Header Area ── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-[#1A1A1A]">Database Obat Pertanian</h2>
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

    {{-- ── Medicines List Table ── --}}
    <div class="bg-white rounded-2xl border border-[#E0E0E0] card-shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#F8F9FA] border-b border-[#E0E0E0]">
                        <th class="px-6 py-4 text-xs font-semibold text-[#9E9E9E] uppercase tracking-wider w-20">Gambar</th>
                        <th class="px-6 py-4 text-xs font-semibold text-[#9E9E9E] uppercase tracking-wider">Nama Obat</th>
                        <th class="px-6 py-4 text-xs font-semibold text-[#9E9E9E] uppercase tracking-wider">Kategori</th>
                        <th class="px-6 py-4 text-xs font-semibold text-[#9E9E9E] uppercase tracking-wider">Harga</th>
                        <th class="px-6 py-4 text-xs font-semibold text-[#9E9E9E] uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-xs font-semibold text-[#9E9E9E] uppercase tracking-wider text-right w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F0F0F0]">
                    @forelse($obatList as $obat)
                        <tr class="hover:bg-[#F8F9FA]/50 transition-colors group">
                            {{-- Image thumbnail --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="w-12 h-12 rounded-xl bg-[#F0F4F0] border border-[#E0E0E0] overflow-hidden flex items-center justify-center shrink-0">
                                    @if(! empty($obat['gambar']))
                                        <img src="{{ $obat['gambar'] }}" alt="{{ $obat['nama'] }}"
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200"
                                             onerror="this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center text-xs text-[#9E9E9E] font-medium\'>Obat</div>'">
                                    @else
                                        <svg class="w-5 h-5 text-[#9E9E9E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 9.172V5L8 4z"/>
                                        </svg>
                                    @endif
                                </div>
                            </td>

                            {{-- Name & Description --}}
                            <td class="px-6 py-4">
                                <div class="font-bold text-[#1A1A1A] text-sm">{{ $obat['nama'] }}</div>
                                <div class="text-xs text-[#9E9E9E] max-w-sm truncate mt-0.5">{{ $obat['deskripsi'] }}</div>
                            </td>

                            {{-- Category --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-[#424242]">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-primary-50 text-primary-700 border border-primary-100">
                                    {{ $obat['kategori'] }}
                                </span>
                            </td>

                            {{-- Price --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-[#1A1A1A]">
                                Rp {{ number_format($obat['harga'], 0, ',', '.') }}
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

                                    {{-- Delete --}}
                                    <form action="{{ route('admin.obat.destroy', $obat['id']) }}" method="POST"
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus data obat ini?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="w-8 h-8 flex items-center justify-center rounded-lg text-[#616161] hover:text-danger-700 hover:bg-danger-50 transition-colors"
                                                title="Hapus">
                                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
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
@endsection
