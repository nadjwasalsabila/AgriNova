{{--
    Stat Card Component
    Usage:
        @include('components.admin.stat-card', [
            'label'      => 'Total Artikel',
            'value'      => '142',
            'icon'       => 'lightbulb',   // key name for SVG path
            'icon_bg'    => 'bg-primary-50',
            'icon_color' => 'text-primary-700',
            'trend'      => '+12%',        // optional
            'trend_up'   => true,          // optional
        ])
--}}

@php
    $iconPaths = [
        'lightbulb' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>',
        'bug'       => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19c-2.21 0-4-1.79-4-4v-5h8v5c0 2.21-1.79 4-4 4zM8 10V8c0-2.21 1.79-4 4-4s4 1.79 4 4v2M6 10h12M9 19v2M15 19v2M6 13H4M20 13h-2"/>',
        'camera'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>',
        'calendar'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>',
        'users'     => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>',
    ];

    $iconSvg   = $iconPaths[$icon ?? 'lightbulb'] ?? $iconPaths['lightbulb'];
    $iconBg    = $icon_bg ?? 'bg-primary-50';
    $iconColor = $icon_color ?? 'text-primary-700';
    $trendUp   = $trend_up ?? true;
@endphp

<div class="bg-white rounded-2xl border border-[#E0E0E0] p-5 card-shadow
            hover:border-primary-100 hover:card-shadow-primary
            transition-all duration-200 group">

    <div class="flex items-start justify-between">
        {{-- Icon Circle --}}
        <div class="w-11 h-11 rounded-xl {{ $iconBg }} flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {!! $iconSvg !!}
            </svg>
        </div>

        {{-- Trend Badge (optional) --}}
        @isset($trend)
            <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-1 rounded-full
                         {{ $trendUp ? 'bg-primary-50 text-primary-700' : 'bg-danger-50 text-danger-700' }}">
                <svg class="w-3 h-3 {{ $trendUp ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                </svg>
                {{ $trend }}
            </span>
        @endisset
    </div>

    <div class="mt-4">
        <p class="text-2xl font-bold text-[#1A1A1A] leading-tight tracking-tight">
            {{ $value ?? '—' }}
        </p>
        <p class="text-sm text-[#9E9E9E] mt-1 font-medium">
            {{ $label ?? 'Label' }}
        </p>
    </div>
</div>
