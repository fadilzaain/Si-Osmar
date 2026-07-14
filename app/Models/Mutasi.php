<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * @deprecated Sejak Juli 2026 — sama seperti Pegawai, nyisa dari rencana
 * penyimpanan mutasi lokal yang gak jadi dipakai. Fitur rotasi sekarang
 * digantikan analisis "peluang redistribusi" dari BezettingApiService
 * (lihat getPeluangRedistribusi() dan getPeluangRedistribusiUntukUnit()).
 */
class Mutasi extends Model
{
    protected $fillable = [
        'pegawai_id',
        'jenis_mutasi',
        'unit_lama',
        'unit_baru',
        'tanggal_mutasi',
        'keterangan',
    ];

    // TAMBAHAN: cast tanggal_mutasi jadi Carbon
    protected $casts = [
        'tanggal_mutasi' => 'date',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }
}