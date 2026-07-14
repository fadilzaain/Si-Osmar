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

    {{-- ================= HERO: Monitoring STR & SIP ================= --}}
    @php
        $str = array_merge(['persen' => 66, 'label_kritis' => 'Instalasi Gawat Darurat', 'detail_kritis' => '7 dari 12 pegawai belum lengkap'], $str ?? []);
        $sip = array_merge(['persen' => 72, 'label_kritis' => 'Instalasi Rawat Inap', 'detail_kritis' => '5 dari 18 pegawai belum lengkap'], $sip ?? []);
        $totalBermasalah = $totalBermasalah ?? 14;
    @endphp
    <a href="{{ route('monitoring-str-sip.index') }}" class="dxg-hero">
        <span class="dxg-live-dot" aria-hidden="true"></span>
        <div class="dxg-hero-glow"></div>
        <div class="dxg-hero-main">
            <div class="dxg-hero-head">
                <div class="dxg-card-icon dxg-card-icon-lg"><i class="fa-solid fa-shield-halved"></i></div>
                <div class="dxg-hero-heading">
                    <div class="dxg-hero-title">Monitoring STR & SIP</div>
                    <div class="dxg-card-subtitle">Kelengkapan dokumen legal per ruangan</div>
                </div>
                <span class="dxg-badge dxg-badge-alert">{{ $totalBermasalah }} bermasalah</span>
            </div>

            <div class="dxg-hero-body">
                <div class="dxg-hero-donuts">
                    <div class="dxg-hero-donut-block">
                        <div class="dxg-mini-chart" id="chart-str" data-chart-type="donut-single"
                             data-chart='@json(["persen" => $str["persen"], "color" => "success"])'></div>
                        <div class="dxg-mini-chart-label">STR</div>
                    </div>
                    <div class="dxg-hero-donut-block">
                        <div class="dxg-mini-chart" id="chart-sip" data-chart-type="donut-single"
                             data-chart='@json(["persen" => $sip["persen"], "color" => "primary"])'></div>
                        <div class="dxg-mini-chart-label">SIP</div>
                    </div>
                </div>

                <div class="dxg-hero-info">
                    <div class="dxg-legend">
                        <span><i style="background:var(--color-success)"></i>Lengkap</span>
                        <span><i style="background:var(--color-danger)"></i>Kadaluarsa / belum ada</span>
                    </div>
                    <div class="dxg-str-note"><strong>{{ $str['label_kritis'] }}</strong> paling kritis STR — {{ $str['detail_kritis'] }}.</div>
                    <div class="dxg-str-note"><strong>{{ $sip['label_kritis'] }}</strong> paling kritis SIP — {{ $sip['detail_kritis'] }}.</div>
                </div>
            </div>
        </div>
        <span class="dxg-card-link dxg-hero-link">Lihat detail <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
    </a>

    {{-- ================= SECONDARY ================= --}}
    <div class="dxg-secondary-label">Modul lainnya</div>
    <div class="dxg-grid">

        {{--
            Ekinerja — mini stat-block per kategori penilaian
            TODO integrasi DB: $ekinerjaStat dari controller, format sama kayak dummy di bawah.
        --}}
        @php
            $ekinerjaStat = $ekinerjaStat ?? [
                ['label' => 'Sangat Baik', 'value' => 31, 'tone' => 'success'],
                ['label' => 'Baik', 'value' => 47, 'tone' => 'success'],
                ['label' => 'Cukup', 'value' => 26, 'tone' => 'warning'],
                ['label' => 'Perlu Perbaikan', 'value' => 5, 'tone' => 'danger'],
            ];
        @endphp
        <a href="{{ route('coming-soon', 'ekinerja') }}" class="dxg-tile">
            <span class="dxg-live-dot" aria-hidden="true"></span>
            <div class="dxg-tile-top">
                <div class="dxg-card-icon"><i class="fa-solid fa-chart-line"></i></div>
                <span class="dxg-badge dxg-badge-soon">Segera hadir</span>
            </div>
            <div class="dxg-tile-title">Ekinerja</div>
            <div class="dxg-card-subtitle">Distribusi capaian kinerja pegawai</div>

            <div class="dxg-stat-grid">
                @foreach ($ekinerjaStat as $s)
                    <div class="dxg-stat-block">
                        <div class="dxg-stat-block-label">{{ $s['label'] }}</div>
                        <div class="dxg-stat-block-value tone-{{ $s['tone'] }}">{{ $s['value'] }}</div>
                    </div>
                @endforeach
            </div>

            <div class="dxg-tile-foot">
                <div class="dxg-tile-stat">{{ $ekinerja['persen_baik'] ?? 78 }}<span>% baik/sangat baik</span></div>
                <span class="dxg-card-link">Lihat detail →</span>
            </div>
        </a>

        {{--
            Pelatihan — list pegawai (bukan donut, gak nyambung buat data ini)
            TODO integrasi DB: $pelatihanList dari controller, format sama kayak dummy di bawah.
        --}}
        @php
            $pelatihanList = $pelatihanList ?? [
                ['nama' => 'Ns. Ratna Dewi', 'jam' => 34, 'status' => 'success'],
                ['nama' => 'dr. Bagas Prasetyo', 'jam' => 22, 'status' => 'success'],
                ['nama' => 'Yulia Anggraini', 'jam' => 0, 'status' => 'danger'],
            ];
            $pelatihanSisa = $pelatihanSisa ?? 41;
        @endphp
        <a href="{{ route('coming-soon', 'pelatihan') }}" class="dxg-tile">
            <span class="dxg-live-dot" aria-hidden="true"></span>
            <div class="dxg-tile-top">
                <div class="dxg-card-icon"><i class="fa-solid fa-graduation-cap"></i></div>
                <span class="dxg-badge dxg-badge-soon">Segera hadir</span>
            </div>
            <div class="dxg-tile-title">Pelatihan</div>
            <div class="dxg-card-subtitle">Jam pelatihan per pegawai</div>

            <div class="dxg-mini-list">
                @foreach ($pelatihanList as $row)
                    <div class="dxg-mini-list-row">
                        <span class="dxg-mini-list-name">{{ $row['nama'] }}</span>
                        <span class="dxg-badge dxg-badge-pill tone-{{ $row['status'] }}">
                            {{ $row['jam'] > 0 ? $row['jam'].' JP' : 'Belum' }}
                        </span>
                    </div>
                @endforeach
                <div class="dxg-mini-list-more">+{{ $pelatihanSisa }} pegawai lainnya</div>
            </div>

            <div class="dxg-tile-foot">
                <div class="dxg-tile-stat">{{ $pelatihan['lebih_20jp'] ?? 62 }}<span>% sudah ≥20 JP</span></div>
                <span class="dxg-card-link">Lihat detail →</span>
            </div>
        </a>

        {{--
            SDM — angka besar + radial (medis/non-medis), link ke modul yang udah jadi
            TODO integrasi DB: $sdm dari controller (medis/non_medis/persen_medis).
        --}}
        @php
            $sdmMedis = $sdm['medis'] ?? 676;
            $sdmNonMedis = $sdm['non_medis'] ?? 769;
            $sdmTotal = $sdmMedis + $sdmNonMedis;
            $sdmPersenMedis = $sdm['persen_medis'] ?? ($sdmTotal > 0 ? round($sdmMedis / $sdmTotal * 100) : 0);
        @endphp
        <a href="{{ route('coming-soon', 'sdm-ringkasan') }}" class="dxg-tile">
            <span class="dxg-live-dot" aria-hidden="true"></span>
            <div class="dxg-tile-top">
                <div class="dxg-card-icon"><i class="fa-solid fa-users"></i></div>
                <span class="dxg-badge dxg-badge-neutral">{{ number_format($sdmTotal) }} orang</span>
            </div>
            <div class="dxg-tile-title">SDM</div>
            <div class="dxg-card-subtitle">Distribusi tenaga per kategori</div>

            <div class="dxg-sdm-body">
                <div class="dxg-mini-chart dxg-mini-chart-sm" id="chart-sdm" data-chart-type="donut-single"
                     data-chart='@json(["persen" => $sdmPersenMedis, "color" => "primary", "size" => 72])'></div>
                <div class="dxg-stat-grid dxg-stat-grid-2">
                    <div class="dxg-stat-block">
                        <div class="dxg-stat-block-label">Medis</div>
                        <div class="dxg-stat-block-value">{{ $sdmMedis }}</div>
                    </div>
                    <div class="dxg-stat-block">
                        <div class="dxg-stat-block-label">Non-medis</div>
                        <div class="dxg-stat-block-value">{{ $sdmNonMedis }}</div>
                    </div>
                </div>
            </div>

            <div class="dxg-tile-foot">
                <div></div>
                <span class="dxg-card-link">Lihat detail →</span>
            </div>
        </a>

        {{--
            Cuti — mini stat-block ringkas
            TODO integrasi DB: $cutiStat dari controller.
        --}}
        @php
            $cutiStat = $cutiStat ?? [
                ['label' => 'Bulan ini', 'value' => 14, 'tone' => 'success'],
                ['label' => 'Akan datang', 'value' => 6, 'tone' => 'neutral'],
                ['label' => 'Rata² durasi', 'value' => '3 hr', 'tone' => 'neutral'],
                ['label' => 'Ruangan terbanyak', 'value' => 'IGD', 'tone' => 'warning'],
            ];
        @endphp
        <a href="{{ route('coming-soon', 'cuti') }}" class="dxg-tile">
            <span class="dxg-live-dot" aria-hidden="true"></span>
            <div class="dxg-tile-top">
                <div class="dxg-card-icon"><i class="fa-solid fa-umbrella-beach"></i></div>
                <span class="dxg-badge dxg-badge-soon">Segera hadir</span>
            </div>
            <div class="dxg-tile-title">Cuti</div>
            <div class="dxg-card-subtitle">Pegawai cuti per periode</div>

            <div class="dxg-stat-grid">
                @foreach ($cutiStat as $s)
                    <div class="dxg-stat-block">
                        <div class="dxg-stat-block-label">{{ $s['label'] }}</div>
                        <div class="dxg-stat-block-value tone-{{ $s['tone'] }}">{{ $s['value'] }}</div>
                    </div>
                @endforeach
            </div>

            <div class="dxg-tile-foot">
                <div></div>
                <span class="dxg-card-link">Lihat detail →</span>
            </div>
        </a>

    </div>
</div>

</x-app-layout>