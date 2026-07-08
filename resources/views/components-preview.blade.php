<x-app-layout title="Component Preview">
    <div class="d-flex flex-column gap-4">

        <div class="card-base card-body">
            <div class="card-title">Button</div>
            <div class="d-flex gap-2" style="margin-top: 12px">
                <x-button variant="primary">Primary</x-button>
                <x-button variant="secondary">Secondary</x-button>
                <x-button variant="outline">Outline</x-button>
                <x-button variant="ghost">Ghost</x-button>
                <x-button variant="danger">Danger</x-button>
                <x-button variant="primary" :loading="true">Loading</x-button>
            </div>
        </div>

        <div class="card-base card-body">
            <div class="card-title">Badge</div>
            <div class="d-flex gap-2" style="margin-top: 12px">
                <x-badge variant="success">Aktif</x-badge>
                <x-badge variant="warning">Pending</x-badge>
                <x-badge variant="danger">Nonaktif</x-badge>
                <x-badge variant="info">Info</x-badge>
                <x-badge variant="neutral">Netral</x-badge>
            </div>
        </div>

        <div class="row" style="display:flex; gap: 16px;">
            <x-stat-card icon="fa-solid fa-users" label="Total Pegawai" value="1.481" trend="2,5%" trendType="up" comparison="dari Apr 2025" color="#15803D" sparkline="0,20 20,18 40,15 60,10 80,8 100,5" />
            <x-stat-card icon="fa-solid fa-id-badge" label="ASN" value="370" trend="1,1%" trendType="up" comparison="dari Apr 2025" color="#2563EB" sparkline="0,15 20,17 40,12 60,14 80,10 100,8" />
        </div>

        <x-chart-card title="Trend Jumlah Pegawai" subtitle="12 Bulan Terakhir" chartId="chart-preview" />

        <x-filter-panel>
            <div class="filter-field">
                <label class="filter-label">Periode</label>
                <select class="form-select"><option>Mei 2025</option></select>
            </div>
            <x-slot:actions>
                <x-button variant="outline" size="sm">Reset</x-button>
                <x-button variant="primary" size="sm">Filter</x-button>
            </x-slot:actions>
        </x-filter-panel>

        <x-button data-bs-toggle="modal" data-bs-target="#modalPreview">Buka Modal</x-button>
        <x-modal id="modalPreview" title="Contoh Modal">
            <p>Ini isi modal contoh.</p>
            <x-slot:footer>
                <x-button variant="ghost" data-bs-dismiss="modal">Batal</x-button>
                <x-button variant="primary">Simpan</x-button>
            </x-slot:footer>
        </x-modal>

        <x-data-table id="table-preview" :columns="['Nama', 'Unit', 'Status']">
            <tr><td>Andi Kurniawan</td><td>Radiologi</td><td><x-badge variant="success">Aktif</x-badge></td></tr>
            <tr><td>Siti Rahmawati</td><td>ICU</td><td><x-badge variant="warning">Cuti</x-badge></td></tr>
        </x-data-table>

        <x-empty-state icon="fa-solid fa-user-slash" title="Belum ada pegawai" description="Data untuk unit ini belum tersedia.">
            <x-button variant="primary" size="sm">Tambah Pegawai</x-button>
        </x-empty-state>

        <div class="card-base card-body d-flex flex-column gap-3">
            <div class="card-title">Skeleton</div>
            <x-skeleton type="text" count="3" />
            <x-skeleton type="avatar" />
            <x-skeleton type="card" />
        </div>

    </div>
</x-app-layout>