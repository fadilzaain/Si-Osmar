@props(['value', 'label', 'tone' => 'neutral'])

<div class="chart-headline">
    <div class="chart-headline-value tone-{{ $tone }}">{{ $value }}</div>
    <div class="chart-headline-label">{{ $label }}</div>
</div>