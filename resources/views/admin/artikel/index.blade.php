@extends('layouts.admin', ['title' => $title])

@section('content')
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-[#1A1A1A]">Artikel &amp; Tips Pertanian</h2>
            <p class="text-xs text-[#9E9E9E] mt-0.5">Kelola artikel panduan tani dan tips berkebun untuk pengguna aplikasi mobile.</p>
        </div>
        <a href="{{ route('admin.artikel.create') }}"
           class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-primary-700 hover:bg-primary-600 text-white text-sm font-semibold rounded-xl card-shadow transition-colors duration-150 shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tulis Artikel Baru
        </a>
    </div>

    {{-- Search --}}
    <div class="bg-white rounded-2xl border border-[#E0E0E0] card-shadow p-4 mb-6">
        <form method="GET" action="{{ route('admin.artikel.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-[#9E9E9E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" name="search" value="{{ $search ?? '' }}"
                    placeholder="Cari artikel berdasarkan judul, kategori, atau isi konten..."
                    class="w-full pl-9 pr-4 py-2.5 text-sm text-[#1A1A1A] bg-[#F8F9FA] border border-[#E0E0E0] rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-600/20 focus:border-primary-600 transition-colors">
            </div>
            <div class="flex gap-2">
                @if(! empty($search))
                    <a href="{{ route('admin.artikel.index') }}" class="px-4 py-2.5 border border-[#E0E0E0] hover:bg-[#F8F9FA] text-[#616161] text-sm font-medium rounded-xl transition-colors flex items-center justify-center">Reset</a>
                @endif
                <button type="submit" class="px-5 py-2.5 bg-[#E8F5E9] hover:bg-[#C8E6C9] text-primary-700 text-sm font-bold rounded-xl transition-colors">Cari</button>
            </div>
        </form>
    </div>

    {{-- Articles Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @forelse($articles as $art)
            @php
                $isPublished = ($art['status'] ?? 'Publikasi') === 'Publikasi';
                $pubDate = isset($art['published_at'])
                    ? \Carbon\Carbon::parse($art['published_at'])->setTimezone('Asia/Jakarta')->locale('id')->translatedFormat('d M Y, H:i') . ' WIB'
                    : '—';
                $modalData = [
                    'id'           => $art['id'] ?? '',
                    'title'        => $art['title'] ?? '',
                    'category'     => $art['category'] ?? 'Umum',
                    'status'       => $art['status'] ?? 'Draf',
                    'published_at' => $pubDate,
                    'image_url'    => $art['image_url'] ?? '',
                    'content'      => $art['content'] ?? '',
                    'source_label' => $art['source_label'] ?? '',
                    'source_url'   => $art['source_url'] ?? '',
                ];
            @endphp

            {{-- Data artikel disimpan di hidden script tag, tidak ada script inline di loop --}}
            <script id="art-json-{{ $art['id'] }}" type="application/json">
                {!! json_encode($modalData, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}
            </script>

            <div class="bg-white rounded-2xl border border-[#E0E0E0] card-shadow overflow-hidden flex flex-col hover:border-primary-100 hover:shadow-md transition-all duration-200 group">
                {{-- Thumbnail --}}
                <div class="w-full aspect-[16/9] bg-[#F0F4F0] border-b border-[#E0E0E0] overflow-hidden flex items-center justify-center relative shrink-0">
                    @if(! empty($art['image_url']))
                        <img src="{{ $art['image_url'] }}" alt="{{ $art['title'] }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                             loading="lazy"
                             onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1500937386664-56d1dfef3854?w=800';">
                    @else
                        <div class="flex flex-col items-center justify-center text-[#A5D6A7]">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                            <span class="text-[10px] font-semibold text-[#81C784] mt-1">Petani Maju</span>
                        </div>
                    @endif
                    <span class="absolute top-3 left-3 inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-white/90 backdrop-blur-sm text-primary-700 border border-[#E0E0E0]">
                        {{ $art['category'] ?? 'Umum' }}
                    </span>
                    <span class="absolute top-3 right-3 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold backdrop-blur-sm {{ $isPublished ? 'bg-[#E8F5E9]/95 text-[#1B5E20]' : 'bg-[#FFF3E0]/95 text-[#E65100]' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $isPublished ? 'bg-[#2E7D32]' : 'bg-[#FF8F00]' }}"></span>
                        {{ $art['status'] ?? 'Draf' }}
                    </span>
                </div>

                {{-- Content --}}
                <div class="p-5 flex-1 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-1.5 flex-wrap">
                            <p class="text-[11px] text-[#9E9E9E] font-medium">{{ $pubDate }}</p>
                            @if(! empty($art['source_label']))
                                <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-primary-700 bg-primary-50 px-2 py-0.5 rounded-md border border-primary-100/50 truncate max-w-[150px]" title="{{ $art['source_label'] }}">
                                    🌐 {{ $art['source_label'] }}
                                </span>
                            @endif
                        </div>
                        <h3 class="text-sm font-bold text-[#1A1A1A] line-clamp-2 leading-snug group-hover:text-primary-700 transition-colors">{{ $art['title'] }}</h3>
                        <p class="text-xs text-[#616161] line-clamp-3 leading-relaxed mt-2">{{ Str::limit(trim(preg_replace('/#+\s*/', '', strip_tags($art['content']))), 120) }}</p>
                    </div>

                    {{-- Actions --}}
                    <div class="border-t border-[#F0F0F0] mt-4 pt-4 flex items-center justify-between">
                        <span class="text-xs text-[#9E9E9E]">ID: {{ substr($art['id'], 0, 8) }}…</span>
                        <div class="flex items-center gap-1">
                            {{-- Baca --}}
                            <button type="button"
                                    onclick="openArtikelModal('{{ $art['id'] }}')"
                                    class="w-8 h-8 flex items-center justify-center rounded-lg text-[#616161] hover:text-blue-600 hover:bg-blue-50 transition-colors"
                                    title="Baca Artikel">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>

                            {{-- Edit --}}
                            <a href="{{ route('admin.artikel.edit', $art['id']) }}"
                               class="w-8 h-8 flex items-center justify-center rounded-lg text-[#616161] hover:text-[#1B5E20] hover:bg-primary-50 transition-colors"
                               title="Edit Artikel">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>

                            {{-- Delete --}}
                            <form action="{{ route('admin.artikel.destroy', $art['id']) }}" method="POST"
                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus artikel ini?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg text-[#616161] hover:text-red-600 hover:bg-red-50 transition-colors"
                                        title="Hapus Artikel">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-[#424242]">Belum ada artikel panduan</p>
                <p class="text-xs text-[#9E9E9E] mt-1 max-w-xs mx-auto">Silakan tulis artikel pertama atau jalankan scraper untuk mengambil konten pertanian terbaru.</p>
            </div>
        @endforelse
    </div>

    {{-- Modal Baca Artikel — diletakkan di luar grid, langsung di akhir body lewat @push --}}
@endsection

@push('scripts')
{{-- Modal HTML diletakkan di sini agar berada di level body, bebas dari stacking context apapun --}}
<div id="artikelModal"
     class="fixed inset-0 z-[9999] hidden"
     role="dialog"
     aria-modal="true"
     aria-labelledby="modalArtikelTitle">

    {{-- Backdrop --}}
    <div id="modalBackdrop"
         class="absolute inset-0 bg-black/60 backdrop-blur-sm"
         onclick="closeArtikelModal()"></div>

    {{-- Dialog Panel --}}
    <div class="absolute inset-0 flex items-center justify-center p-4 sm:p-6 pointer-events-none">
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl flex flex-col border border-[#E0E0E0] pointer-events-auto"
             style="max-height: calc(100vh - 3rem); height: auto;">

            {{-- Header --}}
            <div class="flex items-start justify-between gap-4 p-5 sm:p-6 border-b border-[#F0F0F0] bg-white rounded-t-2xl shrink-0">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap mb-2">
                        <span id="modalCategory"
                              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-[#E8F5E9] text-primary-700 border border-[#C8E6C9]"></span>
                        <span id="modalStatus"
                              class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold"></span>
                    </div>
                    <h2 id="modalArtikelTitle"
                        class="text-base sm:text-lg font-bold text-[#1A1A1A] leading-snug"></h2>
                    <div class="flex items-center gap-3 mt-2 flex-wrap">
                        <p id="modalDate" class="text-[11px] text-[#9E9E9E] font-medium"></p>
                        <div id="modalSourceWrap"
                             class="hidden items-center gap-1 text-[11px] text-primary-700 bg-primary-50 px-2.5 py-1 rounded-lg border border-primary-100/80">
                            <span class="text-[#757575]">Sumber:</span>
                            <a id="modalSourceLink"
                               href="#"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="font-semibold underline hover:text-primary-800"></a>
                            <span id="modalSourceText" class="font-semibold hidden"></span>
                        </div>
                    </div>
                </div>
                <button onclick="closeArtikelModal()"
                        type="button"
                        class="shrink-0 w-9 h-9 flex items-center justify-center rounded-xl text-[#9E9E9E] hover:text-[#1A1A1A] hover:bg-[#F5F5F5] transition-colors"
                        title="Tutup">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Body — scrollable HANYA di dalam panel ini --}}
            <div id="modalBody"
                 class="flex-1 overflow-y-auto overscroll-contain p-5 sm:p-6"
                 style="min-height: 0; max-height: 60vh; -webkit-overflow-scrolling: touch;">

                <div id="modalImageWrap"
                     class="hidden w-full rounded-xl overflow-hidden mb-6 bg-[#F8F9FA] border border-[#E0E0E0]"
                     style="aspect-ratio: 16/9; max-height: 280px;">
                    <img id="modalImage"
                         src=""
                         alt=""
                         class="w-full h-full object-cover"
                         onerror="document.getElementById('modalImageWrap').classList.add('hidden')">
                </div>

                <div id="modalContent"
                     class="artikel-content text-[#333333] text-sm leading-relaxed"></div>
            </div>

            {{-- Footer --}}
            <div class="shrink-0 flex items-center justify-between gap-3 px-5 sm:px-6 py-4 border-t border-[#F0F0F0] bg-[#FAFAFA] rounded-b-2xl">
                <span id="modalId" class="text-[11px] text-[#9E9E9E] font-mono"></span>
                <div class="flex gap-2">
                    <a id="modalEditLink"
                       href="#"
                       class="px-4 py-2 bg-[#E8F5E9] hover:bg-[#C8E6C9] text-primary-700 text-xs sm:text-sm font-semibold rounded-xl transition-colors inline-flex items-center gap-1.5">
                        ✏️ Edit Artikel
                    </a>
                    <button onclick="closeArtikelModal()"
                            type="button"
                            class="px-4 py-2 border border-[#E0E0E0] hover:bg-[#F5F5F5] text-[#616161] text-xs sm:text-sm font-medium rounded-xl transition-colors">
                        Tutup
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
/* Scrollbar modal */
#modalBody::-webkit-scrollbar        { width: 6px; }
#modalBody::-webkit-scrollbar-track  { background: #f1f5f1; border-radius: 4px; }
#modalBody::-webkit-scrollbar-thumb  { background: #a5d6a7; border-radius: 4px; }
#modalBody::-webkit-scrollbar-thumb:hover { background: #66bb6a; }

/* Konten markdown artikel */
.artikel-content h2,
.artikel-content h3          { font-weight: 700; color: #1B5E20; margin-top: 1.25rem; margin-bottom: 0.5rem; }
.artikel-content h2          { font-size: 1.05rem; border-bottom: 2px solid #E8F5E9; padding-bottom: 0.4rem; }
.artikel-content h3          { font-size: 0.95rem; color: #2E7D32; }
.artikel-content p           { margin: 0.6rem 0; line-height: 1.75; color: #374151; }
.artikel-content ul,
.artikel-content ol          { margin: 0.6rem 0 0.6rem 1.5rem; }
.artikel-content ul          { list-style-type: disc; }
.artikel-content ol          { list-style-type: decimal; }
.artikel-content li          { margin: 0.35rem 0; line-height: 1.65; }
.artikel-content strong      { font-weight: 700; color: #111827; }
.artikel-content em          { font-style: italic; color: #4B5563; }
.artikel-content code        { background: #F0F4F0; color: #2E7D32; border-radius: 4px; padding: 0.15rem 0.4rem; font-size: 0.83em; font-family: monospace; }
.artikel-content blockquote  { border-left: 4px solid #4CAF50; color: #4B5563; font-style: italic; margin: 0.75rem 0; background: #F4FBF5; border-radius: 0 8px 8px 0; padding: 0.75rem 1rem; }
.artikel-content hr          { border: none; border-top: 2px solid #E8F5E9; margin: 1.25rem 0; }
.artikel-content a           { color: #2E7D32; font-weight: 600; text-decoration: underline; }
.artikel-content a:hover     { color: #1B5E20; }
</style>

<script>
// ── Markdown renderer ringan (tidak butuh library eksternal) ──
function renderMarkdown(raw) {
    if (!raw) return '';

    // Escape HTML dulu agar tidak ada XSS
    const esc = raw
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');

    const lines = esc.split('\n');
    let html = '';
    let inUl = false;
    let inOl = false;

    const closeList = () => {
        if (inUl) { html += '</ul>'; inUl = false; }
        if (inOl) { html += '</ol>'; inOl = false; }
    };

    const fmt = (t) => t
        // Link markdown [text](url)
        .replace(/\[([^\]]+)\]\(([^)]+)\)/g,
            '<a href="$2" target="_blank" rel="noopener">$1 ↗</a>')
        // Bold + italic
        .replace(/\*\*\*(.*?)\*\*\*/g, '<strong><em>$1</em></strong>')
        // Bold
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        // Italic
        .replace(/\*(.*?)\*/g, '<em>$1</em>')
        // Inline code
        .replace(/`([^`]+)`/g, '<code>$1</code>');

    for (let i = 0; i < lines.length; i++) {
        const line = lines[i];
        const trim = line.trim();

        // H3
        if (trim.startsWith('### ')) {
            closeList();
            html += `<h3>${fmt(trim.slice(4))}</h3>`;
            continue;
        }
        // H2
        if (trim.startsWith('## ')) {
            closeList();
            html += `<h2>${fmt(trim.slice(3))}</h2>`;
            continue;
        }
        // H1 → render sebagai H2 agar tidak terlalu besar di modal
        if (trim.startsWith('# ')) {
            closeList();
            html += `<h2>${fmt(trim.slice(2))}</h2>`;
            continue;
        }
        // HR
        if (/^[-*]{3,}$/.test(trim)) {
            closeList();
            html += '<hr>';
            continue;
        }
        // Bullet list
        if (/^[-*\u2022]\s/.test(trim)) {
            if (inOl) { html += '</ol>'; inOl = false; }
            if (!inUl) { html += '<ul>'; inUl = true; }
            html += `<li>${fmt(trim.replace(/^[-*\u2022]\s/, ''))}</li>`;
            continue;
        }
        // Numbered list
        if (/^\d+\.\s/.test(trim)) {
            if (inUl) { html += '</ul>'; inUl = false; }
            if (!inOl) { html += '<ol>'; inOl = true; }
            html += `<li>${fmt(trim.replace(/^\d+\.\s/, ''))}</li>`;
            continue;
        }
        // Baris kosong
        if (trim === '') {
            closeList();
            continue;
        }
        // Paragraph biasa
        closeList();
        html += `<p>${fmt(trim)}</p>`;
    }

    closeList();
    return html;
}

// ── Buka modal ──
function openArtikelModal(artId) {
    const el = document.getElementById('art-json-' + artId);
    if (!el) return;

    let data;
    try {
        data = JSON.parse(el.textContent);
    } catch (e) {
        console.error('openArtikelModal: gagal parse JSON', e);
        return;
    }

    // Isi konten modal
    document.getElementById('modalArtikelTitle').textContent = data.title || '—';
    document.getElementById('modalCategory').textContent     = data.category || 'Umum';
    document.getElementById('modalDate').textContent         = '📅 ' + (data.published_at || '—');
    document.getElementById('modalId').textContent           = 'ID: ' + (data.id || '');
    document.getElementById('modalEditLink').href            = '/admin/artikel/' + data.id + '/edit';

    // Badge status
    const stEl  = document.getElementById('modalStatus');
    const isPub = (data.status === 'Publikasi');
    stEl.textContent = data.status || 'Draf';
    stEl.className   = 'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold '
        + (isPub ? 'bg-[#E8F5E9] text-[#1B5E20]' : 'bg-[#FFF3E0] text-[#E65100]');

    // Sumber artikel
    const srcWrap = document.getElementById('modalSourceWrap');
    const srcLink = document.getElementById('modalSourceLink');
    const srcText = document.getElementById('modalSourceText');

    srcWrap.classList.add('hidden');
    srcWrap.classList.remove('inline-flex');
    srcLink.classList.add('hidden');
    srcText.classList.add('hidden');

    if (data.source_url) {
        srcLink.href        = data.source_url;
        srcLink.textContent = (data.source_label || data.source_url) + ' ↗';
        srcLink.classList.remove('hidden');
        srcWrap.classList.remove('hidden');
        srcWrap.classList.add('inline-flex');
    } else if (data.source_label) {
        srcText.textContent = data.source_label;
        srcText.classList.remove('hidden');
        srcWrap.classList.remove('hidden');
        srcWrap.classList.add('inline-flex');
    }

    // Gambar
    const imgWrap = document.getElementById('modalImageWrap');
    const imgEl   = document.getElementById('modalImage');
    if (data.image_url) {
        imgEl.src  = data.image_url;
        imgEl.alt  = data.title || '';
        imgWrap.classList.remove('hidden');
    } else {
        imgWrap.classList.add('hidden');
    }

    // Render markdown konten
    document.getElementById('modalContent').innerHTML = renderMarkdown(data.content || '');

    // Reset scroll ke atas
    const body = document.getElementById('modalBody');
    if (body) body.scrollTop = 0;

    // Tampilkan modal
    const modal = document.getElementById('artikelModal');
    modal.classList.remove('hidden');

    // Kunci scroll halaman utama saja, bukan modal body
    document.documentElement.style.overflow = 'hidden';
    document.body.style.overflow             = 'hidden';
}

// ── Tutup modal ──
function closeArtikelModal() {
    const modal = document.getElementById('artikelModal');
    if (!modal) return;

    modal.classList.add('hidden');

    // Kembalikan scroll halaman
    document.documentElement.style.overflow = '';
    document.body.style.overflow             = '';
}

// Tutup dengan Escape
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeArtikelModal();
});
</script>
@endpush