<x-app-layout title="Si-Osmar">

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
    <div class="dxg-sub">Ringkasan Dashboard Si-Osmar {{ now()->translatedFormat('d F Y') }}</div>

    <div class="dxg-grid">

        {{-- ================= 1. Monitoring Dokumen ================= --}}
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
            subtitle="SIP, RKK & SPK seluruh pegawai"
            icon="fa-solid fa-file-shield"
            href="{{ route('monitoring-str-sip.index') }}"
            badge-text="{{ $dokumenBermasalah > 0 ? $dokumenBermasalah . ' belum upload / kadaluarsa' : 'Semua lengkap' }}"
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
            $ekinerjaBelumDinilai = $ekinerjaEksekutif['belum_dinilai'];
        @endphp
        <x-dashboard.tile
            title="Capaian Kinerja"
            subtitle="Distribusi capaian kinerja pegawai"
            icon="fa-solid fa-chart-line"
            href="{{ route('monitoring-evkin.index') }}"
            badge-text="{{ $ekinerjaBelumDinilai > 0 ? $ekinerjaBelumDinilai . ' belum dinilai' : 'Semua dinilai' }}"
            badge-tone="{{ $ekinerjaBelumDinilai > 0 ? 'alert' : 'neutral' }}"
            :footer-value="$ekinerjaEksekutif['persen_baik'] . '%'"
            footer-label="baik/sangat baik"
            :live="true"
        >
            <div class="dxg-donut-body">
                <div class="dxg-mini-chart" data-chart-type="donut-multi"
                    data-chart='@json($ekinerjaChartData)'></div>
                <div class="dxg-donut-legend">
                    @foreach ($ekinerjaChartData['labels'] as $i => $label)
                        <div class="dxg-legend-row">
                            <span class="dxg-legend-dot tone-{{ $ekinerjaChartData['colors'][$i] }}"></span>
                            <span class="dxg-legend-label">{{ $label }}</span>
                            <span class="dxg-legend-value">{{ $ekinerjaChartData['series'][$i] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </x-dashboard.tile>


        {{-- ================= 3. SDM — card lebar ================= --}}
        @php
            $sdmKekurangan = $sdmEksekutif['total_unit_kurang'];
        @endphp
        <x-dashboard.tile
            title="SDM"
            subtitle="Distribusi tenaga per kelompok profesi"
            icon="fa-solid fa-users"
            href="{{ route('sdm-bezetting.index') }}"
            badge-text="{{ $sdmKekurangan > 0 ? $sdmKekurangan . ' unit kritis' : 'Terpenuhi' }}"
            badge-tone="{{ $sdmKekurangan > 0 ? 'alert' : 'neutral' }}"
            :wide="true"
            :live="true"
        >
            <div class="dxg-donut-body">
                <div class="dxg-mini-chart" data-chart-type="donut-multi"
                    data-chart='@json($sdmDistribusiKategori)'></div>
                <div class="dxg-donut-legend">
                    @foreach ($sdmDistribusiKategori['labels'] as $i => $label)
                        <div class="dxg-legend-row">
                            <span class="dxg-legend-dot tone-{{ $sdmDistribusiKategori['colors'][$i] }}"></span>
                            <span class="dxg-legend-label">{{ $label }}</span>
                            <span class="dxg-legend-value">{{ $sdmDistribusiKategori['series'][$i] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
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
        <x-dashboard.tile
            title="Pelatihan"
            subtitle="Rata-rata jam pelatihan per unit"
            icon="fa-solid fa-graduation-cap"
            href="{{ route('pelatihan.index') }}"
            :footer-value="$pelatihanEksekutif['rata_rata_jam_per_pegawai']"
            footer-label="rata-rata jam/pegawai"
        >
            <div data-chart-type="bar-horizontal" data-chart='@json($pelatihanChartPerUnit)'></div>
        </x-dashboard.tile>

    </div>
</div>

</x-app-layout>