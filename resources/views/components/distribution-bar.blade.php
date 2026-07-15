@props(['series' => [], 'labels' => [], 'colors' => []])
@php
    $total = array_sum($series) ?: 1;
@endphp

<div class="dist-bar" data-dist-bar>
    @foreach ($series as $i => $value)
        @continue($value <= 0)
        <div class="dist-bar-segment tone-{{ $colors[$i] ?? 'primary' }}"
             data-dist-target="{{ $value / $total }}"
             title="{{ $labels[$i] ?? '' }}: {{ $value }}"></div>
    @endforeach
</div>