<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

 /**
 * @deprecated Sejak Juli 2026 — SI-OSMAR full-API (semua data operasional
 * ditarik langsung dari SIKAWAN, gak ada penyimpanan lokal). Model ini
 * nyisa dari rencana awal sebelum keputusan itu difinalkan, tabelnya
 * kosong permanen. Dipertahankan (bukan dihapus) buat jaga-jaga kalau
 * rencana berubah lagi. JANGAN dipanggil dari controller/service baru —
 * pakai BezettingApiService buat data pegawai/unit yang sekarang real.
 */
class Pegawai extends Model
{
    protected $fillable = [
        'nip',
        'nama',
        'jabatan',
        'unit_kerja',
        'status_kepegawaian',
        'email',
        'telepon',
        'tanggal_masuk',
        'skor_kompetensi',
        'status',
    ];

    // TAMBAHAN: tanpa ini, tanggal_masuk balik sebagai string mentah dari DB,
    // bukan objek Carbon. Jadi .diffInYears(), .format(), dll bakal error.
    protected $casts = [
        'tanggal_masuk' => 'date',
    ];

    public function kompetensis()
    {
        return $this->hasMany(Kompetensi::class);
    }

    public function pelatihans()
    {
        return $this->belongsToMany(Pelatihan::class, 'pelatihan_pegawai')
                    ->withPivot('status')
                    ->withTimestamps();
    }

    public function mutasis()
    {
        return $this->hasMany(Mutasi::class);
    }
}