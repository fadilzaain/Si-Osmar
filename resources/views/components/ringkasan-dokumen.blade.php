@props(['ringkasan'])

<div class="card-base">
    <div class="card-header">
        <div class="card-title">Kelengkapan Dokumen SDM</div>
    </div>

    <div class="rd-list">
        @foreach ($ringkasan as $r)
            @php
                $total = array_sum($r['breakdown']);
                $pct = fn($n) => $total ? round($n / $total * 100) : 0;
            @endphp
            <a href="{{ route('monitoring-str-sip.index', ['ruangan' => $r['ruangan']]) }}" class="rd-row">
                <div class="rd-row-main">
                    <span class="rd-row-name">{{ $r['ruangan'] }}</span>
                    <div class="rd-bar">
                        <span style="width:{{ $pct($r['breakdown']['berlaku']) }}%;background:var(--color-success)"></span>
                        <span style="width:{{ $pct($r['breakdown']['akan_kadaluarsa']) }}%;background:var(--color-warning)"></span>
                        <span style="width:{{ $pct($r['breakdown']['kadaluarsa']) }}%;background:var(--color-danger)"></span>
                    </div>
                </div>
                <div class="rd-count {{ $r['bermasalah'] > 0 ? 'is-bad' : 'is-ok' }}">
                    {{ $r['bermasalah'] }}<span>/ {{ $r['total_pegawai'] }}</span>
                </div>
            </a>
        @endforeach
    </div>

    <a href="{{ route('monitoring-str-sip.index') }}" class="rd-viewall">Lihat detail semua ruangan →</a>
</div>