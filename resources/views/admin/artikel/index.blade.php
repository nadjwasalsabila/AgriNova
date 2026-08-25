@extends('layouts.admin', ['title' => $title])

@section('content')
    {{-- ── Header Area ── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-[#1A1A1A]">Artikel & Tips Pertanian</h2>
            <p class="text-xs text-[#9E9E9E] mt-0.5">Kelola artikel panduan tani dan tips berkebun untuk pengguna aplikasi mobile.</p>
        </div>
        <a href="{{ route('admin.artikel.create') }}"
           class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-primary-700 hover:bg-primary-600
                  text-white text-sm font-semibold rounded-xl card-shadow transition-colors duration-150 shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tulis Artikel Baru
        </a>
    </div>

    {{-- ── Filter & Search Bar ── --}}
    <div class="bg-white rounded-2xl border border-[#E0E0E0] card-shadow p-4 mb-6">
        <form method="GET" action="{{ route('admin.artikel.index') }}" class="flex flex-col sm:flex-row gap-3">
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
                    placeholder="Cari artikel berdasarkan judul, kategori, atau isi konten..."
                    class="w-full pl-9 pr-4 py-2.5 text-sm text-[#1A1A1A] bg-[#F8F9FA] border border-[#E0E0E0] rounded-xl
                           focus:outline-none focus:ring-2 focus:ring-primary-600/20 focus:border-primary-600 transition-colors"
                >
            </div>
            <div class="flex gap-2">
                @if(! empty($search))
                    <a href="{{ route('admin.artikel.index') }}"
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

    {{-- ── Articles Grid ── --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @forelse($articles as $art)
            @php
                $isPublished = ($art['status'] ?? 'Publikasi') === 'Publikasi';
                $pubDate = isset($art['published_at'])
                    ? \Carbon\Carbon::parse($art['published_at'])->locale('id')->translatedFormat('d M Y, H:i')
                    : '—';
            @endphp
            <div class="bg-white rounded-2xl border border-[#E0E0E0] card-shadow overflow-hidden flex flex-col hover:border-primary-100 hover:shadow-md transition-all duration-200 group">
                {{-- Thumbnail image --}}
                <div class="w-full aspect-[16/9] bg-[#F8F9FA] border-b border-[#E0E0E0] overflow-hidden flex items-center justify-center relative shrink-0">
                    @if(! empty($art['image_url']))
                        <img src="{{ $art['image_url'] }}" alt="{{ $art['title'] }}"
                             class="w-full h-full object-cover group-hover:scale-102 transition-transform duration-300"
                             onerror="this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center text-xs text-[#9E9E9E] font-medium\'>Gambar Artikel</div>'">
                    @else
                        <svg class="w-10 h-10 text-[#BDBDBD]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    @endif

                    {{-- Category badge --}}
                    <span class="absolute top-3 left-3 inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-white/90 backdrop-blur-sm text-primary-700 border border-[#E0E0E0]">
                        {{ $art['category'] ?? 'Umum' }}
                    </span>

                    {{-- Status indicator --}}
                    <span class="absolute top-3 right-3 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold backdrop-blur-sm
                                 {{ $isPublished ? 'bg-[#E8F5E9]/95 text-[#1B5E20]' : 'bg-[#FFF3E0]/95 text-[#E65100]' }}">
                        <span class="w-1 h-1 rounded-full {{ $isPublished ? 'bg-[#2E7D32]' : 'bg-[#FF8F00]' }}"></span>
                        {{ $art['status'] ?? 'Draf' }}
                    </span>
                </div>

                {{-- Content details --}}
                <div class="p-5 flex-1 flex flex-col justify-between">
                    <div>
                        <p class="text-[11px] text-[#9E9E9E] font-medium mb-1.5">{{ $pubDate }}</p>
                        <h3 class="text-sm font-bold text-[#1A1A1A] line-clamp-2 leading-snug group-hover:text-primary-700 transition-colors">
                            {{ $art['title'] }}
                        </h3>
                        <p class="text-xs text-[#616161] line-clamp-3 leading-relaxed mt-2">
                            {{ strip_tags($art['content']) }}
                        </p>
                    </div>

                    {{-- Card Actions --}}
                    <div class="border-t border-[#F0F0F0] mt-4 pt-4 flex items-center justify-between">
                        <span class="text-xs text-[#9E9E9E]">ID: {{ substr($art['id'], 0, 8) }}…</span>
                        <div class="flex items-center gap-1">
                            {{-- Edit --}}
                            <a href="{{ route('admin.artikel.edit', $art['id']) }}"
                               class="w-8 h-8 flex items-center justify-center rounded-lg text-[#616161] hover:text-[#1B5E20] hover:bg-primary-50 transition-colors"
                               title="Edit Artikel">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>

                            {{-- Delete --}}
                            <form action="{{ route('admin.artikel.destroy', $art['id']) }}" method="POST"
                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus artikel ini?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg text-[#616161] hover:text-danger-700 hover:bg-danger-50 transition-colors"
                                        title="Hapus Artikel">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-2xl border border-[#E0E0E0] card-shadow flex flex-col items-center justify-center py-20 px-6 text-center">
                <div class="w-16 h-16 rounded-2xl bg-[#F0F4F0] flex items-center justify-center mb-4 border border-[#E0E0E0]">
                    <svg class="w-8 h-8 text-[#9E9E9E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-[#424242]">Belum ada artikel panduan</p>
                <p class="text-xs text-[#9E9E9E] mt-1 max-w-xs mx-auto">
                    Silakan tulis artikel pertama Anda untuk dibagikan kepada para petani pengguna aplikasi mobile.
                </p>
            </div>
        @endforelse
    </div>
@endsection
