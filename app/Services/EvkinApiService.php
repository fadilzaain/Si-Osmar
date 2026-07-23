<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * EvkinApiService
 *
 * Narik data capaian kinerja (predikat evaluasi kinerja per triwulan) pegawai
 * dari API eksternal SIKAWAN (endpoint 'sikawan.evkin_endpoint'). Response
 * mentahnya per unit -> daftar pegawai, tiap pegawai punya predikat tw_1..tw_4
 * (bisa null kalau triwulan itu belum dinilai). Service ini nentuin predikat
 * "terkini" tiap pegawai (triwulan terakhir yang udah keisi, bukan cuma tw_4
 * mentah-mentah), lalu ngerangkum jadi statistik per unit & eksekutif buat
 * dashboard direktur.
 *
 * Sama kayak service SIKAWAN lain (CutiApiService, BezettingApiService):
 * di-cache biar gak nembak API tiap request, dan ada fallback array kosong
 * kalau API-nya lagi bermasalah — halaman tetap render, cuma nunjukin
 * empty-state, bukan error 500.
 */
class EvkinApiService
{
    /**
     * Urutan predikat resmi dari terbaik ke terburuk. Predikat di luar daftar
     * ini (typo/format beda dari API) tetap ditampilkan apa adanya di sel
     * triwulan, tapi gak masuk hitungan statistik predikat resmi.
     */
    public const URUTAN_PREDIKAT = ['Sangat Baik', 'Baik', 'Cukup', 'Kurang', 'Sangat Kurang'];

    /** Warna tone (dipetakan ke variant <x-badge> & warna chart) per predikat. */
    public const TONE_PREDIKAT = [
        'Sangat Baik' => 'success',
        'Baik' => 'primary',
        'Cukup' => 'info',
        'Kurang' => 'warning',
        'Sangat Kurang' => 'danger',
    ];

    protected string $cacheKey = 'evkin.capaian-kinerja.raw';

    /**
     * Ringkasan per unit — dipakai buat render list accordion di halaman index.
     * Tiap unit udah dibungkus daftar pegawai (rincian per triwulan + predikat
     * terkini) + summary jumlah per predikat, biar Blade tinggal render tanpa
     * ngitung apa-apa lagi.
     */
    public function getRingkasanPerUnit(): array
    {
        $raw = $this->fetchRaw();

        $ringkasan = collect($raw)->map(function ($unitBlock) {
            $pegawai = $this->kelompokkanPegawai($unitBlock['pegawai'] ?? []);

            return [
                'unit' => $unitBlock['unit'],
                'slug' => Str::slug($unitBlock['unit']),
                'pegawai' => $pegawai,
                'summary' => $this->summarizeUnit($pegawai),
            ];
        })->values()->all();

        // Unit dengan persentase capaian baik (Sangat Baik + Baik) paling
        // rendah ditaruh di atas — paling butuh perhatian direktur duluan.
        usort($ringkasan, fn ($a, $b) => $a['summary']['persen_baik'] <=> $b['summary']['persen_baik']);

        return $ringkasan;
    }

    /**
     * Ringkasan eksekutif buat KPI card di paling atas halaman: total
     * pegawai + jumlah pegawai per predikat (predikat terkini masing-masing).
     */
    public function getRingkasanEksekutif(): array
    {
        $ringkasan = $this->getRingkasanPerUnit();
        $semuaPegawai = collect($ringkasan)->flatMap(fn ($u) => $u['pegawai']);

        $jumlahPerPredikat = [];
        foreach (self::URUTAN_PREDIKAT as $predikat) {
            $jumlahPerPredikat[$predikat] = $semuaPegawai->where('predikat_terkini', $predikat)->count();
        }

        $totalDinilai = array_sum($jumlahPerPredikat);
        $jumlahBaik = $jumlahPerPredikat['Sangat Baik'] + $jumlahPerPredikat['Baik'];

        return [
            'total_pegawai' => $semuaPegawai->count(),
            'total_unit' => count($ringkasan),
            'total_dinilai' => $totalDinilai,
            'belum_dinilai' => $semuaPegawai->whereNull('predikat_terkini')->count(),
            'per_predikat' => $jumlahPerPredikat,
            'persen_baik' => $totalDinilai > 0 ? (int) round($jumlahBaik / $totalDinilai * 100) : 0,
        ];
    }

