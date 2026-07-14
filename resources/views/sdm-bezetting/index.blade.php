<x-app-layout title="Bezetting SDM">

<div class="bzs-wrap" data-sdm-bezetting>
    <div class="bzs-eyebrow">SI-OSMAR / SDM</div>
    <h1 class="bzs-title">Bezetting & rotasi pegawai per unit</h1>
    <div class="bzs-sub">Perbandingan jumlah pegawai riil terhadap kebutuhan, per jabatan, di tiap unit / poli.</div>

    <div class="bzs-summary-bar">
        <div class="bzs-summary-item">
            <span class="bzs-summary-value">{{ $totalUnit }}</span>
            <span class="bzs-summary-label">Unit dipantau</span>
        </div>
        <div class="bzs-summary-item bzs-summary-item--danger">
            <span class="bzs-summary-value">{{ $totalUnitKurang }}</span>
            <span class="bzs-summary-label">Unit kekurangan SDM</span>
        </div>
    </div>

    @if (empty($ringkasan))
        <x-empty-state
            icon="fa-solid fa-server"
            title="Data bezetting belum bisa dimuat"
            description="API SIKAWAN sedang tidak bisa diakses. Coba muat ulang halaman beberapa saat lagi."
        />
    @else
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
                <div class="bzs-unit card-base"
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