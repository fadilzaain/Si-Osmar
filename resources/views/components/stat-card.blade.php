@props([
    'icon' => 'fa-solid fa-chart-simple',
    'label' => '',
    'value' => '',
    'trend' => null,
    'trendType' => 'up',
    'comparison' => null,
    'color' => null,
    'sparkline' => null,
])

<div {{ $attributes->merge(['class' => 'card-base stat-card']) }} @if($color) style="--stat-color: {{ $color }}" @endif>
    <div class="stat-card-top">
        <div class="stat-card-icon"><i class="{{ $icon }}"></i></div>
        @if ($trend)
            <span class="stat-card-trend {{ $trendType }}">
                <i class="fa-solid fa-arrow-{{ $trendType === 'up' ? 'up' : 'down' }}"></i>
                {{ $trend }}
            </span>
        @endif
    </div>

    <div>
        <div class="stat-card-label">{{ $label }}</div>
        <div class="stat-card-value">{{ $value }}</div>
        @if ($comparison)
            <div class="stat-card-label">{{ $comparison }}</div>
        @endif
    </div>

    @if ($sparkline)
        <svg class="stat-card-sparkline" viewBox="0 0 100 30" preserveAspectRatio="none">
            <polyline
                fill="none"
                stroke="var(--stat-color, var(--color-primary))"
                stroke-width="2"
                points="{{ $sparkline }}"
            />
        </svg>
    @endif
</div>