@props(['title' => '', 'subtitle' => null, 'chartId' => 'chart-' . uniqid(), 'height' => 280])

<div {{ $attributes->merge(['class' => 'card-base']) }}>
    <div class="card-header">
        <div>
            <div class="card-title">{{ $title }}</div>
            @if ($subtitle)
                <div class="card-subtitle">{{ $subtitle }}</div>
            @endif
        </div>

        @isset($actions)
            <div class="chart-card-actions">{{ $actions }}</div>
        @endisset
    </div>

    <div class="chart-card-body">
        <div id="{{ $chartId }}" class="chart-card-canvas" style="min-height: {{ $height }}px"></div>
    </div>
</div>