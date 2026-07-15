# SI-OSMAR

Sistem Informasi Manajemen SDM — RSUD Jombang.

Web Dashboard SDM rsud jombang: kelengkapan dokumen legalitas profesi (STR/SIP), kecukupan tenaga per unit (bezetting), dan ringkasan umum lewat satu halaman dashboard.

## Daftar Isi

- [Modul](#modul)
- [Tech Stack](#tech-stack)
- [Arsitektur](#arsitektur)
- [Instalasi](#instalasi)
- [Environment Variables](#environment-variables)
- [Struktur Folder](#struktur-folder)
- [Status Data (Real vs Dummy)](#status-data-real-vs-dummy)
- [Catatan Pengembangan](#catatan-pengembangan)

## Modul

| Modul | Route | Deskripsi |
|---|---|---|
| Dashboard Utama | `/dashboard` | Ringkasan gabungan semua modul: KPI, chart, insight, aktivitas terbaru |
| Monitoring STR & SIP | `/monitoring-str-sip` | Status kelengkapan & masa berlaku dokumen (STR, SIP, SPK, RKK) per ruangan |
| SDM Bezetting | `/sdm-bezetting` | Perbandingan jumlah pegawai riil vs kebutuhan per unit/jabatan + rekomendasi redistribusi antar unit |

## Tech Stack

- **Backend:** Laravel, Blade
- **Frontend build:** Vite, Tailwind CSS v4
- **Chart:** ApexCharts
- **UI tambahan:** Bootstrap 5, jQuery, DataTables.net, SweetAlert2, AOS, GSAP
- **Auth:** Session-based (Laravel bawaan), dengan throttle brute-force di login

## Arsitektur

Alur tiap request mengikuti pola berlapis:

```
Route (routes/web.php)
   -> Controller (app/Http/Controllers)      orkestrasi saja, tanpa query langsung
   -> Service (app/Services)                 semua logika bisnis: query, kalkulasi, panggil API eksternal
   -> Model (Eloquent) / Http::get() ke API   sumber data mentah
   -> View Blade (resources/views)
   -> JS Module (resources/js/modules)        render chart & interaksi UI
```

Modul **SDM Bezetting** (`BezettingApiService`) menarik data dari API eksternal **SIKAWAN** (bukan database lokal), dengan cache 15 menit dan fallback aman kalau API sedang down — lihat komentar di dalam file service untuk detail.

Model `Pegawai` dan `Mutasi` **sudah deprecated** (ditandai langsung di kode) sejak keputusan arsitektur "full-API" — tabelnya kosong permanen dan tidak boleh dipakai lagi di kode baru.

Dokumentasi lengkap soal logika tiap modul, query, dan alur data ada di file terpisah: `docs/LOGIKA-SISTEM.docx` (kalau sudah ditambahkan ke repo).

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

> Verifikasi SSL otomatis nonaktif hanya saat `APP_ENV=local` (untuk setup Laragon/XAMPP yang belum punya CA bundle). Pastikan tetap aktif di staging/production.

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

## Status Data (Real vs Dummy)

Beberapa data masih berupa placeholder sambil menunggu sumber aslinya siap — penting diketahui sebelum demo/presentasi:

| Data / Fitur | Status | Sumber saat ini |
|---|---|---|
| SDM Bezetting (semua angka & redistribusi) | **Real** | API SIKAWAN, live + cache 15 menit |
| Status dokumen (berlaku/akan kadaluarsa/kadaluarsa) | **Real** (logikanya) | Dihitung real-time dari tanggal, walau data tanggalnya sendiri masih dummy |
| Monitoring STR & SIP (ringkasan & detail per ruangan) | Dummy | Array hardcoded di `MonitoringDokumenService` |
| Ekinerja, Pelatihan, SDM chart, Cuti (di Dashboard) | Dummy | Angka statis di `DashboardService`, ditandai komentar `// dummy` |
| Model `Pegawai`, `Mutasi` (tabel lokal) | Tidak dipakai | Deprecated, digantikan API SIKAWAN |

Rencana ke depan: menyambungkan Monitoring STR/SIP dan ringkasan Dashboard ke sumber data asli, mengikuti pola yang sudah terbukti di `BezettingApiService` (service khusus + caching + fallback aman).

## Catatan Pengembangan

- Controller **tidak boleh** berisi query atau kalkulasi langsung — semua masuk ke Service.
- Jangan panggil model `Pegawai` / `Mutasi` di kode baru — sudah deprecated.
- Kalau menambah modul baru yang narik dari API eksternal, ikuti pola `BezettingApiService`: cache + timeout + fallback array kosong + log error, jangan biarkan halaman ikut error 500 kalau API sumber down.
- Warna chart diambil dari CSS custom property (`--color-*`), bukan hardcode hex — supaya otomatis ikut tema light/dark.


<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

