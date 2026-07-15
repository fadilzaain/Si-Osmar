<x-app-layout title="Monitoring Dokumen">

<div class="mds-wrap" data-monitoring-dokumen>
    <div class="mds-eyebrow">SI-OSMAR / SDM</div>
    <h1 class="mds-title">Monitoring Dokumen Legal</h1>
    <div class="mds-sub">Kelengkapan SIP, SPK & RKK seluruh pegawai per unit</div>

    @if (empty($unitList))
        <x-empty-state
            icon="fa-solid fa-file-circle-exclamation"
            title="Data dokumen belum bisa dimuat"
            description="API SIKAWAN sedang tidak bisa diakses. Coba muat ulang halaman beberapa saat lagi."
        />
    @else

        {{-- ================= Ringkasan eksekutif ================= --}}
        <div class="mds-kpi-grid">
            <x-stat-card
                icon="fa-solid fa-users"
                label="Pegawai dipantau"
                :value="$eksekutif['total_pegawai']"
                comparison="di {{ $eksekutif['total_unit'] }} unit"
                color="var(--color-primary)"
            />
            <x-stat-card
                icon="fa-solid fa-triangle-exclamation"
                label="Pegawai bermasalah"
                :value="$eksekutif['total_bermasalah']"
                comparison="di {{ $eksekutif['total_unit_bermasalah'] }} unit"
                color="var(--color-danger)"
            />
            <x-stat-card
                icon="fa-solid fa-file-circle-xmark"
                label="Dokumen kadaluarsa"
                :value="$eksekutif['total_dokumen_kadaluarsa']"
                comparison="perlu perpanjangan segera"
                color="var(--color-warning)"
            />
            <x-stat-card
                icon="fa-solid fa-file-circle-question"
                label="Belum diunggah"
                :value="$eksekutif['total_dokumen_belum_ada']"
                comparison="dokumen belum tersedia"
                color="var(--color-info)"
            />
        </div>

        {{-- ================= Kesimpulan naratif ================= --}}
        <div class="mds-insight">
            <div class="mds-insight-icon"><i class="fa-solid fa-lightbulb"></i></div>
            <div>
                <div class="mds-insight-label">Kesimpulan</div>
                <p class="mds-insight-text">{{ $kesimpulan }}</p>
            </div>
        </div>

        {{-- ================= Ranking unit paling kritis ================= --}}
        <div class="mds-rank-card card-base">
            <div class="card-header">
                <div>
                    <div class="card-title">Unit paling kritis</div>
                    <div class="card-subtitle">Diurutkan dari jumlah pegawai bermasalah terbanyak</div>
                </div>
            </div>
            <div class="mds-rank-body">
                @forelse ($topUnitKritis as $u)
                    @php $maxUnit = $topUnitKritis[0]['summary']['bermasalah'] ?: 1; @endphp
                    <button type="button" class="mds-rank-row" data-scroll-to="{{ $u['slug'] }}">
                        <div class="mds-rank-info">
                            <span class="mds-rank-name">{{ $u['unit'] }}</span>
                            <span class="mds-rank-value">{{ $u['summary']['bermasalah'] }} / {{ $u['summary']['total_pegawai'] }} bermasalah</span>
                        </div>
                        <div class="mds-rank-bar-track">
                            <div class="mds-rank-bar-fill tone-danger" style="width: {{ round($u['summary']['bermasalah'] / $maxUnit * 100) }}%"></div>
                        </div>
                    </button>
                @empty
                    <div class="mds-rank-empty">Semua unit sudah lengkap dokumennya.</div>
                @endforelse
            </div>
        </div>

        {{-- ================= Detail per unit ================= --}}
        <div class="mds-detail-divider">
            <span>Detail lengkap per unit</span>
        </div>

        <div class="mds-legend">
            <span class="mds-legend-item"><x-badge variant="danger">Kadaluarsa</x-badge></span>
            <span class="mds-legend-item"><x-badge variant="warning">Segera kadaluarsa</x-badge></span>
            <span class="mds-legend-item"><x-badge variant="neutral">Belum ada</x-badge></span>
            <span class="mds-legend-item"><x-badge variant="success">Berlaku</x-badge></span>
        </div>

        <div class="mds-toolbar">
            <div class="mds-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="mdsSearch" placeholder="Cari nama pegawai / unit...">
            </div>

            <div class="mds-filter-pills">
                <button type="button" class="mds-pill active" data-filter="semua">
                    Semua <span>{{ count($unitList) }}</span>
                </button>
                <button type="button" class="mds-pill mds-pill--danger" data-filter="danger">
                    Kadaluarsa <span data-count-for="danger">0</span>
                </button>
                <button type="button" class="mds-pill mds-pill--warning" data-filter="warning">
                    Segera kadaluarsa <span data-count-for="warning">0</span>
                </button>
                <button type="button" class="mds-pill mds-pill--neutral" data-filter="neutral">
                    Belum ada <span data-count-for="neutral">0</span>
                </button>
            </div>

            <div class="mds-bulk-actions">
                <button type="button" class="mds-bulk-btn" data-bulk="expand">
                    <i class="fa-solid fa-square-caret-down"></i> Buka semua
                </button>
                <button type="button" class="mds-bulk-btn" data-bulk="collapse">
                    <i class="fa-solid fa-square-caret-up"></i> Tutup semua
                </button>
            </div>
        </div>

        <div class="mds-empty-filter" data-empty-filter hidden>
            Tidak ada unit yang cocok dengan pencarian / filter saat ini.
        </div>

        <div class="mds-unit-list" data-accordion>
            @foreach ($unitList as $unit)
                @php
                    $statusTags = collect($unit['pegawai'])->pluck('overall_status')->unique()->values()->all();
                    $searchNama = collect($unit['pegawai'])->pluck('nama')->implode(' ');
                @endphp
                <div id="unit-{{ $unit['slug'] }}" class="mds-unit card-base"
                     data-aos="fade-up" data-aos-delay="{{ $loop->index * 40 }}"
                     data-status="{{ implode(' ', $statusTags) }}"
                     data-search="{{ strtolower($unit['unit'] . ' ' . $searchNama) }}">
                    <button type="button" class="mds-unit-head" data-accordion-trigger>
                        <div class="mds-unit-title">
                            <span class="mds-unit-name">{{ $unit['unit'] }}</span>
                            <span class="mds-unit-count">{{ $unit['summary']['total_pegawai'] }} pegawai</span>
                            @if ($unit['summary']['bermasalah'] > 0)
                                <x-badge variant="danger">{{ $unit['summary']['bermasalah'] }} bermasalah</x-badge>
                            @else
                                <x-badge variant="success">Lengkap</x-badge>
                            @endif
                        </div>
                        <i class="fa-solid fa-chevron-down mds-chev" aria-hidden="true"></i>
                    </button>

                    <div class="mds-body" data-accordion-panel>
                        <div class="mds-body-inner">
                            <div class="mds-table-scroll">
                                <table class="mds-table">
                                    <thead>
                                        <tr>
                                            <th>Pegawai</th>
                                            <th>SIP</th>
                                            <th>SPK</th>
                                            <th>RKK</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($unit['pegawai'] as $p)
                                            <tr>
                                                <td>
                                                    <div class="mds-person">
                                                        <div class="mds-avatar">{{ $p['inisial'] }}</div>
                                                        <div>
                                                            <div class="mds-pname">{{ $p['nama'] }}</div>
                                                            <div class="mds-prole">{{ $p['jabatan'] }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><x-monitoring-dokumen.dokumen-cell :dokumen="$p['dokumen']['SIP']" /></td>
                                                <td><x-monitoring-dokumen.dokumen-cell :dokumen="$p['dokumen']['SPK']" /></td>
                                                <td><x-monitoring-dokumen.dokumen-cell :dokumen="$p['dokumen']['RKK']" /></td>
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

@if ($ruanganAktifSlug)
    <script>window.__mdsRuanganAktifSlug = @json($ruanganAktifSlug);</script>
@endif

</x-app-layout>