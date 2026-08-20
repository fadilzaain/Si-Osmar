# SI-OSMAR

Sistem Informasi Manajemen SDM - RSUD Jombang.

Web Dashboard SDM rsud jombang: kelengkapan dokumen legalitas profesi (STR/SIP), kecukupan tenaga per unit (bezetting), dan ringkasan umum lewat satu halaman dashboard.

## Tech Stack

- **Backend:** Laravel, Blade
- **Frontend build:** Vite, Tailwind CSS v4
- **Chart:** ApexCharts
- **UI tambahan:** Bootstrap 5, jQuery, DataTables.net, SweetAlert2, AOS, GSAP
- **Auth:** Session-based (Laravel bawaan), dengan throttle brute-force di login

```

## Instalasi

```bash
git clone https://github.com/fadilzaain/Si-Osmar.git
cd Si-Osmar

composer install
npm install

cp .env.example .env
php artisan key:generate

# sesuaikan koneksi DB & variabel SIKAWAN di .env (lihat bagian di bawah)

php artisan migrate --seed

npm run dev       # untuk development (Vite watch)
# atau
npm run build      # untuk production build

php artisan serve
```

## Environment Variables

Selain variabel standar Laravel (`DB_*`, `APP_*`), aplikasi ini butuh konfigurasi API SIKAWAN untuk modul SDM Bezetting:

```env
SIKAWAN_BASE_URL=https://new-sikawan.rsudjombang.id
SIKAWAN_BEZETTING_ENDPOINT=/api-monitoring-sdm
SIKAWAN_TIMEOUT=10
SIKAWAN_CACHE_TTL=900
```

## Struktur Folder

```
app/
  Http/Controllers/     Controller tiap modul (tipis, orkestrasi saja)
  Services/              Logika bisnis: query, kalkulasi, integrasi API
  Models/                Eloquent model (Pegawai & Mutasi deprecated, lihat docblock)
resources/
  views/                 Blade templates + komponen (resources/views/components)
  js/modules/            JS per fitur (chart, sidebar, accordion, tema, dsb.)
  css/                   Tailwind v4 + custom properties
database/
  migrations/            Skema tabel
routes/
  web.php                Semua route aplikasi
```
