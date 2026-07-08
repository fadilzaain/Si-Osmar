<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kompetensi extends Model
{
    protected $fillable = [
        'pegawai_id',
        'nama_sertifikat',
        'nomor_sertifikat',
        'penerbit',
        'tanggal_terbit',
        'tanggal_kadaluarsa',
        'status',
    ];

    protected $casts = [
        'tanggal_terbit' => 'date',
        'tanggal_kadaluarsa' => 'date',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }

    // TAMBAHAN PENTING: kolom 'status' di DB itu STATIS, nggak otomatis
    // berubah jadi "Kadaluarsa" begitu tanggal_kadaluarsa lewat.
    // Pakai accessor ini kalau butuh cek expired secara real-time & akurat,
    // jangan andalkan kolom status doang.
    public function getIsExpiredAttribute(): bool
    {
        return $this->tanggal_kadaluarsa->isPast();
    }

    // TAMBAHAN: scope buat cari sertifikat yang mau kadaluarsa dalam N hari ke depan
    // (berguna buat fitur alert/notifikasi di Milestone selanjutnya)
    public function scopeAkanKadaluarsa($query, int $hari = 30)
    {
        return $query->whereBetween('tanggal_kadaluarsa', [now(), now()->addDays($hari)]);
    }

    // TAMBAHAN: scope buat yang sudah lewat tanggal kadaluarsa
    public function scopeSudahKadaluarsa($query)
    {
        return $query->where('tanggal_kadaluarsa', '<', now());
    }
}