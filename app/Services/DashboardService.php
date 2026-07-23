<?php

namespace App\Services;

use App\Models\Pegawai;
use App\Models\Pelatihan;
use App\Models\Mutasi;
use App\Models\Kompetensi;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getMetrics()
    {
        $totalPegawai = Pegawai::count();
        $asn = Pegawai::whereIn('status_kepegawaian', ['PNS', 'PPPK'])->count();
        $nonAsn = Pegawai::where('status_kepegawaian', 'Non-ASN')->count();
        $avgScore = round(Pegawai::avg('skor_kompetensi') ?? 0, 1);

        $aktif = Pegawai::where('status', 'Aktif')->count();
        $cuti = Pegawai::where('status', 'Cuti')->count();
        $nonaktif = Pegawai::where('status', 'Nonaktif')->count();

        
        // ini hardcode jangan sampai kebawa ke demo/produksi tanpa diganti data asli.
        return [
            'total_pegawai' => [
                'value' => $totalPegawai,
                'trend' => '3.4%',
                'trend_type' => 'up',
                'comparison' => 'vs bulan lalu',
            ],
            'asn' => [
                'value' => $asn,
                'trend' => '1.2%',
                'trend_type' => 'up',
                'comparison' => 'vs bulan lalu',
            ],
            'non_asn' => [
                'value' => $nonAsn,
                'trend' => '2.5%',
                'trend_type' => 'down',
                'comparison' => 'vs bulan lalu',
            ],
            'avg_kompetensi' => [
                'value' => $avgScore,
                'trend' => '0.8%',
                'trend_type' => 'up',
                'comparison' => 'vs kuartal lalu',
            ],
            'status' => [
                'aktif' => $aktif,
                'cuti' => $cuti,
                'nonaktif' => $nonaktif,
            ]
        ];
    }

    public function getStaffDistribution()
    {
        $pegawai = Pegawai::all();

        $categories = [
            'Dokter' => 0,
            'Perawat' => 0,
            'Bidan' => 0,
            'Penunjang Medis' => 0,
            'Administrasi' => 0,
        ];

        foreach ($pegawai as $p) {
            $jabatan = strtolower($p->jabatan);
            $unit = strtolower($p->unit_kerja);

            if (str_contains($jabatan, 'dokter')) {
                $categories['Dokter']++;
            } elseif (str_contains($jabatan, 'perawat')) {
                $categories['Perawat']++;
            } elseif (str_contains($jabatan, 'bidan')) {
                $categories['Bidan']++;
            } elseif (str_contains($jabatan, 'pranata laboratorium') || str_contains($jabatan, 'apoteker') || str_contains($jabatan, 'radiografer') || str_contains($unit, 'laboratorium') || str_contains($unit, 'farmasi') || str_contains($unit, 'radiologi')) {
                $categories['Penunjang Medis']++;
            } else {
                $categories['Administrasi']++;
            }
        }

        return $categories;
    }

    public function getStaffTrend()
    {
        // masih data dummy statis. Nanti ganti query agregat bulanan dari kolom tanggal_masuk
        return [
            'labels' => ['Jan 2026', 'Feb 2026', 'Mar 2026', 'Apr 2026', 'Mei 2026', 'Jun 2026'],
            'total'  => [10, 11, 13, 14, 15, 16],
            'asn'    => [7, 7, 7, 8, 9, 9],
            'non_asn'=> [3, 4, 6, 6, 6, 7]
        ];
    }

    public function getRecentActivities()
    {
        $activities = [];

        // Kalau ada input mundur (mutasi lama diinput belakangan), urutan lama jadi salah nongol di atas.
        $mutasis = Mutasi::with('pegawai')->orderByDesc('tanggal_mutasi')->take(3)->get();
        foreach ($mutasis as $m) {
            $activities[] = [
                'type' => 'mutasi',
                'icon' => 'fa-solid fa-right-left',
                'color' => '#3B82F6',
                'title' => 'Mutasi Pegawai: ' . $m->pegawai->nama,
                'desc' => "Mutasi {$m->jenis_mutasi} dari Unit {$m->unit_lama} ke {$m->unit_baru}.",
                'time' => $m->tanggal_mutasi,
            ];
        }

        // order by tanggal_terbit (bukan created_at)
        $kompetensis = Kompetensi::with('pegawai')->orderByDesc('tanggal_terbit')->take(3)->get();
        foreach ($kompetensis as $k) {
            $isExpired = $k->status === 'Kadaluarsa';
            $activities[] = [
                'type' => 'kompetensi',
                'icon' => $isExpired ? 'fa-solid fa-triangle-exclamation' : 'fa-solid fa-award',
                'color' => $isExpired ? '#EF4444' : '#10B981',
                'title' => ($isExpired ? 'Sertifikat Kedaluwarsa: ' : 'Sertifikat Baru: ') . $k->nama_sertifikat,
                'desc' => "Milik {$k->pegawai->nama} (Penerbit: {$k->penerbit}).",
                'time' => $k->tanggal_terbit,
            ];
        }

        // Catatan: 'time' di sini sekarang objek Carbon (karena udah di-cast di model),
        // jadi strcmp() di bawah perlu diganti ke perbandingan Carbon, bukan string.
        usort($activities, function ($a, $b) {
            return $b['time']->timestamp <=> $a['time']->timestamp;
        });

        return array_slice($activities, 0, 5);
    }

    public function getAlertsInsight()
    {
        $alerts = [];

        // Insight 1: Sertifikat/STR yang akan kadaluarsa dalam 3 bulan ke depan
        $akanKadaluarsa = Kompetensi::with('pegawai')->akanKadaluarsa(90)->count();
        if ($akanKadaluarsa > 0) {
            $alerts[] = [
                'type' => 'danger',
                'icon' => 'fa-solid fa-triangle-exclamation',
                'title' => 'Sertifikasi akan berakhir',
                'desc' => "{$akanKadaluarsa} pegawai sertifikasinya akan berakhir dalam 3 bulan",
                'badge' => $akanKadaluarsa,
            ];
        }

        // Insight 2: Sertifikat yang SUDAH lewat tanggal kadaluarsa (lebih urgent dari yang di atas)
        $sudahKadaluarsa = Kompetensi::sudahKadaluarsa()->count();
        if ($sudahKadaluarsa > 0) {
            $alerts[] = [
                'type' => 'danger',
                'icon' => 'fa-solid fa-circle-exclamation',
                'title' => 'Sertifikasi kedaluwarsa',
                'desc' => "{$sudahKadaluarsa} pegawai sertifikasinya sudah kedaluwarsa",
                'badge' => $sudahKadaluarsa,
            ];
        }

        // Insight 3: Mutasi terbaru (7 hari terakhir)
        $mutasiBaru = Mutasi::where('tanggal_mutasi', '>=', now()->subDays(7))->count();
        if ($mutasiBaru > 0) {
            $alerts[] = [
                'type' => 'info',
                'icon' => 'fa-solid fa-right-left',
                'title' => 'Mutasi terbaru',
                'desc' => "{$mutasiBaru} pegawai dimutasi pada 7 hari terakhir",
                'badge' => $mutasiBaru,
            ];
        }

        // TODO: Insight "kekurangan SDM" dan "rekomendasi rekrutmen" menyusul

        return $alerts;
    }

    public function getPelatihanSummary(): array
    {
        // dummy — persentase harus total 100
        return [
            'lebih_20jp' => 62,
            'kurang_20jp' => 25,
            'belum' => 13,
        ];
    }

    public function getSdmSummaryChart(): array
    {
        return [
            'medis' => 676,
            'non_medis' => 769,
            'bars' => [70, 55, 40, 48, 32],
        ];
    }

    public function getCutiSummary(): array
    {
        return [
            'total_bulan_ini' => 14,
            'bars' => [35, 50, 25, 60, 40, 70, 30],
        ];
    }
}