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

        {{-- ================= 1. Monitoring Dokumen (prioritas, live) ================= --}}
        @php
            $dokumenStat = $dokumenStat ?? [
                ['label' => 'STR', 'value' => '66%', 'percent' => 66, 'tone' => 'warning'],
                ['label' => 'SIP', 'value' => '72%', 'percent' => 72, 'tone' => 'warning'],
                ['label' => 'RKK', 'value' => '58%', 'percent' => 58, 'tone' => 'danger'],
                ['label' => 'SPK', 'value' => '81%', 'percent' => 81, 'tone' => 'success'],
            ];
            $dokumenBermasalah = $dokumenBermasalah ?? 14;
            $dokumenNote = $dokumenNote ?? 'RKK paling kritis — 9 pegawai IGD belum lengkap.';
        @endphp
        <x-dashboard.tile
            title="Monitoring Dokumen"
            subtitle="STR, SIP, RKK & SPK seluruh pegawai"
            icon="fa-solid fa-file-shield"
            href="{{ route('monitoring-str-sip.index') }}"
            badge-text="{{ $dokumenBermasalah }} bermasalah"
            badge-tone="alert"
            :priority="true"
            :live="true"
        >
            <x-dashboard.progress-bar :items="$dokumenStat" />
            <div class="dxg-doc-note">{{ $dokumenNote }}</div>
        </x-dashboard.tile>

        {{-- ================= 2. Capaian Kinerja ================= --}}
        @php
            $ekinerjaStat = $ekinerjaStat ?? [
                ['label' => 'Sangat Baik', 'value' => 31, 'tone' => 'success', 'color' => 'success'],
                ['label' => 'Baik', 'value' => 47, 'tone' => 'success', 'color' => 'primary'],
                ['label' => 'Cukup', 'value' => 26, 'tone' => 'warning', 'color' => 'warning'],
                ['label' => 'Perlu Perbaikan', 'value' => 5, 'tone' => 'danger', 'color' => 'danger'],
            ];
            $ekinerjaPersenBaik = $ekinerjaPersenBaik ?? 78;

            $ekinerjaChartData = [
                'series' => array_column($ekinerjaStat, 'value'),
                'labels' => array_column($ekinerjaStat, 'label'),
                'colors' => array_column($ekinerjaStat, 'color'),
                'size' => 128,
                'totalValue' => $ekinerjaPersenBaik . '%',
                'totalLabel' => 'Baik+',
            ];
        @endphp
        <x-dashboard.tile
            title="Capaian Kinerja"
            subtitle="Distribusi capaian kinerja pegawai"
            icon="fa-solid fa-chart-line"
            href="{{ route('coming-soon', 'ekinerja') }}"
            badge-text="Segera hadir"
            badge-tone="soon"
            :footer-value="$ekinerjaPersenBaik . '%'"
            footer-label="baik/sangat baik"
        >
            <div class="dxg-donut-body">
                <div class="dxg-mini-chart" data-chart-type="donut-multi"
                    data-chart='@json($ekinerjaChartData)'></div>
                <div class="dxg-donut-legend">
                    @foreach ($ekinerjaStat as $s)
                        <div class="dxg-legend-row">
                            <span class="dxg-legend-dot tone-{{ $s['tone'] }}"></span>
                            <span class="dxg-legend-label">{{ $s['label'] }}</span>
                            <span class="dxg-legend-value">{{ $s['value'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </x-dashboard.tile>

        {{-- ================= 3. SDM — card lebar, isinya peluang redistribusi pegawai lintas unit ================= --}}
        <x-dashboard.tile
            title="SDM"
            subtitle="Peluang redistribusi pegawai antar unit"
            icon="fa-solid fa-users"
            href="{{ route('sdm-bezetting.index') }}"
            badge-text="{{ $sdmTotalPeluang }} peluang"
            badge-tone="neutral"
            :wide="true"
            :live="true"
        >
            @if (empty($sdmRedistribusi))
                <x-empty-state
                    icon="fa-solid fa-right-left"
                    title="Belum ada peluang redistribusi"
                    description="Peluang pemindahan pegawai antar unit akan muncul di sini kalau ada jabatan yang kurang di satu unit tapi lebih di unit lain."
                />
            @else
                <div class="bzs-redis-list">
                    @foreach ($sdmRedistribusi as $p)
                        @php
                            $unitKurang = $p['unit_kurang'][0] ?? null;
                            $unitLebih = $p['unit_lebih'][0] ?? null;
                        @endphp
                        <div class="bzs-redis-row">
                            <i class="fa-solid fa-right-left bzs-redis-icon tone-info" aria-hidden="true"></i>
                            <div class="bzs-redis-text">
                                <strong>{{ $p['jabatan'] }}</strong> — bisa pindah {{ $p['potensi_pindah'] }} orang
                                @if ($unitLebih && $unitKurang)
                                    dari <strong>{{ $unitLebih['unit'] }}</strong> ke <strong>{{ $unitKurang['unit'] }}</strong>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-dashboard.tile>

        {{-- ================= 4. Cuti ================= --}}
        @php
            $cutiProgress = $cutiProgress ?? [
                ['label' => 'Ahmad Fauzi', 'value' => '8/12', 'percent' => 67, 'tone' => 'success'],
                ['label' => 'Siti Marlina', 'value' => '3/12', 'percent' => 25, 'tone' => 'warning'],
                ['label' => 'Budi Santoso', 'value' => '0/12', 'percent' => 0, 'tone' => 'danger'],
            ];
            $cutiSisaLain = $cutiSisaLain ?? 27;
            $cutiAktifBulanIni = $cutiAktifBulanIni ?? 14;
        @endphp
        <x-dashboard.tile
            title="Cuti"
            subtitle="Sisa & riwayat cuti per pegawai"
            icon="fa-solid fa-umbrella-beach"
            href="{{ route('coming-soon', 'cuti') }}"
            badge-text="Segera hadir"
            badge-tone="soon"
            :footer-value="$cutiAktifBulanIni"
            footer-label="cuti bulan ini"
        >
        <x-dashboard.progress-bar :items="$cutiProgress" />
            <div class="dxg-mini-list-more">+{{ $cutiSisaLain }} pegawai lainnya</div>
        </x-dashboard.tile>

        {{-- ================= 5. Pelatihan ================= --}}
        @php
            $pelatihanProgress = $pelatihanProgress ?? [
                ['label' => 'Ns. Ratna Dewi', 'value' => '34 JP', 'percent' => 100, 'tone' => 'success'],
                ['label' => 'dr. Bagas Prasetyo', 'value' => '22 JP', 'percent' => 100, 'tone' => 'success'],
                ['label' => 'Yulia Anggraini', 'value' => 'Belum', 'percent' => 0, 'tone' => 'danger'],
            ];
            $pelatihanSisa = $pelatihanSisa ?? 41;
            $pelatihanLebih20jp = $pelatihanLebih20jp ?? 62;
        @endphp
        <x-dashboard.tile
            title="Pelatihan"
            subtitle="Jam pelatihan & kelengkapan syarat"
            icon="fa-solid fa-graduation-cap"
            href="{{ route('coming-soon', 'pelatihan') }}"
            badge-text="Segera hadir"
            badge-tone="soon"
            :footer-value="$pelatihanLebih20jp . '%'"
            footer-label="sudah ≥20 JP"
        >
            <x-dashboard.progress-bar :items="$pelatihanProgress" />
            <div class="dxg-mini-list-more">+{{ $pelatihanSisa }} pegawai lainnya</div>
        </x-dashboard.tile>

    </div>
</div>

</x-app-layout>