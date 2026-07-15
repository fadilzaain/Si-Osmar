<x-app-layout title="Monitoring Cuti">

<div class="mct-wrap" data-monitoring-cuti>
    <div class="mct-eyebrow">SI-OSMAR / SDM</div>
    <h1 class="mct-title">Monitoring Cuti Pegawai</h1>
    <div class="mct-sub">Pemantauan sisa & pemakaian cuti pegawai per unit — RSUD Jombang</div>

    @if (empty($ringkasan))
        <x-empty-state
            icon="fa-solid fa-calendar-xmark"
            title="Data cuti belum bisa dimuat"
            description="API SIKAWAN sedang tidak bisa diakses. Coba muat ulang halaman beberapa saat lagi."
        />
    @else

        {{-- ================= KPI ringkas ================= --}}
        <div class="mct-kpi-grid">
            <x-stat-card
                icon="fa-solid fa-users"
                label="Pegawai dipantau"
                :value="$eksekutif['total_pegawai']"
                comparison="di {{ $eksekutif['total_unit'] }} unit"
                color="var(--color-primary)"
            />
            <x-stat-card
                icon="fa-solid fa-triangle-exclamation"
                label="Berstatus kritis"
                :value="$eksekutif['jumlah_kritis']"
                comparison="jatah cuti tahunan habis"
                color="var(--color-danger)"
            />
            <x-stat-card
                icon="fa-solid fa-hourglass-half"
                label="Perlu perhatian"
                :value="$eksekutif['jumlah_perhatian']"
                comparison="pemakaian sudah &ge; 75%"
                color="var(--color-warning)"
            />
            <x-stat-card
                icon="fa-solid fa-chart-pie"
                label="Rata-rata cuti terpakai"
                :value="$eksekutif['rata_rata_persen_terpakai'] . '%'"
                comparison="dari jatah cuti tahunan"
                color="var(--color-info)"
            />
        </div>

        {{-- ================= Kesimpulan naratif ================= --}}
        <div class="mct-insight">
            <div class="mct-insight-icon"><i class="fa-solid fa-lightbulb"></i></div>
            <div>
                <div class="mct-insight-label">Kesimpulan</div>
                <p class="mct-insight-text">{{ $kesimpulan }}</p>
            </div>
        </div>

        {{-- ================= Grafik ================= --}}
        <div class="mct-chart-grid">
            <div class="card-base mct-chart-card">
                <div class="card-header">
                    <div>
                        <div class="card-title">Distribusi Status Cuti</div>
                        <div class="card-subtitle">Sebaran seluruh pegawai berdasarkan status</div>
                    </div>
                </div>
                @php
                    $persenNormal = $eksekutif['total_pegawai'] > 0
                        ? round($eksekutif['jumlah_normal'] / $eksekutif['total_pegawai'] * 100)
                        : 100;
                @endphp
                <div class="mct-status-chart">
                    <x-chart-headline
                        :value="$persenNormal . '%'"
                        label="pegawai status cuti normal"
                        :tone="$eksekutif['jumlah_kritis'] > 0 ? 'danger' : 'success'"
                    />
                    <x-distribution-bar :series="$chartDistribusiStatus['series']" :labels="$chartDistribusiStatus['labels']" :colors="$chartDistribusiStatus['colors']" />
                    <div class="mct-donut-legend mct-donut-legend--inline">
                        <div class="mct-legend-row">
                            <span class="mct-legend-dot tone-success"></span>
                            <span class="mct-legend-label">Normal</span>
                            <span class="mct-legend-value">{{ $eksekutif['jumlah_normal'] }}</span>
                        </div>
                        <div class="mct-legend-row">
                            <span class="mct-legend-dot tone-warning"></span>
                            <span class="mct-legend-label">Perlu Perhatian</span>
                            <span class="mct-legend-value">{{ $eksekutif['jumlah_perhatian'] }}</span>
                        </div>
                        <div class="mct-legend-row">
                            <span class="mct-legend-dot tone-danger"></span>
                            <span class="mct-legend-label">Kritis</span>
                            <span class="mct-legend-value">{{ $eksekutif['jumlah_kritis'] }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-base mct-chart-card">
                <div class="card-header">
                    <div>
                        <div class="card-title">Top Pemakaian Cuti Tahunan</div>
                        <div class="card-subtitle">Pegawai dengan persentase pemakaian tertinggi</div>
                    </div>
                </div>
                @if (count($topPegawaiKritis))
                    <div data-chart-type="bar-horizontal" data-chart='@json($chartTopPegawai)'></div>
                @else
                    <div class="mct-rank-empty">Belum ada pegawai dengan jatah cuti tahunan tercatat.</div>
                @endif
            </div>
        </div>

        {{-- ================= Legend & toolbar ================= --}}
        <div class="mct-detail-divider">
            <span>Detail per unit</span>
        </div>

        <div class="mct-legend">
            <span class="mct-legend-item"><x-badge variant="danger">KRITIS</x-badge> jatah cuti tahunan habis</span>
            <span class="mct-legend-item"><x-badge variant="warning">PERHATIAN</x-badge> pemakaian &ge; 75%</span>
            <span class="mct-legend-item"><x-badge variant="success">NORMAL</x-badge> pemakaian masih wajar</span>
        </div>

        <div class="mct-toolbar">
            <div class="mct-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="mctSearch" placeholder="Cari nama unit / pegawai...">
            </div>

            <div class="mct-filter-pills">
                <button type="button" class="mct-pill active" data-filter="semua">
                    Semua <span>{{ $eksekutif['total_pegawai'] }}</span>
                </button>
                <button type="button" class="mct-pill mct-pill--danger" data-filter="KRITIS">
                    Kritis <span>{{ $eksekutif['jumlah_kritis'] }}</span>
                </button>
                <button type="button" class="mct-pill mct-pill--warning" data-filter="PERHATIAN">
                    Perhatian <span>{{ $eksekutif['jumlah_perhatian'] }}</span>
                </button>
                <button type="button" class="mct-pill mct-pill--success" data-filter="NORMAL">
                    Normal <span>{{ $eksekutif['jumlah_normal'] }}</span>
                </button>
            </div>

            <div class="mct-bulk-actions">
                <button type="button" class="mct-bulk-btn" data-bulk="expand">
                    <i class="fa-solid fa-square-caret-down"></i> Buka semua
                </button>
                <button type="button" class="mct-bulk-btn" data-bulk="collapse">
                    <i class="fa-solid fa-square-caret-up"></i> Tutup semua
                </button>
            </div>
        </div>

        <div class="mct-empty-filter" data-empty-filter hidden>
            Tidak ada unit / pegawai yang cocok dengan pencarian / filter saat ini.
        </div>

        {{-- ================= Daftar unit (accordion) ================= --}}
        <div class="mct-unit-list" data-accordion>
            @foreach ($ringkasan as $unit)
                @php
                    $badgeVariant = match ($unit['summary']['status']) {
                        'KRITIS' => 'danger',
                        'PERHATIAN' => 'warning',
                        default => 'success',
                    };
                @endphp
                <div id="unit-{{ $unit['slug'] }}" class="mct-unit card-base"
                    style="animation-delay: {{ $loop->index * 40 }}ms"
                    data-status="{{ $unit['summary']['status'] }}"
                    data-search="{{ strtolower($unit['unit']) }}">
                    <button type="button" class="mct-unit-head" data-accordion-trigger>
                        <div class="mct-unit-title">
                            <span class="mct-unit-name">{{ $unit['unit'] }}</span>
                            <span class="mct-unit-count">{{ $unit['summary']['total_pegawai'] }} pegawai</span>
                            <x-badge :variant="$badgeVariant">
                                @if ($unit['summary']['status'] === 'KRITIS')
                                    Kritis {{ $unit['summary']['jumlah_kritis'] }}
                                @elseif ($unit['summary']['status'] === 'PERHATIAN')
                                    Perhatian {{ $unit['summary']['jumlah_perhatian'] }}
                                @else
                                    Normal
                                @endif
                            </x-badge>
                        </div>
                        <i class="fa-solid fa-chevron-down mct-chev" aria-hidden="true"></i>
                    </button>

                    <div class="mct-body" data-accordion-panel>
                        <div class="mct-body-inner">
                            <div class="mct-pegawai-grid">
                                @foreach ($unit['pegawai'] as $p)
                                    @php
                                        $pBadge = match ($p['status']) {
                                            'KRITIS' => 'danger',
                                            'PERHATIAN' => 'warning',
                                            default => 'success',
                                        };
                                        $barTone = match ($p['status']) {
                                            'KRITIS' => 'tone-danger',
                                            'PERHATIAN' => 'tone-warning',
                                            default => 'tone-success',
                                        };
                                    @endphp
                                    <button type="button" class="mct-pegawai-card"
                                            data-mct-pegawai
                                            data-status="{{ $p['status'] }}"
                                            data-search="{{ strtolower($p['nama']) }}"
                                            data-detail='@json($p)'>
                                        <div class="mct-pegawai-top">
                                            <div class="mct-avatar">{{ $p['inisial'] }}</div>
                                            <div class="mct-pegawai-info">
                                                <div class="mct-pegawai-name">{{ $p['nama'] }}</div>
                                                <div class="mct-pegawai-meta">Cuti Tahunan {{ $p['tahun'] }}</div>
                                            </div>
                                        </div>
                                        <div class="mct-pegawai-status-row">
                                            <x-badge :variant="$pBadge">{{ $p['status'] }}</x-badge>
                                        </div>

                                        @if ($p['punya_jatah_utama'])
                                            <div class="mct-pegawai-bar">
                                                <div class="mct-pegawai-bar-track">
                                                    <div class="mct-pegawai-bar-fill {{ $barTone }}" style="width: {{ $p['persen_terpakai'] }}%"></div>
                                                </div>
                                                <span class="mct-pegawai-bar-label">{{ $p['diambil_utama'] }}/{{ $p['jatah_utama'] }} hari · sisa {{ $p['sisa_utama'] }}</span>
                                            </div>
                                        @else
                                            <div class="mct-pegawai-bar-label mct-no-jatah">Tidak ada jatah cuti tahunan tercatat</div>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- ================= Modal detail rincian cuti pegawai ================= --}}
    <x-modal id="mctDetailModal" title="Rincian Cuti Pegawai">
        <div class="mct-modal-body" data-mct-modal-body>
            {{-- Diisi via JS saat kartu pegawai diklik --}}
        </div>
    </x-modal>
</div>

</x-app-layout>
