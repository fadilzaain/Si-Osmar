<x-app-layout title="Dashboard">

    {{-- ==================== ROW 1: STAT CARDS ==================== --}}
    <div class="stat-grid" data-aos="fade-up">
        <x-stat-card
            icon="fa-solid fa-users"
            label="Total Pegawai"
            :value="$metrics['total_pegawai']['value']"
            :trend="$metrics['total_pegawai']['trend']"
            :trendType="$metrics['total_pegawai']['trend_type']"
            :comparison="$metrics['total_pegawai']['comparison']"
            color="#15803D"
        />
        <x-stat-card
            icon="fa-solid fa-id-card"
            label="ASN (PNS & PPPK)"
            :value="$metrics['asn']['value']"
            :trend="$metrics['asn']['trend']"
            :trendType="$metrics['asn']['trend_type']"
            :comparison="$metrics['asn']['comparison']"
            color="#2563EB"
        />
        <x-stat-card
            icon="fa-solid fa-user-clock"
            label="Non-ASN"
            :value="$metrics['non_asn']['value']"
            :trend="$metrics['non_asn']['trend']"
            :trendType="$metrics['non_asn']['trend_type']"
            :comparison="$metrics['non_asn']['comparison']"
            color="#F59E0B"
        />
        <x-stat-card
            icon="fa-solid fa-star"
            label="Rata-rata Skor Kompetensi"
            :value="$metrics['avg_kompetensi']['value']"
            :trend="$metrics['avg_kompetensi']['trend']"
            :trendType="$metrics['avg_kompetensi']['trend_type']"
            :comparison="$metrics['avg_kompetensi']['comparison']"
            color="#22C55E"
        />
    </div>

    {{-- ==================== ROW 2: STATUS PEGAWAI (Aktif/Cuti/Nonaktif) ==================== --}}
    <div class="stat-grid stat-grid-3" data-aos="fade-up">
        <x-stat-card
            icon="fa-solid fa-user-check"
            label="Pegawai Aktif"
            :value="$metrics['status']['aktif']"
            color="#22C55E"
        />
        <x-stat-card
            icon="fa-solid fa-umbrella-beach"
            label="Sedang Cuti"
            :value="$metrics['status']['cuti']"
            color="#F59E0B"
        />
        <x-stat-card
            icon="fa-solid fa-user-xmark"
            label="Nonaktif"
            :value="$metrics['status']['nonaktif']"
            color="#EF4444"
        />
    </div>

    {{-- ==================== ROW 3: CHARTS ==================== --}}
    <div class="chart-grid" data-aos="fade-up">
        <x-chart-card
            title="Distribusi Staff"
            subtitle="Berdasarkan kategori jabatan"
            chartId="chart-distribution"
            :height="300"
        />
        <x-chart-card
            title="Tren Jumlah Pegawai"
            subtitle="6 bulan terakhir"
            chartId="chart-trend"
            :height="300"
        />
    </div>

    {{-- ==================== ROW 4: RECENT ACTIVITIES ==================== --}}
    <div class="card-base activity-card" data-aos="fade-up">
        <div class="card-header">
            <div class="card-title">Aktivitas Terbaru</div>
        </div>

        <div class="activity-list">
            @forelse ($activities as $activity)
                <div class="activity-item">
                    <div class="activity-icon" style="--activity-color: {{ $activity['color'] }}">
                        <i class="{{ $activity['icon'] }}"></i>
                    </div>
                    <div class="activity-body">
                        <div class="activity-title">{{ $activity['title'] }}</div>
                        <div class="activity-desc">{{ $activity['desc'] }}</div>
                    </div>
                    {{-- $activity['time'] sudah objek Carbon (hasil casting di model), jadi bisa langsung format --}}
                    <div class="activity-time">{{ $activity['time']->translatedFormat('d M Y') }}</div>
                </div>
            @empty
                <x-empty-state
                    icon="fa-solid fa-inbox"
                    title="Belum ada aktivitas"
                    description="Aktivitas mutasi dan sertifikasi akan muncul di sini."
                />
            @endforelse
        </div>
    </div>

    @push('scripts')
        <script>
            // Data dikirim dari Controller (PHP) ke JS lewat json_encode.
            // Ini satu-satunya cara aman "nitip" data PHP ke ApexCharts tanpa fetch API terpisah.
            const distributionData = @json($distribution);
            const trendData = @json($trend);

            document.addEventListener('DOMContentLoaded', function () {
                // ---- Chart 1: Distribusi Staff (Donut) ----
                new ApexCharts(document.querySelector('#chart-distribution'), {
                    chart: { type: 'donut', height: 300, fontFamily: 'Inter, sans-serif' },
                    labels: Object.keys(distributionData),
                    series: Object.values(distributionData),
                    colors: ['#15803D', '#2563EB', '#F59E0B', '#06B6D4', '#64748B'],
                    legend: { position: 'bottom' },
                    dataLabels: { enabled: true },
                }).render();

                // ---- Chart 2: Tren Pegawai (Line) ----
                new ApexCharts(document.querySelector('#chart-trend'), {
                    chart: { type: 'line', height: 300, fontFamily: 'Inter, sans-serif', toolbar: { show: false } },
                    series: [
                        { name: 'Total', data: trendData.total },
                        { name: 'ASN', data: trendData.asn },
                        { name: 'Non-ASN', data: trendData.non_asn },
                    ],
                    xaxis: { categories: trendData.labels },
                    colors: ['#15803D', '#2563EB', '#F59E0B'],
                    stroke: { curve: 'smooth', width: 3 },
                    legend: { position: 'bottom' },
                }).render();
            });
        </script>
    @endpush

</x-app-layout>