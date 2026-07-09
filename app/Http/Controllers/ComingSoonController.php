<?php

namespace App\Http\Controllers;

class ComingSoonController extends Controller
{
    protected array $modules = [
        // dari card Dashboard Executive
        'ekinerja' => ['title' => 'Ekinerja', 'subtitle' => 'Distribusi capaian kinerja pegawai', 'icon' => 'fa-solid fa-chart-line'],
        'pelatihan' => ['title' => 'Pelatihan', 'subtitle' => 'Jam pelatihan dan sertifikasi pegawai', 'icon' => 'fa-solid fa-graduation-cap'],
        'sdm-ringkasan' => ['title' => 'SDM', 'subtitle' => 'Distribusi tenaga per kategori dan shift', 'icon' => 'fa-solid fa-users'],
        'cuti' => ['title' => 'Cuti', 'subtitle' => 'Rekap pegawai cuti per bulan', 'icon' => 'fa-solid fa-umbrella-beach'],

        // dari sidebar (6 fitur roadmap, minus Monitoring STR & SIP yang udah ada)
        'profil-sdm' => ['title' => 'Profil SDM Terintegrasi', 'subtitle' => 'Data lengkap & mutakhir tiap pegawai', 'icon' => 'fa-solid fa-id-card-clip'],
        'pemetaan-kompetensi' => ['title' => 'Pemetaan Kompetensi', 'subtitle' => 'Data kompetensi, sertifikat, pelatihan, riwayat karier', 'icon' => 'fa-solid fa-award'],
        'distribusi-sdm' => ['title' => 'Distribusi SDM', 'subtitle' => 'Pemetaan tenaga antar unit secara real-time', 'icon' => 'fa-solid fa-map-location-dot'],
        'analisis-beban-kerja' => ['title' => 'Analisis Beban Kerja', 'subtitle' => 'Perhitungan ABK & rekomendasi kebutuhan tenaga', 'icon' => 'fa-solid fa-scale-balanced'],
        'laporan-evaluasi' => ['title' => 'Laporan & Evaluasi', 'subtitle' => 'Laporan otomatis untuk monitoring & evaluasi', 'icon' => 'fa-solid fa-file-lines'],
    ];

    public function show(string $module)
    {
        abort_unless(isset($this->modules[$module]), 404);

        return view('coming-soon', $this->modules[$module]);
    }
}