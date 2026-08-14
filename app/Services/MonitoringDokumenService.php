<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * MonitoringDokumenService
 *
 * Narik data kelengkapan dokumen legal pegawai (SIP & SPK/RKK) dari API
 * eksternal SIKAWAN (endpoint di config/services.php, key
 * 'sikawan.dokumen_endpoint'), lalu dinormalisasi jadi bentuk yang seragam
 * per pegawai — API mentahnya inkonsisten (field tanggal SIP namanya
 * "berlaku", tapi SPK/RKK "tanggal_berlaku"), jadi normalisasi ini yang
 * bikin Blade & JS gak perlu tau soal itu sama sekali.
 *
 * Catatan: SPK dan RKK isinya sama, jadi cuma data RKK yang dipakai dan
 * ditampilkan sebagai satu kolom gabungan "SPK/RKK" (key: SPK_RKK). Data
 * mentah "spk" dari API sengaja gak dipanggil lagi.
 *
 * Sama kayak BezettingApiService: di-cache, dan fallback ke array kosong
 * kalau API bermasalah (bukan error 500).
 */
class MonitoringDokumenService
{
    public const STATUS_SUCCESS = 'success';
    public const STATUS_WARNING = 'warning';
    public const STATUS_DANGER = 'danger';
    public const STATUS_NEUTRAL = 'neutral';

    /**
     * Ambang batas "segera kadaluarsa": kalau masa berlaku dokumen tinggal
     * sekian bulan lagi (atau kurang) dari sekarang, statusnya warning.
     */
    private const BULAN_SEGERA_KADALUARSA = 6;

    /**
     * Urutan "keparahan status, dipakai buat nentuin status keseluruhan
     * satu pegawai dari dokumennya (SIP & SPK/RKK) — makin besar makin
     * butuh perhatian direktur duluan.
     */
    private const SEVERITY = [
        self::STATUS_DANGER => 3,
        self::STATUS_NEUTRAL => 2,
        self::STATUS_WARNING => 1,
        self::STATUS_SUCCESS => 0,
    ];

    protected string $cacheKey = 'sdm.monitoring-dokumen.raw';

    /**
     * Daftar unit lengkap dengan pegawai yang sudah dinormalisasi dan ringkasan
     * per unit. dipakai buat render accordion di halaman detail.
     * Diurutkan dari unit paling bermasalah duluan.
     */
    public function getUnitList(): array
    {
        $raw = $this->fetchRaw();
        $unitList = [];

        foreach ($raw as $unitRow) {
            $namaUnit = $unitRow['unit'] ?? 'Tanpa Unit';
            $pegawaiList = array_map(
                fn (array $p) => $this->normalizePegawai($p),
                $unitRow['pegawai'] ?? []
            );

            $unitList[] = [
                'unit' => $namaUnit,
                'slug' => Str::slug($namaUnit),
                'pegawai' => $pegawaiList,
                'summary' => $this->summarize($pegawaiList),
            ];
        }

        usort($unitList, fn ($a, $b) => $b['summary']['bermasalah'] <=> $a['summary']['bermasalah']);

        return $unitList;
    }

    /**
     * dipakai buat 4 KPI card paling atas di halaman Monitoring Dokumen.
     */
    public function getRingkasanEksekutif(): array
    {
        $unitList = $this->getUnitList();
        $summaries = array_column($unitList, 'summary');

        $totalPegawai = array_sum(array_column($summaries, 'total_pegawai'));
        $totalBermasalah = array_sum(array_column($summaries, 'bermasalah'));

        return [
            'total_unit' => count($unitList),
            'total_unit_bermasalah' => collect($summaries)->where('bermasalah', '>', 0)->count(),
            'total_pegawai' => $totalPegawai,
            'jumlah_lengkap' => array_sum(array_column($summaries, 'lengkap')),
            'jumlah_perlu_diperpanjang' => array_sum(array_column($summaries, 'perlu_diperpanjang')),
            'total_bermasalah' => $totalBermasalah,
            'persen_lengkap' => $totalPegawai > 0
                ? round(($totalPegawai - $totalBermasalah) / $totalPegawai * 100)
                : 100,
            'total_dokumen_kadaluarsa' => array_sum(array_column($summaries, 'dokumen_kadaluarsa')),
            'total_dokumen_belum_ada' => array_sum(array_column($summaries, 'dokumen_belum_ada')),
        ];
    }

