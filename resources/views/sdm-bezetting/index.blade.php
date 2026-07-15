<x-app-layout title="Bezetting SDM">

<div class="bzs-wrap" data-sdm-bezetting>
    <div class="bzs-eyebrow">SI-OSMAR / SDM</div>
    <h1 class="bzs-title">Bezetting SDM</h1>
    <div class="bzs-sub">Ringkasan Kondisi SDM RSUD Jombang</div>

    @if (empty($ringkasan))
        <x-empty-state
            icon="fa-solid fa-server"
            title="Data bezetting belum bisa dimuat"
            description="API SIKAWAN sedang tidak bisa diakses. Coba muat ulang halaman beberapa saat lagi."
        />
    @else

        {{-- ================= Ringkasan eksekutif ================= --}}
        <div class="bzs-kpi-grid">
            <x-stat-card
                icon="fa-solid fa-user-slash"
                label="Total kekurangan SDM"
                :value="$eksekutif['total_kekurangan']"
                comparison="di {{ $eksekutif['total_unit_kurang'] }} dari {{ $eksekutif['total_unit'] }} unit"
                color="var(--color-danger)"
            />
            <x-stat-card
                icon="fa-solid fa-people-arrows"
                label="Bisa ditutup lewat pemindahan"
                :value="$eksekutif['total_bisa_redistribusi']"
                comparison="{{ $eksekutif['persen_bisa_redistribusi'] }}% dari total kekurangan"
                color="var(--color-info)"
            />
            <x-stat-card
                icon="fa-solid fa-user-plus"
                label="Perlu rekrutmen baru"
                :value="$eksekutif['sisa_butuh_rekrutmen']"
                comparison="sisa setelah rotasi internal"
                color="var(--color-warning)"
            />
            <x-stat-card
                icon="fa-solid fa-chart-pie"
                label="Bezetting terpenuhi"
                :value="$eksekutif['persen_terpenuhi'] . '%'"
                comparison="{{ number_format($eksekutif['total_pegawai']) }} / {{ number_format($eksekutif['total_kebutuhan']) }} orang"
                color="var(--color-primary)"
            />
        </div>

        {{-- ================= Kesimpulan naratif — inti dari halaman ini ================= --}}
        <div class="bzs-insight">
            <div class="bzs-insight-icon"><i class="fa-solid fa-lightbulb"></i></div>
            <div>
                <div class="bzs-insight-label">Kesimpulan</div>
                <p class="bzs-insight-text">{{ $kesimpulan }}</p>
            </div>
        </div>

        {{-- ================= Ranking: unit & jabatan paling kritis ================= --}}
        <div class="bzs-rank-grid">
            <div class="bzs-rank-card card-base">
                <div class="card-header">
                    <div>
                        <div class="card-title">Unit paling kritis</div>
                        <div class="card-subtitle">Diurutkan dari kekurangan terbanyak</div>
                    </div>
                </div>
                <div class="bzs-rank-body">
                    @forelse ($topUnitKritis as $u)
                        @php $maxUnit = $topUnitKritis[0]['summary']['total_kekurangan'] ?: 1; @endphp
                        <button type="button" class="bzs-rank-row" data-scroll-to="{{ $u['slug'] }}">
                            <div class="bzs-rank-info">
                                <span class="bzs-rank-name">{{ $u['unit'] }}</span>
                                <span class="bzs-rank-value">Kurang {{ $u['summary']['total_kekurangan'] }}</span>
                            </div>
                            <div class="bzs-rank-bar-track">
                                <div class="bzs-rank-bar-fill tone-danger" style="width: {{ round($u['summary']['total_kekurangan'] / $maxUnit * 100) }}%"></div>
                            </div>
                        </button>
                    @empty
                        <div class="bzs-rank-empty">Semua unit terpenuhi kebutuhannya.</div>
                    @endforelse
                </div>
            </div>

            <div class="bzs-rank-card card-base">
                <div class="card-header">
                    <div>
                        <div class="card-title">Jabatan paling kritis</div>
                        <div class="card-subtitle">Total kekurangan lintas seluruh unit</div>
                    </div>
                </div>
                <div class="bzs-rank-body">
                    @forelse ($topJabatanKritis as $j)
                        @php $maxJabatan = $topJabatanKritis[0]['total_kekurangan'] ?: 1; @endphp
                        <div class="bzs-rank-row bzs-rank-row--static">
                            <div class="bzs-rank-info">
                                <span class="bzs-rank-name">{{ $j['jabatan'] }}</span>
                                <span class="bzs-rank-value">Kurang {{ $j['total_kekurangan'] }} · {{ $j['jumlah_unit_terdampak'] }} unit</span>
                            </div>
                            <div class="bzs-rank-bar-track">
                                <div class="bzs-rank-bar-fill tone-warning" style="width: {{ round($j['total_kekurangan'] / $maxJabatan * 100) }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="bzs-rank-empty">Tidak ada jabatan yang kekurangan saat ini.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ================= Peluang Rotasi — lintas semua unit ================= --}}
        <div class="bzs-rank-card card-base bzs-redis-global">
            <div class="card-header">
                <div>
                    <div class="card-title">Peluang rotasi pegawai</div>
                    <div class="card-subtitle">Jabatan yang kurang di satu unit, tapi lebih di unit lain </div>
                </div>
            </div>
            <div class="bzs-rank-body">
                @forelse ($peluangRedistribusiGlobal as $p)
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
                @empty
                    <div class="bzs-rank-empty">Belum ada peluang redistribusi yang cocok saat ini.</div>
                @endforelse
            </div>
        </div>

        {{-- ================= Detail per unit — drill-down, bukan konten utama ================= --}}
        <div class="bzs-detail-divider">
            <span>Detail lengkap per unit</span>
        </div>

        <div class="bzs-legend">
            <span class="bzs-legend-item"><x-badge variant="danger">KURANG</x-badge> tenaga di bawah kebutuhan</span>
            <span class="bzs-legend-item"><x-badge variant="success">SESUAI</x-badge> tenaga sesuai kebutuhan</span>
            <span class="bzs-legend-item"><x-badge variant="info">LEBIH</x-badge> tenaga melebihi kebutuhan</span>
        </div>

        {{-- ===== Toolbar: search + filter + bulk expand/collapse ===== --}}
        <div class="bzs-toolbar">
            <div class="bzs-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="bzsSearch" placeholder="Cari nama unit / ruangan...">
            </div>

            <div class="bzs-filter-pills">
                <button type="button" class="bzs-pill active" data-filter="semua">
                    Semua <span>{{ $totalUnit }}</span>
                </button>
                <button type="button" class="bzs-pill bzs-pill--danger" data-filter="KURANG">
                    Kurang <span>{{ $totalUnitKurang }}</span>
                </button>
                <button type="button" class="bzs-pill bzs-pill--success" data-filter="SESUAI">
                    Sesuai <span data-count-for="SESUAI">0</span>
                </button>
                <button type="button" class="bzs-pill bzs-pill--info" data-filter="LEBIH">
                    Lebih <span data-count-for="LEBIH">0</span>
                </button>
            </div>

            <div class="bzs-bulk-actions">
                <button type="button" class="bzs-bulk-btn" data-bulk="expand">
                    <i class="fa-solid fa-square-caret-down"></i> Buka semua
                </button>
                <button type="button" class="bzs-bulk-btn" data-bulk="collapse">
                    <i class="fa-solid fa-square-caret-up"></i> Tutup semua
                </button>
            </div>
        </div>

        <div class="bzs-empty-filter" data-empty-filter hidden>
            Tidak ada unit yang cocok dengan pencarian / filter saat ini.
        </div>

        <div class="bzs-unit-list" data-accordion>
            @foreach ($ringkasan as $unit)
                @php
                    $badgeVariant = match ($unit['summary']['status']) {
                        'KURANG' => 'danger',
                        'LEBIH' => 'info',
                        default => 'success',
                    };
                    // Skor urgensi buat sorting default: unit KURANG paling parah muncul paling atas.
                    $severity = match ($unit['summary']['status']) {
                        'KURANG' => 1000 + ($unit['summary']['total_kekurangan'] ?? 0),
                        'SESUAI' => 100,
                        'LEBIH' => 0,
                        default => 50,
                    };
                @endphp
                <div id="unit-{{ $unit['slug'] }}" class="bzs-unit card-base"
                     data-aos="fade-up" data-aos-delay="{{ $loop->index * 40 }}"
                     data-status="{{ $unit['summary']['status'] }}"
                     data-severity="{{ $severity }}"
                     data-search="{{ strtolower($unit['unit']) }}">
                    <button type="button" class="bzs-unit-head" data-accordion-trigger>
                        <div class="bzs-unit-title">
                            <span class="bzs-unit-name">{{ $unit['unit'] }}</span>
                            <span class="bzs-unit-count">{{ $unit['summary']['total_pegawai'] }} / {{ $unit['summary']['total_kebutuhan'] }} orang</span>
                            <x-badge :variant="$badgeVariant">
                                @if ($unit['summary']['status'] === 'KURANG')
                                    Kurang {{ $unit['summary']['total_kekurangan'] }}
                                @elseif ($unit['summary']['status'] === 'LEBIH')
                                    Lebih {{ $unit['summary']['total_lebih'] }}
                                @else
                                    Sesuai
                                @endif
                            </x-badge>
                        </div>
                        <i class="fa-solid fa-chevron-down bzs-chev" aria-hidden="true"></i>
                    </button>

                    <div class="bzs-body" data-accordion-panel>
                        <div class="bzs-body-inner">

                            {{-- ===== Bezetting ===== --}}
                            <div class="bzs-section">
                                <div class="bzs-section-title">
                                    <i class="fa-solid fa-users-viewfinder"></i> Bezetting per jabatan
                                </div>
                                <div class="bzs-table-scroll">
                                    <table class="bzs-table">
                                        <thead>
                                            <tr>
                                                <th>Jabatan</th>
                                                <th class="bzs-col-num">PNS</th>
                                                <th class="bzs-col-num">PPPK</th>
                                                <th class="bzs-col-num">PPPK-PW</th>
                                                <th class="bzs-col-num">Non ASN</th>
                                                <th class="bzs-col-num">Jumlah</th>
                                                <th class="bzs-col-num">Kebutuhan</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($unit['rows'] as $row)
                                                <tr>
                                                    <td>
                                                        <div class="bzs-jabatan">{{ $row['jabatan'] }}</div>
                                                        @if (!empty($row['kualifikasi']))
                                                            <div class="bzs-kualifikasi">{{ $row['kualifikasi'] }}</div>
                                                        @endif
                                                    </td>
                                                    <td class="bzs-col-num">{{ $row['pns'] }}</td>
                                                    <td class="bzs-col-num">{{ $row['pppk'] }}</td>
                                                    <td class="bzs-col-num">{{ $row['pppk_pw'] }}</td>
                                                    <td class="bzs-col-num">{{ $row['non_asn'] }}</td>
                                                    <td class="bzs-col-num bzs-col-strong">{{ $row['jumlah'] }}</td>
                                                    <td class="bzs-col-num">{{ $row['kebutuhan'] }}</td>
                                                    <td>
                                                        <x-badge :variant="match ($row['keterangan']) {
                                                            'KURANG' => 'danger',
                                                            'LEBIH' => 'info',
                                                            default => 'success',
                                                        }">{{ $row['keterangan'] }}</x-badge>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- ===== Peluang redistribusi ===== --}}
                            <div class="bzs-section">
                                <div class="bzs-section-title">
                                    <i class="fa-solid fa-right-left"></i> Peluang redistribusi
                                </div>
                                @include('sdm-bezetting.partials.redistribusi-list', ['items' => $unit['redistribusi']])
                            </div>

                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

</x-app-layout>
