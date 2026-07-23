<x-app-layout title="Capaian Kinerja">

<div class="mek-wrap" data-monitoring-evkin>
    <div class="mek-eyebrow">SI-OSMAR / SDM</div>
    <h1 class="mek-title">Capaian Kinerja Pegawai</h1>
    <div class="mek-sub">Pemantauan Evaluasi Kinerja Pegawai — RSUD Jombang</div>

    @if (empty($ringkasan))
        <x-empty-state
            icon="fa-solid fa-chart-line"
            title="Data capaian kinerja belum bisa dimuat"
            description="API SIKAWAN sedang tidak bisa diakses. Coba muat ulang halaman beberapa saat lagi."
        />
    @else

        {{-- ================= KPI ringkas ================= --}}
        <div class="mek-kpi-grid">
            <x-stat-card
                icon="fa-solid fa-users"
                label="Pegawai dipantau"
                :value="$eksekutif['total_pegawai']"
                comparison="di {{ $eksekutif['total_unit'] }} unit"
                color="var(--color-primary)"
            />
            <x-stat-card
                icon="fa-solid fa-award"
                label="Sangat Baik"
                :value="$eksekutif['per_predikat']['Sangat Baik']"
                comparison="predikat terkini"
                color="var(--color-success)"
            />
            <x-stat-card
                icon="fa-solid fa-thumbs-up"
                label="Baik"
                :value="$eksekutif['per_predikat']['Baik']"
                comparison="predikat terkini"
                color="var(--color-primary)"
            />
            <x-stat-card
                icon="fa-solid fa-circle-half-stroke"
                label="Cukup"
                :value="$eksekutif['per_predikat']['Cukup']"
                comparison="predikat terkini"
                color="var(--color-info)"
            />
            <x-stat-card
                icon="fa-solid fa-triangle-exclamation"
                label="Kurang"
                :value="$eksekutif['per_predikat']['Kurang']"
                comparison="predikat terkini"
                color="var(--color-warning)"
            />
            <x-stat-card
                icon="fa-solid fa-circle-exclamation"
                label="Sangat Kurang"
                :value="$eksekutif['per_predikat']['Sangat Kurang']"
                comparison="predikat terkini"
                color="var(--color-danger)"
            />
        </div>

        {{-- ================= Kesimpulan naratif ================= --}}
        <div class="mek-insight">
            <div class="mek-insight-icon"><i class="fa-solid fa-lightbulb"></i></div>
            <div>
                <div class="mek-insight-label">Kesimpulan</div>
                <p class="mek-insight-text">{{ $kesimpulan }}</p>
            </div>
        </div>

        {{-- ================= Grafik ================= --}}
         <div class="mek-chart-grid">
            <div class="card-base mek-chart-card">
                <div class="card-header">
                    <div>
                        <div class="card-title">Distribusi Predikat</div>
                        <div class="card-subtitle">Sebaran pegawai yang sudah dinilai, berdasarkan predikat terkini</div>
                    </div>
                </div>
 
                @if ($eksekutif['total_dinilai'] > 0)
                    <div data-chart-type="bar-horizontal" data-chart='@json($chartDistribusiPredikat)'></div>
                @else
                    <div class="mek-rank-empty">Belum ada pegawai dengan penilaian tercatat.</div>
                @endif
            </div>
 
            <div class="card-base mek-chart-card">
                <div class="card-header">
                    <div>
                        <div class="card-title">Unit Perlu Perhatian</div>
                        <div class="card-subtitle">Diurutkan dari persentase pegawai yang belum dinilai paling tinggi</div>
                    </div>
                </div>
                @if ($eksekutif['total_pegawai'] > 0)
                    <div data-chart-type="bar-horizontal" data-chart='@json($chartUnitPerluPerhatian)'></div>
                @else
                    <div class="mek-rank-empty">Belum ada data pegawai yang bisa ditampilkan.</div>
                @endif
            </div>
        </div>

        {{-- ================= Legend & toolbar ================= --}}
        <div class="mek-detail-divider">
            <span>Detail per unit</span>
        </div>

        <div class="mek-legend">
            @foreach ($tonePredikat as $predikat => $tone)
                <span class="mek-legend-item"><x-badge :variant="$tone">{{ $predikat }}</x-badge></span>
            @endforeach
        </div>

        <div class="mek-toolbar">
            <div class="mek-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="mekSearch" placeholder="Cari nama unit / pegawai...">
            </div>

            <div class="mek-filter-pills">
                <button type="button" class="mek-pill active" data-filter="semua">
                    Semua <span>{{ $eksekutif['total_pegawai'] }}</span>
                </button>
                @foreach ($eksekutif['per_predikat'] as $predikat => $jumlah)
                    <button type="button" class="mek-pill mek-pill--{{ $tonePredikat[$predikat] }}" data-filter="{{ $predikat }}">
                        {{ $predikat }} <span>{{ $jumlah }}</span>
                    </button>
                @endforeach
                @if ($eksekutif['belum_dinilai'] > 0)
                    <button type="button" class="mek-pill mek-pill--neutral" data-filter="Belum Dinilai">
                        Belum Dinilai <span>{{ $eksekutif['belum_dinilai'] }}</span>
                    </button>
                @endif
            </div>

            <div class="mek-bulk-actions">
                <button type="button" class="mek-bulk-btn" data-bulk="expand">
                    <i class="fa-solid fa-square-caret-down"></i> Buka semua
                </button>
                <button type="button" class="mek-bulk-btn" data-bulk="collapse">
                    <i class="fa-solid fa-square-caret-up"></i> Tutup semua
                </button>
            </div>
        </div>

        <div class="mek-empty-filter" data-empty-filter hidden>
            Tidak ada unit / pegawai yang cocok dengan pencarian / filter saat ini.
        </div>

        {{-- ================= Daftar unit (accordion) — ini bagian detail
             pencarian pegawai yang dimaksud direktur ================= --}}
        <div class="mek-unit-list" data-accordion>
            @foreach ($ringkasan as $unit)
                @php
                    $searchNama = collect($unit['pegawai'])->pluck('nama')->implode(' ');
                @endphp
                <div id="unit-{{ $unit['slug'] }}" class="mek-unit card-base"
                     style="animation-delay: {{ $loop->index * 40 }}ms"
                     data-search="{{ strtolower($unit['unit'] . ' ' . $searchNama) }}">
                    <button type="button" class="mek-unit-head" data-accordion-trigger>
                        <div class="mek-unit-title">
                            <span class="mek-unit-name">{{ $unit['unit'] }}</span>
                            <span class="mek-unit-count">{{ $unit['summary']['total_pegawai'] }} pegawai</span>
                            @if ($unit['summary']['total_dinilai'] > 0)
                                <x-badge :variant="$unit['summary']['persen_baik'] >= 75 ? 'success' : ($unit['summary']['persen_baik'] >= 50 ? 'warning' : 'danger')">
                                    {{ $unit['summary']['persen_baik'] }}% baik
                                </x-badge>
                            @else
                                <x-badge variant="neutral">Belum dinilai</x-badge>
                            @endif
                        </div>
                        <i class="fa-solid fa-chevron-down mek-chev" aria-hidden="true"></i>
                    </button>

                    <div class="mek-body" data-accordion-panel>
                        <div class="mek-body-inner">
                            <div class="mek-table-scroll">
                                <table class="mek-table">
                                    <thead>
                                        <tr>
                                            <th>Pegawai</th>
                                            <th class="mek-col-center">TW 1</th>
                                            <th class="mek-col-center">TW 2</th>
                                            <th class="mek-col-center">TW 3</th>
                                            <th class="mek-col-center">TW 4</th>
                                            <th>Predikat Terkini</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($unit['pegawai'] as $p)
                                            <tr data-mek-pegawai
                                                data-predikat="{{ $p['predikat_terkini'] ?? 'Belum Dinilai' }}"
                                                data-search="{{ strtolower($p['nama']) }}">
                                                <td>
                                                    <div class="mek-person">
                                                        <div class="mek-avatar">{{ $p['inisial'] }}</div>
                                                        <div>
                                                            <div class="mek-pname">{{ $p['nama'] }}</div>
                                                            <div class="mek-prole">Tahun {{ $p['tahun'] }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="mek-col-center"><x-monitoring-evkin.predikat-cell :predikat="$p['triwulan']['tw_1']" :tone-predikat="$tonePredikat" /></td>
                                                <td class="mek-col-center"><x-monitoring-evkin.predikat-cell :predikat="$p['triwulan']['tw_2']" :tone-predikat="$tonePredikat" /></td>
                                                <td class="mek-col-center"><x-monitoring-evkin.predikat-cell :predikat="$p['triwulan']['tw_3']" :tone-predikat="$tonePredikat" /></td>
                                                <td class="mek-col-center"><x-monitoring-evkin.predikat-cell :predikat="$p['triwulan']['tw_4']" :tone-predikat="$tonePredikat" /></td>
                                                <td>
                                                    @if ($p['predikat_terkini'])
                                                        <x-monitoring-evkin.predikat-cell :predikat="$p['predikat_terkini']" :tone-predikat="$tonePredikat" />
                                                        <div class="mek-tw-label">{{ $p['triwulan_terkini'] }}</div>
                                                    @else
                                                        <span class="mek-cell-empty">Belum dinilai</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

</x-app-layout>