    /**
     * Unit paling kritis (pegawai bermasalah terbanyak), buat ranked list di bagian atas halaman
     */
    public function getTopUnitKritis(int $limit = 6): array
    {
        return collect($this->getUnitList())
            ->where('summary.bermasalah', '>', 0)
            ->take($limit)
            ->values()
            ->all();
    }

    /**
     * Kesimpulan satu paragraf
     */
    public function getKesimpulan(): string
    {
        $r = $this->getRingkasanEksekutif();

        if ($r['total_bermasalah'] <= 0 && $r['jumlah_perlu_diperpanjang'] <= 0) {
            return 'Seluruh dokumen legal pegawai (SIP & SPK/RKK) di semua unit dalam kondisi lengkap dan berlaku. Tidak ada tindakan mendesak yang diperlukan.';
        }

        if ($r['total_bermasalah'] > 0) {
            $topUnit = $this->getTopUnitKritis(1)[0] ?? null;

            $teks = "{$r['total_bermasalah']} pegawai di {$r['total_unit_bermasalah']} unit punya dokumen yang sudah kadaluarsa atau belum diunggah, perlu segera ditindaklanjuti.";

            if ($topUnit) {
                $teks .= " Mulai dari unit {$topUnit['unit']}  — menduduki peringkat atas, {$topUnit['summary']['bermasalah']} dari {$topUnit['summary']['total_pegawai']} pegawainya bermasalah.";
            }

            if ($r['jumlah_perlu_diperpanjang'] > 0) {
                $teks .= " Setelah itu, {$r['jumlah_perlu_diperpanjang']} pegawai lain juga perlu diingatkan untuk memperpanjang dokumennya sebelum kadaluarsa.";
            }

            return $teks;
        }

        // total_bermasalah 0, tapi ada yang perlu_diperpanjang.
        return "Belum ada dokumen yang benar-benar kadaluarsa. Tapi {$r['jumlah_perlu_diperpanjang']} pegawai perlu mulai diingatkan untuk memperpanjang dokumennya sebelum jatuh tempo.";
    }


    public function getRingkasanPerRuangan(): array
    {
        return collect($this->getUnitList())->map(function (array $unit) {
            $b = $unit['summary']['breakdown'];

            return [
                'ruangan' => $unit['unit'],
                'profesi' => collect($unit['pegawai'])->pluck('jabatan')->unique()->values()->all(),
                'total_pegawai' => $unit['summary']['total_pegawai'],
                'bermasalah' => $unit['summary']['bermasalah'],
                'breakdown' => [
                    'berlaku' => $b[self::STATUS_SUCCESS] ?? 0,
                    'akan_kadaluarsa' => $b[self::STATUS_WARNING] ?? 0,
                    'kadaluarsa' => ($b[self::STATUS_DANGER] ?? 0) + ($b[self::STATUS_NEUTRAL] ?? 0),
                ],
            ];
        })->all();
    }

 /**
     * Data siap pakai buat donut chart distribusi status dokumen (Lengkap / Akan Kadaluarsa / Kadaluarsa / Belum Ada)
     */
    public function getChartDistribusiStatus(): array
    {
        $eksekutif = $this->getRingkasanEksekutif();
        $breakdownTotal = [
            self::STATUS_SUCCESS => 0,
            self::STATUS_WARNING => 0,
            self::STATUS_DANGER => 0,
            self::STATUS_NEUTRAL => 0,
        ];

        foreach ($this->getUnitList() as $unit) {
            foreach ($unit['summary']['breakdown'] as $status => $jumlah) {
                $breakdownTotal[$status] += $jumlah;
            }
        }

        return [
            'series' => [
                $breakdownTotal[self::STATUS_SUCCESS],
                $breakdownTotal[self::STATUS_WARNING],
                $breakdownTotal[self::STATUS_DANGER],
                $breakdownTotal[self::STATUS_NEUTRAL],
            ],
            'labels' => ['Lengkap', 'Akan Kadaluarsa', 'Kadaluarsa', 'Belum Ada'],
            'colors' => ['success', 'warning', 'danger', 'info'],
            'size' => 128,
            'totalValue' => $eksekutif['persen_lengkap'] . '%',
            'totalLabel' => 'Lengkap',
        ];
    }

