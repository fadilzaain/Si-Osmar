<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * PelatihanApiService
 *
 * Narik data pelatihan pegawai (jumlah pelatihan yang diikuti & total jam
 * pelatihan, per unit & per pegawai) dari API eksternal SIKAWAN. 
 *
 */
class PelatihanApiService
{
    protected string $cacheKey = 'pelatihan.raw';
    protected const AMBANG_JAM_CUKUP = 20;

    /**
     * Ringkasan per unit
     */
    public function getRingkasanPerUnit(): array
    {
        $raw = $this->fetchRaw();

        return collect($raw)->map(function ($unitBlock) {
            $pegawai = collect($unitBlock['pegawai'] ?? [])
                ->map(fn ($p) => [
                    'pegawai_id' => $p['pegawai_id'],
                    'nama' => trim($p['nama_pegawai']),
                    'inisial' => $this->buatInisial($p['nama_pegawai']),
                    'jabatan' => $p['jabatan'] ?? '-',
                    'jumlah_pelatihan' => (int) $p['jumlah_pelatihan'],
                    'total_jam_pelatihan' => (float) $p['total_jam_pelatihan'],
                    'rata_rata_jam' => (float) $p['rata_rata_jam'],
                ])
                ->sortBy('nama')
                ->values()
                ->all();

            return [
                'unit' => $unitBlock['unit'],
                'slug' => Str::slug($unitBlock['unit']),
                'pegawai' => $pegawai,
                'summary' => [
                    'total_pegawai' => (int) $unitBlock['total_pegawai'],
                    'total_pelatihan' => (int) $unitBlock['total_pelatihan'],
                    'total_jam_pelatihan' => (float) $unitBlock['total_jam_pelatihan'],
                    'rata_rata_jam_per_pegawai' => (float) $unitBlock['rata_rata_jam_per_pegawai'],
                ],
            ];
        })
        ->sortBy('unit')
        ->values()
        ->all();
    }

    /**
     * Angka ringkasan level rumah sakit — dipakai buat KPI card paling atas.
     */
    public function getRingkasanEksekutif(): array
    {
        $ringkasan = $this->getRingkasanPerUnit();
        $summaries = array_column($ringkasan, 'summary');

        $totalPegawai = array_sum(array_column($summaries, 'total_pegawai'));
        $totalJam = array_sum(array_column($summaries, 'total_jam_pelatihan'));

        return [
            'total_unit' => count($ringkasan),
            'total_pegawai' => $totalPegawai,
            'total_pelatihan' => array_sum(array_column($summaries, 'total_pelatihan')),
            'total_jam_pelatihan' => (int) round($totalJam),
            'rata_rata_jam_per_pegawai' => $totalPegawai > 0
                ? round($totalJam / $totalPegawai, 2)
                : 0,
        ];
    }

    /**
     * Angka kepesertaan total pegawai, yang jam pelatihannya udah >= ambang, yang sudah pernah
     * ikut pelatihan minimal 1x, dan yang belum sama sekali.
     */
    public function getRingkasanKepesertaan(): array
    {
        $semuaPegawai = collect($this->getRingkasanPerUnit())->flatMap(fn ($u) => $u['pegawai']);

        $totalPegawai = $semuaPegawai->count();
        $jumlah20jpPlus = $semuaPegawai->where('total_jam_pelatihan', '>=', self::AMBANG_JAM_CUKUP)->count();
        $jumlahSudah = $semuaPegawai->where('jumlah_pelatihan', '>', 0)->count();
        $jumlahBelum = $totalPegawai - $jumlahSudah;

        return [
            'total_pegawai' => $totalPegawai,
            'jumlah_20jp_plus' => $jumlah20jpPlus,
            'persen_20jp_plus' => $this->persen($jumlah20jpPlus, $totalPegawai),
            'jumlah_sudah_pelatihan' => $jumlahSudah,
            'persen_sudah_pelatihan' => $this->persen($jumlahSudah, $totalPegawai),
            'jumlah_belum_pelatihan' => $jumlahBelum,
            'persen_belum_pelatihan' => $this->persen($jumlahBelum, $totalPegawai),
        ];
    }

    protected function persen(int $bagian, int $total): int
    {
        return $total > 0 ? (int) round($bagian / $total * 100) : 0;
    }

