@props(['items' => [], 'moreCount' => null])

<div class="dxg-mini-list">
    @foreach ($items as $row)
        <div class="dxg-mini-list-row">
            <span class="dxg-mini-list-name">{{ $row['nama'] }}</span>
            <span class="dxg-badge dxg-badge-pill tone-{{ $row['tone'] ?? 'neutral' }}">{{ $row['badge'] }}</span>
        </div>
    @endforeach

    @if ($moreCount)
        <div class="dxg-mini-list-more">+{{ $moreCount }} pegawai lainnya</div>
    @endif
</div>