    /**
     * Satu pegawai dari API { nama, jabatan, inisial, dokumen: [SIP, SPK/RKK], overall_status }.
     * SPK & RKK isinya sama, jadi cuma data RKK yang dipakai buat kolom
     * gabungan "SPK/RKK" — data "spk" dari API sengaja gak disentuh lagi.
     */
    protected function normalizePegawai(array $p): array
    {
        $dokumen = [
            'SIP' => $this->normalizeDokumen($p['sip'] ?? null, $p['sip_status'] ?? null, $p['sip_masa_berlaku'] ?? null),
            'SPK_RKK' => $this->normalizeDokumen($p['rkk'] ?? null, $p['rkk_status'] ?? null, $p['rkk_masa_berlaku'] ?? null),
        ];

        // Status keseluruhan pegawai = status paling parah dari dokumennya.
        $overall = collect($dokumen)
            ->sortByDesc(fn (array $d) => self::SEVERITY[$d['status']] ?? 0)
            ->first()['status'] ?? self::STATUS_SUCCESS;

        $nama = $p['nama'] ?? '-';

        return [
            'nama' => $nama,
            'jabatan' => $p['jabatan'] ?? '-',
            'inisial' => $this->buatInisial($nama),
            'dokumen' => $dokumen,
            'overall_status' => $overall,
        ];
    }

    /**
     * Satu jenis dokumen (SIP/SPK/RKK) dari API -> bentuk seragam.
     * Nama field tanggal beda-beda di API mentah (sip: "berlaku",
     * spk/rkk: "tanggal_berlaku"), disamakan jadi "tanggal". Status
     * dihitung sendiri dari tanggal ini lewat determineStatus(), bukan
     * dipercaya mentah-mentah dari API.
     */
    protected function normalizeDokumen(?array $raw, ?string $statusRaw, ?string $masaBerlakuRaw): array
    {
        $raw ??= [];
        $file = $raw['file'] ?? null;
        $tanggal = $raw['berlaku'] ?? $raw['tanggal_berlaku'] ?? null;

        return [
            'tanggal' => $tanggal,
            'file' => $file,
            'file_url' => $this->getFileUrl($file),
            'file_verified' => $raw['file_verified'] ?? null,
            'masa_berlaku' => $masaBerlakuRaw ?: '-',
            'status' => $this->determineStatus($tanggal, $statusRaw),
        ];
    }

    /**
     * Tentukan status dokumen berdasarkan tanggal masa berlaku:
     * - Belum ada tanggal sama sekali (dokumen belum diunggah)   -> neutral
     * - Tanggal sudah lewat dari hari ini                        -> danger
     * - Tanggal tinggal <= 6 bulan lagi dari hari ini             -> warning
     * - Selain itu (masih lama)                                  -> success
     *
     * Kalau tanggalnya ada tapi gagal di-parse (format aneh dari API),
     * fallback ke status mentah dari API biar halaman tetap jalan.
     */
    protected function determineStatus(?string $tanggal, ?string $statusRaw): string
    {
        if (! $tanggal) {
            return self::STATUS_NEUTRAL;
        }

        try {
            $masaBerlaku = Carbon::parse($tanggal);
        } catch (\Throwable $e) {
            return $this->normalizeStatus($statusRaw);
        }

        if ($masaBerlaku->isPast()) {
            return self::STATUS_DANGER;
        }

        if ($masaBerlaku->lessThanOrEqualTo(now()->addMonths(self::BULAN_SEGERA_KADALUARSA))) {
            return self::STATUS_WARNING;
        }

        return self::STATUS_SUCCESS;
    }