    /**
     * Satu kalimat kesimpulan naratif inti insight dari halaman.
     */
    public function getKesimpulan(): string
    {
        $eksekutif = $this->getRingkasanEksekutif();

        if ($eksekutif['total_dinilai'] === 0) {
            return 'Belum ada data capaian kinerja pegawai yang bisa ditampilkan saat ini.';
        }

        $teks = "Dari {$eksekutif['total_dinilai']} pegawai yang sudah dinilai di {$eksekutif['total_unit']} unit, "
            . "{$eksekutif['persen_baik']}% berpredikat Sangat Baik atau Baik "
            . "({$eksekutif['per_predikat']['Sangat Baik']} Sangat Baik, {$eksekutif['per_predikat']['Baik']} Baik).";

        $perluPerhatian = $eksekutif['per_predikat']['Kurang'] + $eksekutif['per_predikat']['Sangat Kurang'];
        if ($perluPerhatian > 0) {
            $teks .= " {$perluPerhatian} pegawai berpredikat Kurang/Sangat Kurang dan perlu perhatian.";
        }

        if ($eksekutif['belum_dinilai'] > 0) {
            $teks .= " {$eksekutif['belum_dinilai']} pegawai belum ada penilaian triwulan berjalan.";
        }

        return $teks;
    }

    /**
     * Data siap pakai buat bar chart distribusi predikat seluruh pegawai
     * yang sudah dinilai (sebelumnya donut — diganti bar biar jumlah per
     * predikat lebih gampang dibandingkan langsung, bukan cuma proporsi).
     * Satu warna per bar sesuai tone predikat masing-masing (mode distributed
     * di renderBarHorizontal).
     */
    public function getChartDistribusiPredikat(): array
    {
        $eksekutif = $this->getRingkasanEksekutif();

        return [
            'labels' => self::URUTAN_PREDIKAT,
            'series' => array_values($eksekutif['per_predikat']),
            'colors' => array_values(self::TONE_PREDIKAT),
            'seriesName' => 'Jumlah Pegawai',
            'suffix' => ' orang',
            'height' => 260,
        ];
    }

    /**
     * Data siap pakai buat horizontal bar chart "unit paling butuh perhatian".
     * Basisnya persentase pegawai yang BELUM DINILAI sama sekali (bukan
     * persentase capaian baik) — belum dinilai itu lebih actionable buat
     * direktur: itu berarti ada pegawai yang belum ngumpulin/dievaluasi,
     * bukan sekadar predikatnya kurang bagus. Diurutkan dari yang paling
     * banyak belum dinilai.
     */
    public function getChartUnitPerluPerhatian(int $limit = 8): array
    {
        $unit = collect($this->getRingkasanPerUnit())
            ->filter(fn ($u) => $u['summary']['total_pegawai'] > 0)
            ->sortByDesc(fn ($u) => $u['summary']['persen_belum_dinilai'])
            ->take($limit)
            ->reverse()
            ->values();

        return [
            'labels' => $unit->map(fn ($u) => Str::limit($u['unit'], 22))->all(),
            'series' => $unit->map(fn ($u) => $u['summary']['persen_belum_dinilai'])->all(),
            'seriesName' => '% Belum Dinilai',
            'color' => 'danger',
            'height' => max(220, $unit->count() * 34),
        ];
    }

    /**
     * Kelompokkan baris pegawai mentah dari API jadi bentuk siap render:
     * rincian predikat per triwulan + predikat terkini.
     */
    protected function kelompokkanPegawai(array $rows): array
    {
        return collect($rows)
            ->map(function ($r) {
                $triwulan = [
                    'tw_1' => $this->normalisasiPredikat($r['tw_1'] ?? null),
                    'tw_2' => $this->normalisasiPredikat($r['tw_2'] ?? null),
                    'tw_3' => $this->normalisasiPredikat($r['tw_3'] ?? null),
                    'tw_4' => $this->normalisasiPredikat($r['tw_4'] ?? null),
                ];

                [$predikatTerkini, $twTerkini] = $this->cariPredikatTerkini($triwulan);

                return [
                    'pegawai_id' => $r['pegawai_id'],
                    'nama' => trim($r['nama']),
                    'tahun' => $r['tahun'],
                    'inisial' => $this->buatInisial($r['nama']),
                    'triwulan' => $triwulan,
                    'predikat_terkini' => $predikatTerkini,
                    'triwulan_terkini' => $twTerkini,
                ];
            })
            ->sortBy('nama')
            ->values()
            ->all();
    }

