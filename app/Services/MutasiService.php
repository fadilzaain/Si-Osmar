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