    /**
     * Fallback: samakan string status mentah dari API ke salah satu
     * konstanta STATUS_*. Cuma dipakai kalau tanggal dokumen gagal di-parse.
     */
    protected function normalizeStatus(?string $raw): string
    {
        return match ($raw) {
            'danger' => self::STATUS_DANGER,
            'warning' => self::STATUS_WARNING,
            'success' => self::STATUS_SUCCESS,
            default => self::STATUS_NEUTRAL,
        };
    }

    /**
     * Inisial buat avatar bulat 
     */
    protected function buatInisial(string $nama): string
    {
        $namaBersih = trim(strtok($nama, ','));

        $inisial = collect(preg_split('/\s+/', $namaBersih))
            ->filter()
            ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))
            ->take(2)
            ->implode('');

        return $inisial ?: '?';
    }

    /**
     * URL publik berkas PDF. ASUMSI: berkas diakses lewat {base_url}/storage/{path}.
     */
    public function getFileUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $base = rtrim(
            config('services.sikawan.storage_url') ?: config('services.sikawan.base_url'),
            '/'
        );

        return $base . '/storage/' . ltrim($path, '/');
    }

    /**
     * Ringkasan per unit dari daftar pegawai yang sudah dinormalisasi
     */
    protected function summarize(array $pegawaiList): array
    {
        $breakdown = [
            self::STATUS_SUCCESS => 0,
            self::STATUS_WARNING => 0,
            self::STATUS_DANGER => 0,
            self::STATUS_NEUTRAL => 0,
        ];
        $dokumenKadaluarsa = 0;
        $dokumenBelumAda = 0;

        foreach ($pegawaiList as $p) {
            $breakdown[$p['overall_status']] = ($breakdown[$p['overall_status']] ?? 0) + 1;

            foreach ($p['dokumen'] as $d) {
                if ($d['status'] === self::STATUS_DANGER) {
                    $dokumenKadaluarsa++;
                }
                if ($d['status'] === self::STATUS_NEUTRAL) {
                    $dokumenBelumAda++;
                }
            }
        }

        $total = count($pegawaiList);

        return [
            'total_pegawai' => $total,
            'lengkap' => $breakdown[self::STATUS_SUCCESS],
            'perlu_diperpanjang' => $breakdown[self::STATUS_WARNING],
            'bermasalah' => $breakdown[self::STATUS_DANGER] + $breakdown[self::STATUS_NEUTRAL],
            'breakdown' => $breakdown,
            'dokumen_kadaluarsa' => $dokumenKadaluarsa,
            'dokumen_belum_ada' => $dokumenBelumAda,
        ];
    }

    /**
     * Ambil data mentah dari API, di-cache. Kalau API gagal, balikin array
     * kosong dan catat ke log, halaman tetap render dengan empty-state,
     * bukan error 500.
     */
    protected function fetchRaw(): array
    {
        return Cache::remember($this->cacheKey, config('services.sikawan.cache_ttl', 900), function () {
            $baseUrl = rtrim(config('services.sikawan.base_url'), '/');
            $endpoint = config('services.sikawan.dokumen_endpoint');

            try {
                $response = Http::timeout(config('services.sikawan.timeout', 10))
                    ->acceptJson()
                    ->withOptions(['verify' => app()->isLocal() ? false : config('services.sikawan.verify_ssl', true)])
                    ->get($baseUrl . $endpoint);

                if (! $response->successful()) {
                    Log::warning('MonitoringDokumenService: response tidak sukses', [
                        'status' => $response->status(),
                    ]);

                    return [];
                }

                $body = $response->json();

                return $body['data'] ?? [];
            } catch (\Throwable $e) {
                Log::error('MonitoringDokumenService: gagal fetch API monitoring dokumen', [
                    'message' => $e->getMessage(),
                ]);

                return [];
            }
        });
    }
}