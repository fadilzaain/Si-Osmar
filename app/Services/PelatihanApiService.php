<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * PelatihanApiService
 *
 * Narik data pelatihan pegawai (jumlah pelatihan yang diikuti dan total jam
 * pelatihan, per unit dan per pegawai) dari API eksternal SIKAWAN. 
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
                ->values();

            return [
                'unit' => $unitBlock['unit'],
                'slug' => Str::slug($unitBlock['unit']),
                'pegawai' => $pegawai->all(),
                'summary' => [
                    'total_pegawai' => (int) $unitBlock['total_pegawai'],
                    'total_pelatihan' => (int) $unitBlock['total_pelatihan'],
                    'total_jam_pelatihan' => (float) $unitBlock['total_jam_pelatihan'],
                    'rata_rata_jam_per_pegawai' => (float) $unitBlock['rata_rata_jam_per_pegawai'],
                    ...$this->hitungAmbangJam($pegawai),
                ],
            ];
        })
        ->sortBy('unit')
        ->values()
        ->all();
    }

    /**
     * Hitung berapa pegawai yang total jam pelatihannya udah nyampe ambang
     * (AMBANG_JAM_CUKUP) dan berapa yang belum, dari satu collection pegawai.
     */
    protected function hitungAmbangJam(\Illuminate\Support\Collection $pegawai): array
    {
        $total = $pegawai->count();
        $jumlah20jpPlus = $pegawai->where('total_jam_pelatihan', '>=', self::AMBANG_JAM_CUKUP)->count();

        return [
            'jumlah_20jp_plus' => $jumlah20jpPlus,
            'jumlah_kurang_20jp' => $total - $jumlah20jpPlus,
        ];
    }

    /**
     * Angka ringkasan level rumah sakit — dipakai buat KPI card paling atas dan teks kesimpulan naratif. 
     */
    public function getRingkasanEksekutif(): array
    {
        $ringkasan = $this->getRingkasanPerUnit();
        $summaries = array_column($ringkasan, 'summary');
        $statistik = $this->fetchStatistik();

        $totalPegawai = ! empty($statistik)
            ? (int) $statistik['total_pegawai']
            : array_sum(array_column($summaries, 'total_pegawai'));

        $totalJam = ! empty($statistik)
            ? (float) $statistik['total_jam_pelatihan']
            : array_sum(array_column($summaries, 'total_jam_pelatihan'));

        return [
            'total_unit' => count($ringkasan),
            'total_pegawai' => $totalPegawai,
            'total_pelatihan' => array_sum(array_column($summaries, 'total_pelatihan')),
            'total_jam_pelatihan' => (int) round($totalJam),
            'rata_rata_jam_per_pegawai' => ! empty($statistik)
                ? (float) $statistik['rata_rata_jam_per_pegawai']
                : ($totalPegawai > 0 ? round($totalJam / $totalPegawai, 2) : 0),
        ];
    }

    /**
     * Angka kepesertaan total pegawai, yang jam pelatihannya udah >= ambang, yang sudah pernah
     * ikut pelatihan minimal 1x, dan yang belum sama sekali.
     *
     * Diambil dari "statistik" resmi SIKAWAN (dihitung dari sisi mereka,
     * mencakup pegawai yang 0x pelatihan juga), bukan diagregasi ulang dari
     * daftar per unit kita, karena daftar per unit itu cuma nyertain pegawai
     * yang minimal 1x pelatihan, jadi gak bisa dipakai buat ngitung
     * "belum pelatihan".
     */
    public function getRingkasanKepesertaan(): array
    {
        $statistik = $this->fetchStatistik();

        if (empty($statistik)) {
            return $this->hitungKepesertaanDariDaftarUnit();
        }

        $totalPegawai = (int) $statistik['total_pegawai'];
        $jumlahSudah = (int) $statistik['total_pegawai_sudah_pelatihan'];
        $jumlah20jpPlus = (int) $statistik['total_pegawai_sudah_pelatihan_20jp'];
        $jumlahBelum = (int) $statistik['total_pegawai_belum_pelatihan'];

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

    /**
     * Fallback: agregasi manual dari daftar per unit. Cuma dipakai
     * kalau "statistik" dari SIKAWAN lagi kosong/gagal.
     */
    protected function hitungKepesertaanDariDaftarUnit(): array
    {
        $semuaPegawai = collect($this->getRingkasanPerUnit())->flatMap(fn ($u) => $u['pegawai']);

        $totalPegawai = $semuaPegawai->count();
        $ambangJam = $this->hitungAmbangJam($semuaPegawai);
        $jumlahSudah = $semuaPegawai->where('jumlah_pelatihan', '>', 0)->count();
        $jumlahBelum = $totalPegawai - $jumlahSudah;

        return [
            'total_pegawai' => $totalPegawai,
            'jumlah_20jp_plus' => $ambangJam['jumlah_20jp_plus'],
            'persen_20jp_plus' => $this->persen($ambangJam['jumlah_20jp_plus'], $totalPegawai),
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
     */
    public function getChartRataRataPerUnit(int $limit = 8, bool $compact = false): array
    {
        $top = collect($this->getRingkasanPerUnit())
            ->sortByDesc('summary.rata_rata_jam_per_pegawai')
            ->take($limit)
            ->values();

        $labelLength = $compact ? 16 : 22;

        return [
            'labels' => $top->map(fn ($u) => Str::limit($u['unit'], $labelLength))->all(),
            'series' => $top->map(fn ($u) => $u['summary']['rata_rata_jam_per_pegawai'])->all(),
            'seriesName' => 'Rata-rata jam',
            'suffix' => ' jam',
            'color' => 'primary',
            'hideAxis' => $compact,
            'height' => $this->chartHeight($top->count(), $compact),
        ];
    }

    /**
     * Data buat horizontal bar chart pegawai dengan jam pelatihan tertinggi.
     */
    public function getChartTopPegawai(int $limit = 8, bool $compact = false): array
    {
        $top = collect($this->getTopPegawai($limit))->values();

        return [
            'labels' => $top->map(fn ($p) => Str::limit($p['nama'], 22))->all(),
            'series' => $top->map(fn ($p) => $p['total_jam_pelatihan'])->all(),
            'seriesName' => 'Jam pelatihan',
            'suffix' => ' jam',
            'color' => 'info',
            'hideAxis' => $compact,
            'height' => $this->chartHeight($top->count(), $compact),
        ];
    }

    /**
     * Tinggi chart horizontal-bar berdasarkan jumlah baris. Dipakai bareng
     * oleh semua chart per-unit & per-pegawai.
     */
    protected function chartHeight(int $count, bool $compact): int
    {
        return $compact
            ? max(120, $count * 40)
            : max(220, $count * 34);
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
     * balikin struktur kosong dan catat ke log, biar halaman tetap render
     * dalam bentuk empty-state, bukan error 500.
     */
    protected function fetchBody(): array
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

                    return ['data' => [], 'statistik' => []];
                }

                $body = $response->json();

                return [
                    'data' => $body['data'] ?? [],
                    'statistik' => $body['statistik'] ?? [],
                ];
            } catch (\Throwable $e) {
                Log::error('PelatihanApiService: gagal fetch API monitoring pelatihan', [
                    'message' => $e->getMessage(),
                ]);

                return ['data' => [], 'statistik' => []];
            }
        });
    }

    protected function fetchRaw(): array
    {
        return $this->fetchBody()['data'];
    }

    protected function fetchStatistik(): array
    {
        return $this->fetchBody()['statistik'];
    }
}