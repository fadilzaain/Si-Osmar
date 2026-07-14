<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * BezettingApiService
 *
 * Narik data bezetting (jumlah pegawai riil vs kebutuhan, per jabatan, per unit)
 * dari API eksternal SIKAWAN (endpoint dikonfigurasi di config/services.php,
 * key 'sikawan.bezetting_endpoint'). Di-cache biar gak nembak API tiap request,
 * dan ada fallback structure kosong kalau API-nya lagi bermasalah — jadi
 * dashboard tetap render, cuma nunjukin empty-state, bukan error 500.
 */
class BezettingApiService
{
    public const STATUS_SESUAI = 'SESUAI';
    public const STATUS_KURANG = 'KURANG';
    public const STATUS_LEBIH = 'LEBIH';

    protected string $cacheKey = 'sdm.bezetting.raw';

    /**
     * Ringkasan per unit — dipakai buat render list accordion di halaman index.
     * Tiap unit udah dibungkus sama total & status keseluruhan biar Blade
     * gak perlu ngitung apa-apa lagi, tinggal render.
     */
    public function getRingkasanPerUnit(): array
    {
        $raw = $this->fetchRaw();

        $ringkasan = [];

        foreach ($raw as $namaUnit => $rows) {
            $ringkasan[] = [
                'unit' => $namaUnit,
                'slug' => \Illuminate\Support\Str::slug($namaUnit),
                'rows' => $rows,
                'summary' => $this->summarize($rows),
            ];
        }

        // Unit yang paling banyak kekurangan ditaruh di atas — lebih actionable
        // buat kepala SDM yang buka halaman ini.
        usort($ringkasan, fn ($a, $b) => $b['summary']['total_kekurangan'] <=> $a['summary']['total_kekurangan']);

        return $ringkasan;
    }

    /**
     * Detail baris bezetting untuk satu unit (dipakai kalau butuh re-query
     * per unit tanpa nge-load semuanya, misal buat endpoint API terpisah nanti).
     */
    public function getDetailByUnit(string $unit): array
    {
        $raw = $this->fetchRaw();

        return $raw[$unit] ?? [];
    }

    /**
     * Hitung ringkasan angka + status keseluruhan dari kumpulan baris jabatan
     * dalam satu unit.
     */
    protected function summarize(array $rows): array
    {
        $totalJumlah = array_sum(array_column($rows, 'jumlah'));
        $totalKebutuhan = array_sum(array_column($rows, 'kebutuhan'));
        $totalKekurangan = collect($rows)->sum(fn ($r) => max(0, $r['kekurangan']));
        $totalLebih = collect($rows)->sum(fn ($r) => max(0, -$r['kekurangan']));
        $jumlahJabatanKurang = collect($rows)->where('keterangan', self::STATUS_KURANG)->count();

        return [
            'total_pegawai' => $totalJumlah,
            'total_kebutuhan' => $totalKebutuhan,
            'total_kekurangan' => $totalKekurangan,
            'total_lebih' => $totalLebih,
            'jumlah_jabatan_kurang' => $jumlahJabatanKurang,
            'status' => $this->resolveOverallStatus($rows),
        ];
    }

    /**
     * Status keseluruhan unit, buat badge di header card:
     * - KURANG kalau ada minimal satu jabatan yang kurang (paling kritis, prioritas tampil)
     * - LEBIH kalau gak ada yang kurang tapi ada yang lebih
     * - SESUAI kalau semua pas
     */
    protected function resolveOverallStatus(array $rows): string
    {
        $keterangan = array_column($rows, 'keterangan');

        if (in_array(self::STATUS_KURANG, $keterangan, true)) {
            return self::STATUS_KURANG;
        }

        if (in_array(self::STATUS_LEBIH, $keterangan, true)) {
            return self::STATUS_LEBIH;
        }

        return self::STATUS_SESUAI;
    }

    /**
     * Ambil data mentah dari API, di-cache. Kalau API gagal (timeout, 500, dll),
     * balikin array kosong + catat ke log, biar gampang di-debug tanpa bikin
     * halaman ikut error.
     */
    protected function fetchRaw(): array
    {
        return Cache::remember($this->cacheKey, config('services.sikawan.cache_ttl', 900), function () {
            $baseUrl = rtrim(config('services.sikawan.base_url'), '/');
            $endpoint = config('services.sikawan.bezetting_endpoint');

            try {
                $response = Http::timeout(config('services.sikawan.timeout', 10))
                    ->acceptJson()
                    // SSL verification dimatikan cuma di environment local — banyak
                    // setup Windows (Laragon/XAMPP) belum punya CA bundle terpasang.
                    // JANGAN sampai baris ini nyala di production/staging.
                    ->withOptions(['verify' => ! app()->isLocal()])
                    ->get($baseUrl . $endpoint);

                if (! $response->successful()) {
                    Log::warning('BezettingApiService: response tidak sukses', [
                        'status' => $response->status(),
                    ]);

                    return [];
                }

                $body = $response->json();

                return $body['data'] ?? [];
            } catch (\Throwable $e) {
                Log::error('BezettingApiService: gagal fetch API bezetting', [
                    'message' => $e->getMessage(),
                ]);

                return [];
            }
        });
    }
}