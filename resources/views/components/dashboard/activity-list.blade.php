@props(['items' => []])

<div class="dxg-activity-list">
    @foreach ($items as $row)
        <div class="dxg-activity-row">
            <div class="dxg-activity-avatar">{{ strtoupper(substr($row['nama'], 0, 1)) }}</div>
            <div class="dxg-activity-info">
                <div class="dxg-activity-name">{{ $row['nama'] }}</div>
                <div class="dxg-activity-detail">
                    {{ $row['dari'] }} <i class="fa-solid fa-arrow-right-long" aria-hidden="true"></i> {{ $row['ke'] }}
                </div>
            </div>
            <div class="dxg-activity-time">{{ $row['waktu'] }}</div>
        </div>
    @endforeach
</div>