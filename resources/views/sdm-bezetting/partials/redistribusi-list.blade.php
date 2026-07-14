@php $items = $items ?? []; @endphp

<div class="bzs-redis-list">
    @forelse ($items as $r)
        <div class="bzs-redis-row">
            @if ($r['arah'] === 'butuh')
                <i class="fa-solid fa-arrow-down-to-bracket bzs-redis-icon tone-danger" aria-hidden="true"></i>
                <div class="bzs-redis-text">
                    <strong>{{ $r['jabatan'] }}</strong> kurang {{ $r['jumlah'] }}
                    @if ($r['unit_pasangan'])
                        — bisa diambil dari <strong>{{ $r['unit_pasangan'] }}</strong> (lebih {{ $r['jumlah_pasangan'] }})
                    @endif
                </div>
            @else
                <i class="fa-solid fa-arrow-up-from-bracket bzs-redis-icon tone-info" aria-hidden="true"></i>
                <div class="bzs-redis-text">
                    <strong>{{ $r['jabatan'] }}</strong> lebih {{ $r['jumlah'] }}
                    @if ($r['unit_pasangan'])
                        — bisa dipindah ke <strong>{{ $r['unit_pasangan'] }}</strong> (kurang {{ $r['jumlah_pasangan'] }})
                    @endif
                </div>
            @endif
        </div>
    @empty
        <div class="bzs-rotasi-empty">Tidak ada peluang redistribusi untuk unit ini saat ini.</div>
    @endforelse
</div>