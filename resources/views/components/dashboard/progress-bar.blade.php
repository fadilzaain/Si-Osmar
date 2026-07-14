@props(['items' => []])

<div class="dxg-progress-list">
    @foreach ($items as $i => $p)
        <div class="dxg-progress-row" style="--delay: {{ $i * 0.08 }}s">
            <div class="dxg-progress-head">
                <span class="dxg-progress-label">{{ $p['label'] }}</span>
                <span class="dxg-progress-value tone-{{ $p['tone'] ?? 'neutral' }}">{{ $p['value'] }}</span>
            </div>
            <div class="dxg-progress-track">
                <div class="dxg-progress-fill tone-{{ $p['tone'] ?? 'neutral' }}" style="--target: {{ $p['percent'] }}%"></div>
            </div>
        </div>
    @endforeach
</div>