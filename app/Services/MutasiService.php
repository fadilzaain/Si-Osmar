<?php

namespace App\Services;

use App\Models\Mutasi;
use App\Models\Pegawai;
use Illuminate\Support\Facades\DB;

class MutasiService
{
    public function getAll($filters = [])
    {
        $query = Mutasi::with('pegawai');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('pegawai', function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%");
            })->orWhere('jenis_mutasi', 'like', "%{$search}%");
        }

        if (!empty($filters['jenis_mutasi'])) {
            $query->where('jenis_mutasi', $filters['jenis_mutasi']);
        }

        return $query->latest()->get();
    }

    /**
     * Aktivitas mutasi (rotasi/promosi/demosi/keluar) terbaru yang menyentuh
     * satu unit tertentu — baik sebagai unit asal maupun unit tujuan.
     * Dipakai di kartu detail Bezetting SDM per unit.
     *
     * Return-nya udah dibentuk sesuai props <x-dashboard.activity-list>
     * (nama, dari, ke, waktu) biar bisa langsung dipakai tanpa transformasi lagi.
     */
    public function getRecentByUnit(string $unit, int $limit = 5): array
    {
        $mutasi = Mutasi::with('pegawai')
            ->where(function ($q) use ($unit) {
                $q->where('unit_lama', $unit)->orWhere('unit_baru', $unit);
            })
            ->latest('tanggal_mutasi')
            ->limit($limit)
            ->get();

        return $this->formatUntukActivityList($mutasi);
    }

    /**
     * Sama seperti getRecentByUnit, tapi lintas semua unit — dipakai di
     * card ringkasan SDM pada dashboard utama.
     */
    public function getRecentGlobal(int $limit = 6): array
    {
        $mutasi = Mutasi::with('pegawai')
            ->latest('tanggal_mutasi')
            ->limit($limit)
            ->get();

        return $this->formatUntukActivityList($mutasi);
    }

    protected function formatUntukActivityList($mutasiCollection): array
    {
        return $mutasiCollection->map(function (Mutasi $m) {
            return [
                'nama' => $m->pegawai->nama ?? '—',
                'dari' => $m->unit_lama,
                'ke' => $m->jenis_mutasi === 'Keluar' ? 'Keluar/Purna' : $m->unit_baru,
                'waktu' => $m->tanggal_mutasi?->diffForHumans() ?? '-',
            ];
        })->all();
    }

    public function create($data)
    {
        // FIX: dibungkus transaction. Sebelumnya kalau create Mutasi berhasil
        // tapi update Pegawai gagal (misal exception di tengah), data jadi
        // nyangkut setengah (mutasi tercatat tapi unit_kerja pegawai gak keupdate).
        return DB::transaction(function () use ($data) {
            $mutasi = Mutasi::create($data);

            if (in_array($mutasi->jenis_mutasi, ['Rotasi', 'Promosi', 'Demosi'])) {
                $pegawai = Pegawai::findOrFail($mutasi->pegawai_id);
                $pegawai->update(['unit_kerja' => $mutasi->unit_baru]);
            } elseif ($mutasi->jenis_mutasi === 'Keluar') {
                $pegawai = Pegawai::findOrFail($mutasi->pegawai_id);
                $pegawai->update(['status' => 'Nonaktif']);
            }

            return $mutasi;
        });
    }
}