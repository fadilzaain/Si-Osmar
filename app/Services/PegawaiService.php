<?php

namespace App\Services;

use App\Models\Pegawai;

class PegawaiService
{
    public function getAll($filters = [])
    {
        $query = Pegawai::query();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('jabatan', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['unit_kerja'])) {
            $query->where('unit_kerja', $filters['unit_kerja']);
        }

        if (!empty($filters['status_kepegawaian'])) {
            $query->where('status_kepegawaian', $filters['status_kepegawaian']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->get();
    }

    public function getById($id)
    {
        return Pegawai::with(['kompetensis', 'pelatihans', 'mutasis'])->findOrFail($id);
    }

    public function create($data)
    {
        return Pegawai::create($data);
    }

    public function update($id, $data)
    {
        $pegawai = Pegawai::findOrFail($id);
        $pegawai->update($data);
        return $pegawai;
    }

    public function delete($id)
    {
        $pegawai = Pegawai::findOrFail($id);
        return $pegawai->delete();
    }

    public function getAllUnits()
    {
        return Pegawai::select('unit_kerja')->distinct()->pluck('unit_kerja');
    }
}
