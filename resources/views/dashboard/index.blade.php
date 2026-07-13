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

    {{--
        ================= HERO: Monitoring STR & SIP =================
        TODO integrasi DB: ganti $str / $sip dari controller (bukan dummy di bawah).
        Struktur array wajib sama: ['persen' => int, 'label_kritis' => string, 'detail_kritis' => string]
    --}}
    @php
        $str = $str ?? ['persen' => 66, 'label_kritis' => 'Instalasi Gawat Darurat', 'detail_kritis' => '7 dari 12 pegawai belum lengkap'];
        $sip = $sip ?? ['persen' => 72, 'label_kritis' => 'Instalasi Rawat Inap', 'detail_kritis' => '5 dari 18 pegawai belum lengkap'];
        $totalBermasalah = $totalBermasalah ?? 14;
    @endphp
    <a href="{{ route('monitoring-str-sip.index') }}" class="dxg-hero">
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
                    {{-- Donut STR --}}
                    <div class="dxg-hero-donut-block">
                        <div class="dxg-mini-chart" id="chart-str"
                             data-chart-type="donut-single"
                             data-chart='@json(["label" => "STR", "persen" => $str["persen"], "color" => "success"])'></div>
                        <div class="dxg-mini-chart-label">STR</div>
                    </div>
                    {{-- Donut SIP --}}
                    <div class="dxg-hero-donut-block">
                        <div class="dxg-mini-chart" id="chart-sip"
                             data-chart-type="donut-single"
                             data-chart='@json(["label" => "SIP", "persen" => $sip["persen"], "color" => "primary"])'></div>
                        <div class="dxg-mini-chart-label">SIP</div>
                    </div>
                </div>

                <div class="dxg-hero-info">
                    <div class="dxg-legend">
                        <span><i style="background:var(--color-success)"></i>Lengkap</span>
                        <span><i style="background:var(--color-danger)"></i>Kadaluarsa / belum ada</span>
                    </div>
                    <div class="dxg-str-note">
                        <strong>{{ $str['label_kritis'] }}</strong> paling kritis STR — {{ $str['detail_kritis'] }}.
                    </div>
                    <div class="dxg-str-note">
                        <strong>{{ $sip['label_kritis'] }}</strong> paling kritis SIP — {{ $sip['detail_kritis'] }}.
                    </div>
                </div>
            </div>
        </div>
        <span class="dxg-card-link dxg-hero-link">Lihat detail <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
    </a>

    {{-- ================= SECONDARY: modul dengan kerangka chart ================= --}}
    <div class="dxg-secondary-label">Modul lainnya</div>
    <div class="dxg-grid">

        {{--
            Ekinerja — line chart
            TODO integrasi DB: $ekinerjaChart dari controller.
            categories = label sumbu-x, series = array of {name, data[]}
        --}}
        @php
            $ekinerjaChart = $ekinerjaChart ?? [
                'categories' => ['Sangat Baik','Baik','Cukup','Kurang','Perlu Perbaikan'],
                'series' => [
                    ['name' => 'Jumlah Pegawai', 'data' => [18, 22, 26, 24, 31]],
                    ['name' => 'Target', 'data' => [12, 20, 24, 19, 24]],
                ],
            ];
        @endphp
        <a href="{{ route('coming-soon', 'ekinerja') }}" class="dxg-tile">
            <div class="dxg-tile-top">
                <div class="dxg-card-icon"><i class="fa-solid fa-chart-line"></i></div>
                <span class="dxg-badge dxg-badge-soon">Segera hadir</span>
            </div>
            <div class="dxg-tile-title">Ekinerja</div>
            <div class="dxg-card-subtitle">Distribusi capaian kinerja pegawai</div>
            <div class="dxg-chart-box" id="chart-ekinerja" data-chart-type="line" data-chart='@json($ekinerjaChart)'></div>
            <div class="dxg-tile-stat">{{ $ekinerja['persen_baik'] ?? 78 }}<span>% baik/sangat baik</span></div>
        </a>

        {{--
            Pelatihan — donut chart
            TODO integrasi DB: $pelatihanChart dari controller.
        --}}
        @php
            $pelatihanChart = $pelatihanChart ?? [
                'labels' => ['≥20 JP', '<20 JP', 'Belum Pelatihan'],
                'series' => [62, 25, 13],
            ];
        @endphp
        <a href="{{ route('coming-soon', 'pelatihan') }}" class="dxg-tile">
            <div class="dxg-tile-top">
                <div class="dxg-card-icon"><i class="fa-solid fa-graduation-cap"></i></div>
                <span class="dxg-badge dxg-badge-soon">Segera hadir</span>
            </div>
            <div class="dxg-tile-title">Pelatihan</div>
            <div class="dxg-card-subtitle">Jam pelatihan per pegawai</div>
            <div class="dxg-chart-box" id="chart-pelatihan" data-chart-type="donut" data-chart='@json($pelatihanChart)'></div>
            <div class="dxg-tile-stat">{{ $pelatihan['lebih_20jp'] ?? 62 }}<span>% sudah ≥20 JP</span></div>
        </a>

        {{--
            SDM — bar chart
            TODO integrasi DB: $sdmChart dari controller.
        --}}
        @php
            $sdmChart = $sdmChart ?? [
                'categories' => ['Dokter', 'Perawat', 'Bidan', 'Non-Medis', 'Penunjang'],
                'series' => [['name' => 'Pegawai', 'data' => [86, 214, 97, 71, 63]]],
            ];
        @endphp
        <a href="{{ route('coming-soon', 'sdm-ringkasan') }}" class="dxg-tile">
            <div class="dxg-tile-top">
                <div class="dxg-card-icon"><i class="fa-solid fa-users"></i></div>
                <span class="dxg-badge dxg-badge-soon">Segera hadir</span>
            </div>
            <div class="dxg-tile-title">SDM</div>
            <div class="dxg-card-subtitle">Distribusi tenaga per kategori</div>
            <div class="dxg-chart-box" id="chart-sdm" data-chart-type="bar" data-chart='@json($sdmChart)'></div>
            <div class="dxg-tile-stat">{{ $sdm['medis'] ?? 676 }}<span>medis / {{ $sdm['non_medis'] ?? 769 }} non-medis</span></div>
        </a>

        {{--
            Cuti — bar chart per bulan
            TODO integrasi DB: $cutiChart dari controller. highlight_index = bulan berjalan (0-11).
        --}}
        @php
            $cutiChart = $cutiChart ?? [
                'categories' => ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'],
                'series' => [['name' => 'Pegawai Cuti', 'data' => [8,11,9,14,10,12,14,9,13,10,7,6]]],
                'highlight_index' => now()->month - 1,
            ];
        @endphp
        <a href="{{ route('coming-soon', 'cuti') }}" class="dxg-tile">
            <div class="dxg-tile-top">
                <div class="dxg-card-icon"><i class="fa-solid fa-umbrella-beach"></i></div>
                <span class="dxg-badge dxg-badge-soon">Segera hadir</span>
            </div>
            <div class="dxg-tile-title">Cuti</div>
            <div class="dxg-card-subtitle">Pegawai cuti per periode</div>
            <div class="dxg-chart-box" id="chart-cuti" data-chart-type="bar" data-chart='@json($cutiChart)'></div>
            <div class="dxg-tile-stat">{{ $cuti['total_bulan_ini'] ?? 14 }}<span>pegawai bulan ini</span></div>
        </a>

    </div>
</div>

</x-app-layout>