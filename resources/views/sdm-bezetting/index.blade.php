<x-app-layout title="Bezetting SDM">

<div class="bzs-wrap">
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

        <div class="bzs-unit-list" data-accordion>
            @foreach ($ringkasan as $unit)
                @php
                    $badgeVariant = match ($unit['summary']['status']) {
                        'KURANG' => 'danger',
                        'LEBIH' => 'info',
                        default => 'success',
                    };
                @endphp
                <div class="bzs-unit card-base" data-aos="fade-up" data-aos-delay="{{ $loop->index * 40 }}">
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

                            {{-- ===== Aktivitas rotasi ===== --}}
                            <div class="bzs-section">
                                <div class="bzs-section-title">
                                    <i class="fa-solid fa-right-left"></i> Aktivitas rotasi pegawai
                                </div>
                                @if (empty($unit['rotasi']))
                                    <div class="bzs-rotasi-empty">Belum ada aktivitas rotasi tercatat untuk unit ini.</div>
                                @else
                                    <x-dashboard.activity-list :items="$unit['rotasi']" />
                                @endif
                            </div>

                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

</x-app-layout>
