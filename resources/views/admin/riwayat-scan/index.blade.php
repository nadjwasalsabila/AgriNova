@extends('layouts.admin', ['title' => $title])

@section('content')
    {{-- ── Header ── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-[#1A1A1A]">Riwayat Scan AI</h2>
            <p class="text-xs text-[#9E9E9E] mt-0.5">Riwayat deteksi penyakit tanaman oleh pengguna melalui fitur Scan AI.</p>
        </div>
        {{-- Stats badge --}}
        <div class="flex items-center gap-2 shrink-0">
            <div class="bg-white border border-[#E0E0E0] rounded-xl px-4 py-2 flex items-center gap-2 card-shadow">
                <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
                <span class="text-xs font-semibold text-[#616161]">Total:</span>
                <span class="text-sm font-bold text-[#1A1A1A]">{{ number_format($totalCount) }} scan</span>
            </div>
        </div>
    </div>

    {{-- ── Filter & Search Bar ── --}}
    <div class="bg-white rounded-2xl border border-[#E0E0E0] card-shadow p-4 mb-6">
        <form method="GET" action="{{ route('admin.riwayat-scan.index') }}" class="flex flex-col sm:flex-row gap-3">

            {{-- Search --}}
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-[#9E9E9E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" name="search" value="{{ $search ?? '' }}"
                       placeholder="Cari berdasarkan nama penyakit atau jenis tanaman..."
                       class="w-full pl-9 pr-4 py-2.5 text-sm text-[#1A1A1A] bg-[#F8F9FA] border border-[#E0E0E0] rounded-xl
                              focus:outline-none focus:ring-2 focus:ring-primary-600/20 focus:border-primary-600 transition-colors">
            </div>

            {{-- Plant Type Filter --}}
            <div class="sm:w-52">
                <select name="filter"
                        class="w-full px-3 py-2.5 text-sm text-[#424242] bg-[#F8F9FA] border border-[#E0E0E0] rounded-xl
                               focus:outline-none focus:ring-2 focus:ring-primary-600/20 focus:border-primary-600 transition-colors">
                    <option value="">Semua Tanaman</option>
                    @foreach($plantTypes as $pt)
                        <option value="{{ $pt }}" {{ ($filter ?? '') === $pt ? 'selected' : '' }}>
                            {{ $pt }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Buttons --}}
            <div class="flex gap-2">
                @if(! empty($search) || ! empty($filter))
                    <a href="{{ route('admin.riwayat-scan.index') }}"
                       class="px-4 py-2.5 border border-[#E0E0E0] hover:bg-[#F8F9FA] text-[#616161]
                              text-sm font-medium rounded-xl transition-colors flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
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

    {{-- ── Result count ── --}}
    <div class="flex items-center justify-between mb-3 px-1">
        <p class="text-xs text-[#9E9E9E]">
            Menampilkan <strong class="text-[#424242]">{{ count($records) }}</strong> data
            @if(! empty($search)) untuk "<em>{{ $search }}</em>" @endif
            @if(! empty($filter)) pada tanaman <strong class="text-primary-700">{{ $filter }}</strong> @endif
        </p>
        @if($totalPages > 1)
            <p class="text-xs text-[#9E9E9E]">Halaman {{ $page }} / {{ $totalPages }}</p>
        @endif
    </div>

    {{-- ── Table ── --}}
    <div class="bg-white rounded-2xl border border-[#E0E0E0] card-shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#F8F9FA] border-b border-[#E0E0E0]">
                        <th class="px-5 py-4 text-xs font-semibold text-[#9E9E9E] uppercase tracking-wider w-20">Foto</th>
                        <th class="px-5 py-4 text-xs font-semibold text-[#9E9E9E] uppercase tracking-wider">Penyakit Terdeteksi</th>
                        <th class="px-5 py-4 text-xs font-semibold text-[#9E9E9E] uppercase tracking-wider">Jenis Tanaman</th>
                        <th class="px-5 py-4 text-xs font-semibold text-[#9E9E9E] uppercase tracking-wider text-center">Akurasi</th>
                        <th class="px-5 py-4 text-xs font-semibold text-[#9E9E9E] uppercase tracking-wider text-center">Status</th>
                        <th class="px-5 py-4 text-xs font-semibold text-[#9E9E9E] uppercase tracking-wider">Waktu Scan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F0F0F0]">
                    @forelse($records as $rec)
                        @php
                            $confidence = isset($rec['confidence']) ? round($rec['confidence'] * 100, 1) : null;
                            $confColor  = match(true) {
                                $confidence >= 85 => ['bar' => 'bg-green-500',  'text' => 'text-green-700',  'bg' => 'bg-green-50'],
                                $confidence >= 60 => ['bar' => 'bg-yellow-500', 'text' => 'text-yellow-700', 'bg' => 'bg-yellow-50'],
                                default           => ['bar' => 'bg-red-400',    'text' => 'text-red-700',    'bg' => 'bg-red-50'],
                            };
                            $status      = $rec['status'] ?? 'Unknown';
                            $statusStyle = match($status) {
                                'Success' => 'bg-green-50 text-green-700 border-green-100',
                                'Failed'  => 'bg-red-50 text-red-700 border-red-100',
                                default   => 'bg-gray-50 text-gray-600 border-gray-100',
                            };
                            $statusIcon = match($status) {
                                'Success' => '✓',
                                'Failed'  => '✗',
                                default   => '?',
                            };

                            // Parse plant type – strip parentheses for badge display
                            $plantRaw  = $rec['plant_type'] ?? '-';
                            $plantShort = preg_replace('/\s*\(.*?\)/', '', $plantRaw);

                            $plantColor = match(true) {
                                str_contains(strtolower($plantRaw), 'padi')  => 'bg-yellow-50 text-yellow-700 border-yellow-100',
                                str_contains(strtolower($plantRaw), 'teh')   => 'bg-green-50 text-green-700 border-green-100',
                                str_contains(strtolower($plantRaw), 'tomat') => 'bg-red-50 text-red-700 border-red-100',
                                str_contains(strtolower($plantRaw), 'jeruk') => 'bg-orange-50 text-orange-700 border-orange-100',
                                str_contains(strtolower($plantRaw), 'jagung')=> 'bg-amber-50 text-amber-700 border-amber-100',
                                default => 'bg-blue-50 text-blue-700 border-blue-100',
                            };

                            $createdAt = $rec['created_at'] ?? null;
                            $dateFormatted = $createdAt
                                ? \Carbon\Carbon::parse($createdAt)->timezone('Asia/Jakarta')->format('d M Y, H:i')
                                : '-';
                            $timeAgo = $createdAt
                                ? \Carbon\Carbon::parse($createdAt)->diffForHumans()
                                : '';
                        @endphp
                        <tr class="hover:bg-[#F8F9FA]/60 transition-colors group">

                            {{-- Foto --}}
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <div class="w-14 h-14 rounded-xl bg-[#F0F4F0] border border-[#E0E0E0] overflow-hidden flex items-center justify-center shrink-0">
                                    @if(! empty($rec['image_url']))
                                        <img src="{{ $rec['image_url'] }}" alt="scan"
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200"
                                             onerror="this.style.display='none';this.parentElement.innerHTML='<span class=\'text-[10px] text-[#9E9E9E] p-1 text-center\'>No img</span>'">
                                    @else
                                        <svg class="w-5 h-5 text-[#BDBDBD]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    @endif
                                </div>
                            </td>

                            {{-- Penyakit --}}
                            <td class="px-5 py-3.5">
                                <div class="font-semibold text-[#1A1A1A] text-sm leading-snug max-w-xs">
                                    {{ $rec['disease'] ?? '-' }}
                                </div>
                                <div class="text-[10px] text-[#BDBDBD] mt-0.5 font-mono">
                                    ID: {{ substr($rec['id'] ?? '—', 0, 8) }}…
                                </div>
                            </td>

                            {{-- Jenis Tanaman --}}
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $plantColor }}">
                                    {{ $plantShort }}
                                </span>
                            </td>

                            {{-- Akurasi --}}
                            <td class="px-5 py-3.5 text-center">
                                @if($confidence !== null)
                                    <div class="flex flex-col items-center gap-1">
                                        <span class="text-sm font-bold {{ $confColor['text'] }}">{{ $confidence }}%</span>
                                        {{-- progress bar --}}
                                        <div class="w-16 h-1.5 bg-[#E0E0E0] rounded-full overflow-hidden">
                                            <div class="{{ $confColor['bar'] }} h-full rounded-full transition-all"
                                                 style="width: {{ min($confidence, 100) }}%"></div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-xs text-[#9E9E9E]">—</span>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold border {{ $statusStyle }}">
                                    {{ $statusIcon }} {{ $status }}
                                </span>
                            </td>

                            {{-- Waktu Scan --}}
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <div class="text-sm text-[#424242] font-medium">{{ $dateFormatted }}</div>
                                <div class="text-xs text-[#9E9E9E] mt-0.5">{{ $timeAgo }}</div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-14 text-center text-sm text-[#9E9E9E]">
                                <svg class="w-12 h-12 text-[#BDBDBD] mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <p class="font-medium">Belum ada riwayat scan.</p>
                                @if(! empty($search) || ! empty($filter))
                                    <p class="mt-1 text-xs">Coba ubah atau reset filter pencarian.</p>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ── Pagination ── --}}
        @if($totalPages > 1)
            <div class="flex items-center justify-between px-6 py-4 border-t border-[#F0F0F0] bg-[#FAFAFA]">
                <p class="text-xs text-[#9E9E9E]">
                    Halaman <strong>{{ $page }}</strong> dari <strong>{{ $totalPages }}</strong>
                </p>
                <div class="flex gap-1.5">
                    {{-- Prev --}}
                    @if($page > 1)
                        <a href="{{ route('admin.riwayat-scan.index', array_filter(['page' => $page - 1, 'search' => $search, 'filter' => $filter])) }}"
                           class="px-3 py-1.5 text-xs font-semibold border border-[#E0E0E0] rounded-lg hover:bg-[#F0F4F0] text-[#424242] transition-colors">
                            ← Sebelumnya
                        </a>
                    @endif

                    {{-- Page numbers (max 5 visible) --}}
                    @php
                        $start = max(1, $page - 2);
                        $end   = min($totalPages, $start + 4);
                    @endphp
                    @for($i = $start; $i <= $end; $i++)
                        <a href="{{ route('admin.riwayat-scan.index', array_filter(['page' => $i, 'search' => $search, 'filter' => $filter])) }}"
                           class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors
                                  {{ $i === $page
                                     ? 'bg-primary-600 text-white'
                                     : 'border border-[#E0E0E0] hover:bg-[#F0F4F0] text-[#424242]' }}">
                            {{ $i }}
                        </a>
                    @endfor

                    {{-- Next --}}
                    @if($page < $totalPages)
                        <a href="{{ route('admin.riwayat-scan.index', array_filter(['page' => $page + 1, 'search' => $search, 'filter' => $filter])) }}"
                           class="px-3 py-1.5 text-xs font-semibold border border-[#E0E0E0] rounded-lg hover:bg-[#F0F4F0] text-[#424242] transition-colors">
                            Berikutnya →
                        </a>
                    @endif
                </div>
            </div>
        @endif
    </div>
@endsection
