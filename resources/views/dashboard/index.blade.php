<x-app-layout title="Dashboard">

<div class="dxg-page" data-aos="fade-up">

    <div class="dxg-eyebrow">SI-OSMAR</div>
    @php
    $jam = now()->hour;
    $sapaan = match(true) {
        $jam >= 4 && $jam < 11 => 'Selamat pagi',
        $jam >= 11 && $jam < 15 => 'Selamat siang',
        $jam >= 15 && $jam < 18 => 'Selamat sore',
        default => 'Selamat malam',
        };
    @endphp
    <h1 class="dxg-title">{{ $sapaan }}</h1>
    <div class="dxg-sub">Ringkasan strategis lintas modul SDM — {{ now()->translatedFormat('d F Y') }}</div>

    <div class="dxg-grid">

        {{-- Ekinerja --}}
        <a href="{{ route('coming-soon', 'ekinerja') }}" class="dxg-card">
            <div class="dxg-card-glow"></div>
            <div class="dxg-card-top">
                <div class="dxg-card-icon"><i class="fa-solid fa-chart-line"></i></div>
                <span class="dxg-card-badge">{{ now()->year }}</span>
            </div>
            <div class="dxg-card-title">Ekinerja</div>
            <div class="dxg-card-subtitle">Distribusi capaian kinerja pegawai</div>
            <div class="dxg-chart-area">
                @php
                    $w = 220; $h = 90;
                    $toPoints = function(array $vals) use ($w, $h) {
                        $n = count($vals) - 1;
                        return collect($vals)->map(fn($v, $i) => round($i * $w / $n) . ',' . round($h - ($v / 100 * $h)))->implode(' ');
                    };
                @endphp
                <svg viewBox="0 0 {{ $w }} {{ $h }}" width="100%" height="100%">
                    <polyline class="dxg-line" points="{{ $toPoints($ekinerja['series_baik']) }}" fill="none" stroke="var(--color-success)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <polyline class="dxg-line dxg-line-delay" points="{{ $toPoints($ekinerja['series_kurang']) }}" fill="none" stroke="#4C8DFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" opacity="0.6"/>
                </svg>
            </div>
            <div class="dxg-card-foot">
                <div class="dxg-card-stat">{{ $ekinerja['persen_baik'] }}<span>% baik/sangat baik</span></div>
                <span class="dxg-card-link">Lihat detail →</span>
            </div>
        </a>

        {{-- Pelatihan --}}
        <a href="{{ route('coming-soon', 'pelatihan') }}" class="dxg-card">
            <div class="dxg-card-glow"></div>
            <div class="dxg-card-top">
                <div class="dxg-card-icon"><i class="fa-solid fa-graduation-cap"></i></div>
                <span class="dxg-card-badge">YTD</span>
            </div>
            <div class="dxg-card-title">Pelatihan</div>
            <div class="dxg-card-subtitle">Jam pelatihan per pegawai</div>
            @php
                $circ = 213.6;
                $seg1 = round($pelatihan['lebih_20jp'] / 100 * $circ, 1);
                $seg2 = round($pelatihan['kurang_20jp'] / 100 * $circ, 1);
                $rot2 = round($pelatihan['lebih_20jp'] / 100 * 360) - 90;
            @endphp
            <div class="dxg-chart-area dxg-chart-center">
                <svg viewBox="0 0 90 90" width="90" height="90" class="dxg-donut">
                    <circle cx="45" cy="45" r="34" fill="none" stroke="var(--color-border)" stroke-width="11"/>
                    <circle cx="45" cy="45" r="34" fill="none" stroke="var(--color-success)" stroke-width="11" stroke-linecap="round" transform="rotate(-90 45 45)" stroke-dasharray="{{ $seg1 }} {{ $circ }}"/>
                    <circle cx="45" cy="45" r="34" fill="none" stroke="var(--color-warning)" stroke-width="11" stroke-linecap="round" transform="rotate({{ $rot2 }} 45 45)" stroke-dasharray="{{ $seg2 }} {{ $circ }}"/>
                    <text x="45" y="50" text-anchor="middle" class="dxg-donut-label">{{ $pelatihan['lebih_20jp'] }}%</text>
                </svg>
            </div>
            <div class="dxg-legend">
                <span><i style="background:var(--color-success)"></i>≥20 JP</span>
                <span><i style="background:var(--color-warning)"></i>&lt;20 JP</span>
                <span><i style="background:var(--color-text-muted)"></i>Belum</span>
            </div>
            <div class="dxg-card-foot">
                <div></div>
                <span class="dxg-card-link">Lihat detail →</span>
            </div>
        </a>

        {{-- SDM --}}
        <a href="{{ route('coming-soon', 'sdm-ringkasan') }}" class="dxg-card">
            <div class="dxg-card-glow"></div>
            <div class="dxg-card-top">
                <div class="dxg-card-icon"><i class="fa-solid fa-users"></i></div>
                <span class="dxg-card-badge">{{ number_format($sdm['medis'] + $sdm['non_medis']) }}</span>
            </div>
            <div class="dxg-card-title">SDM</div>
            <div class="dxg-card-subtitle">Distribusi tenaga per kategori</div>
            <div class="dxg-chart-area">
                <svg viewBox="0 0 220 90" width="100%" height="100%">
                    @foreach ($sdm['bars'] as $i => $val)
                        <rect class="dxg-bar" x="{{ 10 + $i * 42 }}" y="{{ 90 - $val }}" width="26" height="{{ $val }}" rx="4" fill="var(--color-success)" opacity="{{ 1 - $i * 0.08 }}" style="animation-delay:{{ 0.85 + $i * 0.07 }}s"/>
                    @endforeach
                </svg>
            </div>
            <div class="dxg-card-foot">
                <div class="dxg-card-stat">{{ $sdm['medis'] }}<span>medis / {{ $sdm['non_medis'] }} non-medis</span></div>
                <span class="dxg-card-link">Lihat detail →</span>
            </div>
        </a>

        {{-- Monitoring STR & SIP (wide) --}}
        @php
            $pctBermasalah = $totalPegawai ? round($totalBermasalah / $totalPegawai * 100) : 0;
            $seg = round((100 - $pctBermasalah) / 100 * $circ, 1);
        @endphp
        <a href="{{ route('monitoring-str-sip.index') }}" class="dxg-card dxg-card-wide">
            <div class="dxg-card-glow"></div>
            <div class="dxg-card-top">
                <div class="dxg-card-icon"><i class="fa-solid fa-file-shield"></i></div>
                <span class="dxg-card-badge">{{ $totalBermasalah }} bermasalah</span>
            </div>
            <div class="dxg-card-title">Monitoring STR & SIP</div>
            <div class="dxg-card-subtitle">Kelengkapan dokumen legal per ruangan</div>
            <div class="dxg-str-body">
                <svg viewBox="0 0 90 90" width="90" height="90" class="dxg-donut">
                    <circle cx="45" cy="45" r="34" fill="none" stroke="var(--color-border)" stroke-width="11"/>
                    <circle cx="45" cy="45" r="34" fill="none" stroke="var(--color-success)" stroke-width="11" stroke-linecap="round" transform="rotate(-90 45 45)" stroke-dasharray="{{ $seg }} {{ $circ }}"/>
                    <text x="45" y="50" text-anchor="middle" class="dxg-donut-label">{{ 100 - $pctBermasalah }}%</text>
                </svg>
                <div class="dxg-str-info">
                    <div class="dxg-legend">
                        <span><i style="background:var(--color-success)"></i>Lengkap</span>
                        <span><i style="background:var(--color-danger)"></i>Kadaluarsa / belum ada</span>
                    </div>
                    @if ($ruanganKritis)
                        <div class="dxg-str-note">{{ $ruanganKritis['ruangan'] }} paling kritis — {{ $ruanganKritis['bermasalah'] }} dari {{ $ruanganKritis['total_pegawai'] }} pegawai belum lengkap.</div>
                    @endif
                </div>
            </div>
            <div class="dxg-card-foot">
                <div></div>
                <span class="dxg-card-link">Lihat detail →</span>
            </div>
        </a>

        {{-- Cuti --}}
        <a href="{{ route('coming-soon', 'cuti') }}" class="dxg-card">
            <div class="dxg-card-glow"></div>
            <div class="dxg-card-top">
                <div class="dxg-card-icon"><i class="fa-solid fa-umbrella-beach"></i></div>
                <span class="dxg-card-badge">{{ now()->translatedFormat('M Y') }}</span>
            </div>
            <div class="dxg-card-title">Cuti</div>
            <div class="dxg-card-subtitle">Pegawai cuti per periode</div>
            <div class="dxg-chart-area">
                <svg viewBox="0 0 220 90" width="100%" height="100%">
                    @foreach ($cuti['bars'] as $i => $val)
                        <rect class="dxg-bar" x="{{ 4 + $i * 30 }}" y="{{ 90 - $val }}" width="20" height="{{ $val }}" rx="3" fill="{{ $val > 55 ? 'var(--color-warning)' : 'var(--color-success)' }}" opacity="{{ $val > 55 ? 1 : 0.7 }}" style="animation-delay:{{ 0.85 + $i * 0.05 }}s"/>
                    @endforeach
                </svg>
            </div>
            <div class="dxg-card-foot">
                <div class="dxg-card-stat">{{ $cuti['total_bulan_ini'] }}<span>pegawai bulan ini</span></div>
                <span class="dxg-card-link">Lihat detail →</span>
            </div>
        </a>

    </div>
</div>

</x-app-layout>