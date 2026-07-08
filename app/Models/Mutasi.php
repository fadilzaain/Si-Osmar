<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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