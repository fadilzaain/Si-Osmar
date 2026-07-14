@props(['items' => []])

<div class="dxg-stat-grid">
    @foreach ($items as $s)
        <div class="dxg-stat-block">
            <div class="dxg-stat-block-label">{{ $s['label'] }}</div>
            <div class="dxg-stat-block-value tone-{{ $s['tone'] ?? 'neutral' }}">{{ $s['value'] }}</div>
        </div>
    @endforeach
</div>