    /**
     * kesimpulan naratif
     */
    public function getKesimpulan(): string
    {
        $r = $this->getRingkasanEksekutif();

        if ($r['total_pegawai'] === 0) {
            return 'Belum ada data pelatihan pegawai yang bisa ditampilkan saat ini.';
        }

        $teks = "Dari {$r['total_pegawai']} pegawai di {$r['total_unit']} unit, tercatat "
            . "{$r['total_pelatihan']} kali partisipasi pelatihan dengan total "
            . number_format($r['total_jam_pelatihan'], 0, ',', '.') . ' jam pelatihan '
            . "(rata-rata {$r['rata_rata_jam_per_pegawai']} jam per pegawai).";

        $topUnit = collect($this->getRingkasanPerUnit())
            ->sortByDesc('summary.rata_rata_jam_per_pegawai')
            ->first();

        if ($topUnit) {
            $teks .= " Unit dengan rata-rata jam pelatihan tertinggi: {$topUnit['unit']} "
                . "({$topUnit['summary']['rata_rata_jam_per_pegawai']} jam/pegawai).";
        }

        return $teks;
    }

    /**
     * Pegawai dengan total jam pelatihan tertinggi, lintas semua unit.
     */
    public function getTopPegawai(?int $limit = 8): array
    {
        $semua = collect($this->getRingkasanPerUnit())
            ->flatMap(fn ($u) => collect($u['pegawai'])->map(fn ($p) => [...$p, 'unit' => $u['unit']]))
            ->sortByDesc('total_jam_pelatihan')
            ->values();

        return $limit ? $semua->take($limit)->all() : $semua->all();
    }

    /**
     * Pegawai dengan total jam pelatihan paling sedikit
     */
    public function getPegawaiJamPalingSedikit(?int $limit = 8): array
    {
        $semua = collect($this->getRingkasanPerUnit())
            ->flatMap(fn ($u) => collect($u['pegawai'])->map(fn ($p) => [...$p, 'unit' => $u['unit']]))
            ->sortBy('total_jam_pelatihan')
            ->values();

        return $limit ? $semua->take($limit)->all() : $semua->all();
    }

    /**
     * Data buat horizontal bar chart rata-rata jam pelatihan per unit.
     * suffix & color di-set eksplisit jika dibiarkan default,
     * renderer chart anggep datanya persen (0-100) dan warnanya merah,
     * padahal ini satuan jam.
     */
    public function getChartRataRataPerUnit(int $limit = 8): array
    {
        $top = collect($this->getRingkasanPerUnit())
            ->sortByDesc('summary.rata_rata_jam_per_pegawai')
            ->take($limit)
            ->values();

        return [
            'labels' => $top->map(fn ($u) => Str::limit($u['unit'], 22))->all(),
            'series' => $top->map(fn ($u) => $u['summary']['rata_rata_jam_per_pegawai'])->all(),
            'seriesName' => 'Rata-rata jam',
            'suffix' => ' jam',
            'color' => 'primary',
            'height' => max(220, $top->count() * 34),
        ];
    }

    /**
     * Data buat horizontal bar chart pegawai dengan jam pelatihan tertinggi.
     */
    public function getChartTopPegawai(int $limit = 8): array
    {
        $top = collect($this->getTopPegawai($limit))->values();

        return [
            'labels' => $top->map(fn ($p) => Str::limit($p['nama'], 22))->all(),
            'series' => $top->map(fn ($p) => $p['total_jam_pelatihan'])->all(),
            'seriesName' => 'Jam pelatihan',
            'suffix' => ' jam',
            'color' => 'info',
            'height' => max(220, $top->count() * 34),
        ];
    }

    protected function buatInisial(string $nama): string
    {
        $kata = array_filter(explode(' ', trim($nama)));

        return collect($kata)
            ->take(2)
            ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))
            ->implode('');
    }

    /**
     * Ambil data mentah dari API, di-cache. Kalau API gagal, 
     * balikin array kosong dan catat ke log, biar halaman tetap render
     * dalam bentuk empty-state, bukan error 500.
     */
    protected function fetchRaw(): array
    {
        return Cache::remember($this->cacheKey, config('services.sikawan.cache_ttl', 900), function () {
            $baseUrl = rtrim(config('services.sikawan.base_url'), '/');
            $endpoint = config('services.sikawan.pelatihan_endpoint');

            try {
                $response = Http::timeout(config('services.sikawan.timeout', 10))
                    ->acceptJson()
                    // SSL verification dimatikan cuma di environment local.
                    // Di production, ikutin SIKAWAN_VERIFY_SSL di .env (default true).
                    ->withOptions(['verify' => app()->isLocal() ? false : config('services.sikawan.verify_ssl', true)])
                    ->get($baseUrl . $endpoint);

                if (! $response->successful()) {
                    Log::warning('PelatihanApiService: response tidak sukses', [
                        'status' => $response->status(),
                    ]);

                    return [];
                }

                $body = $response->json();

                return $body['data'] ?? [];
            } catch (\Throwable $e) {
                Log::error('PelatihanApiService: gagal fetch API monitoring pelatihan', [
                    'message' => $e->getMessage(),
                ]);

                return [];
            }
        });
    }
}