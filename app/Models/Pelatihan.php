<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pelatihan extends Model
{
    protected $fillable = [
        'nama_pelatihan',
        'penyelenggara',
        'tanggal_mulai',
        'tanggal_selesai',
        'kuota',
        'status',
    ];

    // TAMBAHAN: cast tanggal
    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function pegawais()
    {
        return $this->belongsToMany(Pegawai::class, 'pelatihan_pegawai')
                    ->withPivot('status')
                    ->withTimestamps();
    }
}