    /**
     * Samain teks predikat mentah dari API SIKAWAN ke salah satu string resmi
     * di URUTAN_PREDIKAT (kalau cocok, abaikan beda spasi/kapitalisasi) —
     * dipanggil sekali di titik masuk data (kelompokkanPegawai), biar semua
     * perbandingan string persis di bawahnya (hitungan per predikat, tone
     * badge) gak diam-diam gagal cuma gara-gara API ngirim "sangat baik"
     * atau ada spasi nyempil, padahal maksudnya predikat yang sama.
     * Predikat di luar daftar resmi (typo/format lain) dibalikin apa adanya
     * biar tetap kelihatan di sel, cuma gak masuk hitungan statistik resmi.
     */
    protected function normalisasiPredikat(?string $predikat): ?string
    {
        $bersih = trim((string) $predikat);
        if ($bersih === '') {
            return null;
        }

        foreach (self::URUTAN_PREDIKAT as $resmi) {
            if (strcasecmp($bersih, $resmi) === 0) {
                return $resmi;
            }
        }

        return $bersih;
    }

    /**
     * Cari predikat triwulan paling akhir yang sudah keisi (tw_4 diprioritaskan,
     * mundur ke tw_1 kalau yang belakangan masih null/kosong). Balikin
     * [predikat, label_triwulan] atau [null, null] kalau belum ada satupun
     * triwulan yang keisi.
     */
    protected function cariPredikatTerkini(array $triwulan): array
    {
        foreach (['tw_4', 'tw_3', 'tw_2', 'tw_1'] as $tw) {
            if (! empty($triwulan[$tw])) {
                return [$triwulan[$tw], str_replace('tw_', 'TW ', $tw)];
            }
        }

        return [null, null];
    }

    /**
     * Ringkasan angka satu unit dari daftar pegawai yang sudah dikelompokkan.
     */
    protected function summarizeUnit(array $pegawai): array
    {
        $collection = collect($pegawai);

        $jumlahPerPredikat = [];
        foreach (self::URUTAN_PREDIKAT as $predikat) {
            $jumlahPerPredikat[$predikat] = $collection->where('predikat_terkini', $predikat)->count();
        }

        $totalPegawai = $collection->count();
        $totalDinilai = array_sum($jumlahPerPredikat);
        $jumlahBaik = $jumlahPerPredikat['Sangat Baik'] + $jumlahPerPredikat['Baik'];
        $belumDinilai = $collection->whereNull('predikat_terkini')->count();

        return [
            'total_pegawai' => $totalPegawai,
            'total_dinilai' => $totalDinilai,
            'belum_dinilai' => $belumDinilai,
            'per_predikat' => $jumlahPerPredikat,
            'persen_baik' => $totalDinilai > 0 ? (int) round($jumlahBaik / $totalDinilai * 100) : 0,
            // Persentase pegawai yang SAMA SEKALI belum ada penilaian triwulan
            // berjalan (bukan soal predikatnya jelek, tapi belum dinilai/belum
            // ngumpulin sama sekali) — dipakai buat chart "Unit Perlu Perhatian"
            // karena ini indikator yang lebih actionable buat direktur daripada
            // % capaian baik (lihat getChartUnitPerluPerhatian).
            'persen_belum_dinilai' => $totalPegawai > 0 ? (int) round($belumDinilai / $totalPegawai * 100) : 0,
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
     * Ambil data mentah dari API, di-cache. Kalau API gagal (timeout, 500,
     * dll), balikin array kosong + catat ke log, biar halaman tetap render
     * dalam bentuk empty-state, bukan error 500.
     */
    protected function fetchRaw(): array
    {
        return Cache::remember($this->cacheKey, config('services.sikawan.cache_ttl', 900), function () {
            $baseUrl = rtrim(config('services.sikawan.base_url'), '/');
            $endpoint = config('services.sikawan.evkin_endpoint');

            try {
                $response = Http::timeout(config('services.sikawan.timeout', 10))
                    ->acceptJson()
                    // SSL verification dimatikan cuma di environment local.
                    // Di production, ikutin SIKAWAN_VERIFY_SSL di .env (default true).
                    ->withOptions(['verify' => app()->isLocal() ? false : config('services.sikawan.verify_ssl', true)])
                    ->get($baseUrl . $endpoint);

                if (! $response->successful()) {
                    Log::warning('EvkinApiService: response tidak sukses', [
                        'status' => $response->status(),
                    ]);

                    return [];
                }

                $body = $response->json();

                // API-nya udah ngirim data per unit sebagai object { unit, pegawai: [...] },
                // jadi tinggal dipakai langsung tanpa perlu di-flatten ulang.
                return $body['data'] ?? [];
            } catch (\Throwable $e) {
                Log::error('EvkinApiService: gagal fetch API capaian kinerja', [
                    'message' => $e->getMessage(),
                ]);

                return [];
            }
        });
    }
}