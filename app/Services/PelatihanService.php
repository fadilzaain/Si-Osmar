<?php

namespace App\Services;

use App\Models\Pelatihan;

class PelatihanService
{
    public function getAll($filters = [])
    {
        $query = Pelatihan::query()->withCount('pegawais');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where('nama_pelatihan', 'like', "%{$search}%")
                  ->orWhere('penyelenggara', 'like', "%{$search}%");
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->get();
    }

    public function getById($id)
    {
        return Pelatihan::with('pegawais')->findOrFail($id);
    }

    public function create($data)
    {
        return Pelatihan::create($data);
    }

    public function update($id, $data)
    {
        $pelatihan = Pelatihan::findOrFail($id);
        $pelatihan->update($data);
        return $pelatihan;
    }

    public function delete($id)
    {
        $pelatihan = Pelatihan::findOrFail($id);
        return $pelatihan->delete();
    }

    public function enrollPegawai($pelatihanId, $pegawaiId, $status = 'Terdaftar')
    {
        $pelatihan = Pelatihan::findOrFail($pelatihanId);
        $pelatihan->pegawais()->syncWithoutDetaching([$pegawaiId => ['status' => $status]]);
        return $pelatihan;
    }

    public function updateParticipantStatus($pelatihanId, $pegawaiId, $status)
    {
        $pelatihan = Pelatihan::findOrFail($pelatihanId);
        $pelatihan->pegawais()->updateExistingPivot($pegawaiId, ['status' => $status]);
        return $pelatihan;
    }
}
