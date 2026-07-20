<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * MonitoringDokumenService
 *
 * Narik data kelengkapan dokumen legal pegawai (SIP, SPK, RKK) dari API
 * eksternal SIKAWAN (endpoint di config/services.php, key
 * 'sikawan.dokumen_endpoint'), lalu dinormalisasi jadi bentuk yang seragam
 * per pegawai — API mentahnya inkonsisten (field tanggal SIP namanya
 * "berlaku", tapi SPK/RKK "tanggal_berlaku"), jadi normalisasi ini yang
 * bikin Blade & JS gak perlu tau soal itu sama sekali.
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
     * Urutan "keparahan" status, dipakai buat nentuin status keseluruhan
     * satu pegawai dari 3 dokumennya (SIP/SPK/RKK) — makin besar makin
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
     * Daftar unit lengkap dengan pegawai yang sudah dinormalisasi + ringkasan
     * per unit. Ini yang dipakai langsung buat render accordion di halaman
     * detail. Diurutkan dari unit paling bermasalah duluan — paling
     * actionable buat direktur yang buka halaman ini.
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
     * Ringkasan level rumah sakit (lintas semua unit) — dipakai buat 4 KPI
     * card paling atas di halaman Monitoring Dokumen.
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
            'total_bermasalah' => $totalBermasalah,
            'persen_lengkap' => $totalPegawai > 0
                ? round(($totalPegawai - $totalBermasalah) / $totalPegawai * 100)
                : 100,
            'total_dokumen_kadaluarsa' => array_sum(array_column($summaries, 'dokumen_kadaluarsa')),
            'total_dokumen_belum_ada' => array_sum(array_column($summaries, 'dokumen_belum_ada')),
        ];
    }

    /**
     * Unit-unit paling kritis (pegawai bermasalah terbanyak), buat ranked
     * list di bagian atas halaman — getUnitList() udah di-sort desc, jadi
     * di sini tinggal filter yang beneran bermasalah + potong.
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
     * Kesimpulan satu paragraf, dihasilkan otomatis dari angka yang sama
     * kayak KPI card di atasnya — bukan input manual.
     */
    public function getKesimpulan(): string
    {
        $r = $this->getRingkasanEksekutif();

        if ($r['total_bermasalah'] <= 0) {
            return 'Seluruh dokumen legal pegawai (SIP, SPK, RKK) di semua unit dalam kondisi lengkap dan berlaku. Tidak ada tindakan mendesak yang diperlukan.';
        }

        $persen = 100 - $r['persen_lengkap'];

        $teks = "Dari {$r['total_pegawai']} pegawai yang dipantau di {$r['total_unit']} unit, {$r['total_bermasalah']} pegawai ({$persen}%) memiliki dokumen bermasalah, tersebar di {$r['total_unit_bermasalah']} unit.";

        if ($r['total_dokumen_kadaluarsa'] > 0) {
            $teks .= " Tercatat {$r['total_dokumen_kadaluarsa']} dokumen sudah kadaluarsa dan perlu segera diperpanjang.";
        }

        if ($r['total_dokumen_belum_ada'] > 0) {
            $teks .= " {$r['total_dokumen_belum_ada']} dokumen lainnya belum diunggah sama sekali.";
        }

        return $teks;
    }

    /**
     * Kontrak lama dipertahankan apa adanya — dipakai oleh DashboardController
     * & komponen <x-ringkasan-dokumen> di dashboard. "Belum ada dokumen"
     * digabung ke bucket 'kadaluarsa' di sini karena widget dashboard cuma
     * punya 3 warna; rincian sebenarnya (kadaluarsa vs belum ada) baru
     * dipisah di halaman detail penuh.
     */
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
     * Data siap pakai buat donut chart distribusi status dokumen (Lengkap /
     * Akan Kadaluarsa / Kadaluarsa / Belum Ada) — dipakai di card
     * "Monitoring Dokumen" pada dashboard utama. Format sesuai renderer
     * 'donut-multi' di dashboard-charts.js.
     *
     * Sengaja dipecah 4 kategori, bukan digabung jadi satu "Bermasalah" —
     * biar direktur langsung lihat proporsi yang beneran genting
     * (kadaluarsa / belum ada) vs yang masih sebatas warning (akan
     * kadaluarsa), gak ditumpuk jadi satu angka merah gede.
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
            // Urutan label & warna HARUS sejajar sama urutan series di atas.
            'labels' => ['Lengkap', 'Akan Kadaluarsa', 'Kadaluarsa', 'Belum Ada'],
            'colors' => ['success', 'warning', 'danger', 'info'],
            'size' => 128,
            'totalValue' => $eksekutif['persen_lengkap'] . '%',
            'totalLabel' => 'Lengkap',
        ];
    }

    /**
     * Satu pegawai dari raw API -> bentuk seragam { nama, jabatan, inisial,
     * dokumen: [SIP, SPK, RKK], overall_status }.
     */
    protected function normalizePegawai(array $p): array
    {
        $dokumen = [
            'SIP' => $this->normalizeDokumen($p['sip'] ?? null, $p['sip_status'] ?? null, $p['sip_masa_berlaku'] ?? null),
            'SPK' => $this->normalizeDokumen($p['spk'] ?? null, $p['spk_status'] ?? null, $p['spk_masa_berlaku'] ?? null),
            'RKK' => $this->normalizeDokumen($p['rkk'] ?? null, $p['rkk_status'] ?? null, $p['rkk_masa_berlaku'] ?? null),
        ];

        // Status keseluruhan pegawai = status paling parah dari 3 dokumennya.
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
     * Satu jenis dokumen (SIP/SPK/RKK) dari raw API -> bentuk seragam.
     * Nama field tanggal beda-beda di API mentah (sip: "berlaku",
     * spk/rkk: "tanggal_berlaku"), di sini disamakan jadi "tanggal".
     */
    protected function normalizeDokumen(?array $raw, ?string $statusRaw, ?string $masaBerlakuRaw): array
    {
        $raw ??= [];
        $file = $raw['file'] ?? null;

        return [
            'tanggal' => $raw['berlaku'] ?? $raw['tanggal_berlaku'] ?? null,
            'file' => $file,
            'file_url' => $this->getFileUrl($file),
            'file_verified' => $raw['file_verified'] ?? null,
            'masa_berlaku' => $masaBerlakuRaw ?: '-',
            'status' => $this->normalizeStatus($statusRaw),
        ];
    }

    /**
     * Peta status mentah dari API -> variant badge kita. Value yang gak
     * dikenali (termasuk "secondary" yang berarti dokumen belum ada, atau
     * null) sengaja fallback ke 'neutral', bukan diabaikan — biar dokumen
     * yang statusnya aneh tetap kelihatan butuh dicek, bukan hilang diam-diam.
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
     * Inisial buat avatar bulat — nama di API kadang ada gelar setelah koma
     * ("HERY PURNOMOWATI , A.Md.AK"), jadi bagian setelah koma pertama
     * dibuang dulu sebelum diambil huruf depannya.
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
     * Kalau di server SIKAWAN pola URL-nya beda, set SIKAWAN_STORAGE_URL di
     * .env atau sesuaikan baris ini.
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
     * Ringkasan per unit dari daftar pegawai yang sudah dinormalisasi:
     * breakdown jumlah pegawai per status keseluruhan, plus hitungan
     * dokumen individual yang kadaluarsa / belum ada (lintas SIP+SPK+RKK).
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
            'bermasalah' => $total - $breakdown[self::STATUS_SUCCESS],
            'breakdown' => $breakdown,
            'dokumen_kadaluarsa' => $dokumenKadaluarsa,
            'dokumen_belum_ada' => $dokumenBelumAda,
        ];
    }

    /**
     * Ambil data mentah dari API, di-cache. Kalau API gagal, balikin array
     * kosong + catat ke log — halaman tetap render dengan empty-state,
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