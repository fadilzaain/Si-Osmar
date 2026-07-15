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
    <div class="dxg-sub">Ringkasan Dashboard Si-Osmar — {{ now()->translatedFormat('d F Y') }}</div>

    <div class="dxg-grid">

        {{-- ================= 1. Monitoring Dokumen (prioritas, live) ================= --}}
    @php
        $dokumenBermasalah = $dokumenEksekutif['total_bermasalah'];
        $dokumenNote = $dokumenBermasalah <= 0
            ? 'Seluruh dokumen legal pegawai lengkap dan berlaku di semua unit.'
            : ($unitDokumenKritis
                ? "{$unitDokumenKritis['unit']} paling kritis — {$unitDokumenKritis['summary']['bermasalah']} pegawai bermasalah."
                : "{$dokumenBermasalah} pegawai punya dokumen bermasalah.");
    @endphp
        <x-dashboard.tile
            title="Monitoring Dokumen"
            subtitle="STR, SIP, RKK & SPK seluruh pegawai"
            icon="fa-solid fa-file-shield"
            href="{{ route('monitoring-str-sip.index') }}"
            badge-text="{{ $dokumenBermasalah > 0 ? $dokumenBermasalah . ' bermasalah' : 'Semua lengkap' }}"
            badge-tone="{{ $dokumenBermasalah > 0 ? 'alert' : 'neutral' }}"
            :footer-value="$dokumenEksekutif['total_dokumen_kadaluarsa']"
            footer-label="dokumen kadaluarsa"
            :priority="true"
            :live="true"
        >
            <div class="dxg-status-chart">
                <x-chart-headline
                    :value="$dokumenEksekutif['persen_lengkap'] . '%'"
                    label="dokumen lengkap & berlaku"
                    :tone="$dokumenBermasalah > 0 ? 'warning' : 'success'"
                />
                    <x-distribution-bar :series="$dokumenChart['series']" :labels="$dokumenChart['labels']" :colors="$dokumenChart['colors']" />
                <div class="dxg-donut-legend dxg-donut-legend--inline">
                    @foreach ($dokumenChart['labels'] as $i => $label)
                        <div class="dxg-legend-row">
                            <span class="dxg-legend-dot tone-{{ $dokumenChart['colors'][$i] }}"></span>
                            <span class="dxg-legend-label">{{ $label }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
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

        {{-- ================= 3. SDM — card lebar ================= --}}
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
            $cutiKritis = $cutiEksekutif['jumlah_kritis'];
            $cutiTotal = max(1, $cutiEksekutif['total_pegawai']); // hindari div/0
        @endphp
        <x-dashboard.tile
            title="Cuti"
            subtitle="Rekap cuti tahunan seluruh pegawai"
            icon="fa-solid fa-umbrella-beach"
            href="{{ route('monitoring-cuti.index', 'cuti') }}"
            badge-text="{{ $cutiKritis > 0 ? $cutiKritis . ' kritis' : 'Aman' }}"
            badge-tone="{{ $cutiKritis > 0 ? 'alert' : 'neutral' }}"
            :footer-value="$cutiEksekutif['rata_rata_persen_terpakai'] . '%'"
            footer-label="rata-rata terpakai"
            :live="true"
        >
           <div class="dxg-status-chart">
                <div class="dxg-stacked-bar-track">
                    <div class="dxg-stacked-bar-seg tone-success" style="width: {{ $cutiEksekutif['jumlah_normal'] / $cutiTotal * 100 }}%"></div>
                    <div class="dxg-stacked-bar-seg tone-warning" style="width: {{ $cutiEksekutif['jumlah_perhatian'] / $cutiTotal * 100 }}%"></div>
                    <div class="dxg-stacked-bar-seg tone-danger" style="width: {{ $cutiEksekutif['jumlah_kritis'] / $cutiTotal * 100 }}%"></div>
                </div>
                <div class="dxg-donut-legend dxg-donut-legend--inline">
                    <div class="dxg-legend-row">
                        <span class="dxg-legend-dot tone-success"></span>
                        <span class="dxg-legend-label">Normal</span>
                        <span class="dxg-legend-value">{{ $cutiEksekutif['jumlah_normal'] }}</span>
                    </div>
                    <div class="dxg-legend-row">
                        <span class="dxg-legend-dot tone-warning"></span>
                        <span class="dxg-legend-label">Perhatian</span>
                        <span class="dxg-legend-value">{{ $cutiEksekutif['jumlah_perhatian'] }}</span>
                    </div>
                    <div class="dxg-legend-row">
                        <span class="dxg-legend-dot tone-danger"></span>
                        <span class="dxg-legend-label">Kritis</span>
                        <span class="dxg-legend-value">{{ $cutiEksekutif['jumlah_kritis'] }}</span>
                    </div>
                </div>
            </div>
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