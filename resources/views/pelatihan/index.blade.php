<x-app-layout title="Monitoring Pelatihan">

<div class="plh-wrap" data-monitoring-pelatihan>
    <div class="plh-eyebrow">SI-OSMAR / SDM</div>
    <h1 class="plh-title">Monitoring Pelatihan Pegawai</h1>
    <div class="plh-sub">Pemantauan pelatihan pegawai — RSUD Jombang</div>

    @if (empty($ringkasan))
        <x-empty-state
            icon="fa-solid fa-graduation-cap"
            title="Data pelatihan belum bisa dimuat"
            description="API SIKAWAN sedang tidak bisa diakses. Coba muat ulang halaman beberapa saat lagi."
        />
    @else

        {{-- ================= KPI ringkas ================= --}}
        <div class="plh-kpi-grid">
            <x-stat-card
                icon="fa-solid fa-users"
                label="Total Pegawai"
                :value="number_format($kepesertaan['total_pegawai'], 0, ',', '.')"
                comparison="Seluruh pegawai aktif"
                color="var(--color-primary)"
            />
            <x-stat-card
                icon="fa-solid fa-user-check"
                label="Pelatihan 20JP+"
                :value="number_format($kepesertaan['jumlah_20jp_plus'], 0, ',', '.')"
                comparison="{{ $kepesertaan['persen_20jp_plus'] }}% telah mengikuti pelatihan"
                color="var(--color-success)"
            />
            <x-stat-card
                icon="fa-solid fa-user-check"
                label="Sudah Pelatihan"
                :value="number_format($kepesertaan['jumlah_sudah_pelatihan'], 0, ',', '.')"
                comparison="{{ $kepesertaan['persen_sudah_pelatihan'] }}% telah mengikuti pelatihan"
                color="var(--color-secondary)"
            />
            <x-stat-card
                icon="fa-solid fa-user-xmark"
                label="Belum Pelatihan"
                :value="number_format($kepesertaan['jumlah_belum_pelatihan'], 0, ',', '.')"
                comparison="{{ $kepesertaan['persen_belum_pelatihan'] }}% belum mengikuti pelatihan"
                color="var(--color-danger)"
            />
        </div>

        {{-- ================= Kesimpulan naratif ================= --}}
        <div class="plh-insight">
            <div class="plh-insight-icon"><i class="fa-solid fa-lightbulb"></i></div>
            <div>
                <div class="plh-insight-label">Kesimpulan</div>
                <p class="plh-insight-text">{{ $kesimpulan }}</p>
            </div>
        </div>

        {{-- ================= Grafik ================= --}}
        <div class="plh-chart-grid">
            <div class="card-base plh-chart-card">
                <div class="card-header">
                    <div>
                        <div class="card-title">Rata-rata Jam Pelatihan per Unit</div>
                        <div class="card-subtitle">Unit dengan rata-rata jam tertinggi</div>
                    </div>
                </div>
                @if (count($chartRataRataPerUnit['labels']))
                    <div data-chart-type="bar-horizontal" data-chart='@json($chartRataRataPerUnit)'></div>
                @else
                    <div class="plh-rank-empty">Belum ada data rata-rata jam per unit.</div>
                @endif
            </div>

            <div class="card-base plh-chart-card">
                <div class="card-header">
                    <div>
                        <div class="card-title">Pegawai dengan Jam Pelatihan Tertinggi</div>
                        <div class="card-subtitle">Top {{ count($topPegawai) }} pegawai, lintas semua unit</div>
                    </div>
                </div>
                @if (count($topPegawai))
                    <div data-chart-type="bar-horizontal" data-chart='@json($chartTopPegawai)'></div>
                @else
                    <div class="plh-rank-empty">Belum ada pegawai dengan jam pelatihan tercatat.</div>
                @endif
            </div>
        </div>

        {{-- ================= Divider & toolbar ================= --}}
        <div class="plh-detail-divider">
            <span>Detail per unit</span>
        </div>

        <div class="plh-toolbar">
            <div class="plh-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="plhSearch" placeholder="Cari nama unit / pegawai...">
            </div>

            <div class="plh-bulk-actions">
                <button type="button" class="plh-bulk-btn" data-bulk="expand">
                    <i class="fa-solid fa-square-caret-down"></i> Buka semua
                </button>
                <button type="button" class="plh-bulk-btn" data-bulk="collapse">
                    <i class="fa-solid fa-square-caret-up"></i> Tutup semua
                </button>
            </div>
        </div>

        <div class="plh-empty-filter" data-empty-filter hidden>
            Tidak ada unit / pegawai yang cocok dengan pencarian saat ini.
        </div>

        {{-- ================= Daftar unit (accordion) ================= --}}
        <div class="plh-unit-list" data-accordion>
            @foreach ($ringkasan as $unit)
                <div id="unit-{{ $unit['slug'] }}" class="plh-unit card-base"
                    style="animation-delay: {{ $loop->index * 40 }}ms"
                    data-search="{{ strtolower($unit['unit']) }}">
                    <button type="button" class="plh-unit-head" data-accordion-trigger>
                        <div class="plh-unit-title">
                            <span class="plh-unit-name">{{ $unit['unit'] }}</span>
                            <span class="plh-unit-count">{{ $unit['summary']['total_pegawai'] }} pegawai</span>
                            <x-badge variant="success">
                                {{ $unit['summary']['jumlah_20jp_plus'] }} ≥20JP
                            </x-badge>
                            <x-badge variant="danger">
                                {{ $unit['summary']['jumlah_kurang_20jp'] }} &lt;20JP
                            </x-badge>
                            <x-badge variant="neutral">
                                Rata-rata {{ $unit['summary']['rata_rata_jam_per_pegawai'] }} jam
                            </x-badge>
                        </div>
                        <i class="fa-solid fa-chevron-down plh-chev" aria-hidden="true"></i>
                    </button>

                    <div class="plh-body" data-accordion-panel>
                        <div class="plh-body-inner">
                            <div class="plh-pegawai-grid">
                                @foreach ($unit['pegawai'] as $p)
                                    <div class="plh-pegawai-card"
                                        data-plh-pegawai
                                        data-search="{{ strtolower($p['nama']) }}">
                                        <div class="plh-pegawai-top">
                                            <div class="plh-avatar">{{ $p['inisial'] }}</div>
                                            <div class="plh-pegawai-info">
                                                <div class="plh-pegawai-name">{{ $p['nama'] }}</div>
                                                <div class="plh-pegawai-meta">{{ $p['jabatan'] }}</div>
                                            </div>
                                        </div>
                                        <div class="plh-pegawai-stats">
                                            <div class="plh-pegawai-stat">
                                                <span class="plh-pegawai-stat-value">{{ $p['jumlah_pelatihan'] }}</span>
                                                <span class="plh-pegawai-stat-label">pelatihan</span>
                                            </div>
                                            <div class="plh-pegawai-stat">
                                                <span class="plh-pegawai-stat-value">{{ number_format($p['total_jam_pelatihan'], 0, ',', '.') }}</span>
                                                <span class="plh-pegawai-stat-label">jam total</span>
                                            </div>
                                            <div class="plh-pegawai-stat">
                                                <span class="plh-pegawai-stat-value">{{ $p['rata_rata_jam'] }}</span>
                                                <span class="plh-pegawai-stat-label">jam/pelatihan</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

</x-app-layout>