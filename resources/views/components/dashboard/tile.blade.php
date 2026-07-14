@props([
    'title',
    'subtitle' => null,
    'icon' => 'fa-solid fa-square',
    'href' => '#',
    'badgeText' => null,
    'badgeTone' => 'neutral', // alert | soon | neutral
    'footerValue' => null,
    'footerLabel' => null,
    'priority' => false,
    'wide' => false,
    'live' => false, // true = card ini datanya real-time, munculin indikator live di link
])

<a href="{{ $href }}" class="dxg-tile {{ $priority ? 'dxg-tile-priority' : '' }} {{ $wide ? 'dxg-tile-wide' : '' }}">
    <div class="dxg-tile-top">
        <div class="dxg-card-icon"><i class="{{ $icon }}"></i></div>
        @if ($badgeText)
            <span class="dxg-badge dxg-badge-{{ $badgeTone }}">{{ $badgeText }}</span>
        @endif
    </div>

    <div class="dxg-tile-title">{{ $title }}</div>
    @if ($subtitle)
        <div class="dxg-card-subtitle">{{ $subtitle }}</div>
    @endif

    <div class="dxg-tile-body">
        {{ $slot }}
    </div>

    <div class="dxg-tile-foot">
        @if ($footerValue)
            <div class="dxg-tile-stat">{{ $footerValue }}<span>{{ $footerLabel }}</span></div>
        @else
            <div></div>
        @endif
        <span class="dxg-card-link">
            @if ($live)
                <span class="dxg-live-pulse" title="Data live" aria-hidden="true"></span>
            @endif
            Lihat detail <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
        </span>
    </div>
</a>