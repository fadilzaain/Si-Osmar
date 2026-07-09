<x-app-layout>

<div class="mds-wrap">
    <div class="mds-eyebrow">SI-OSMAR / Monitoring STR & SIP</div>
    <h1 class="mds-title">Kelengkapan legal per pegawai</h1>

    <div class="mds-legend">
        <span class="mds-legend-item"><i class="mds-dot mds-dot--ok"></i>Masih berlaku</span>
        <span class="mds-legend-item"><i class="mds-dot mds-dot--warn"></i>Akan kadaluarsa</span>
        <span class="mds-legend-item"><i class="mds-dot mds-dot--bad"></i>Kadaluarsa</span>
        <span class="mds-legend-item"><i class="mds-dot mds-dot--none"></i>Belum ada data</span>
    </div>

    @foreach ($ringkasan as $r)
        <div class="mds-unit card-base" data-aos="fade-up" data-aos-delay="{{ $loop->index * 60 }}" data-ruangan="{{ $r['ruangan'] }}">
            <div class="mds-unit-head">
                <div class="mds-unit-title">
                    <span class="mds-unit-name">{{ $r['ruangan'] }}</span>
                    <span class="mds-unit-count">{{ $r['total_pegawai'] }} pegawai</span>
                </div>
                <span class="mds-chev">&#9662;</span>
            </div>
            <div class="mds-rows">
                <div class="mds-rows-inner"></div>
            </div>
        </div>
    @endforeach
</div>

@if ($ruanganAktif)
    <script>window.__mdsRuanganAktif = @json($ruanganAktif);</script>
@endif

</x-app-layout>