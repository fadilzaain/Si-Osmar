<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * CutiApiService
 *
 * Narik data sisa cuti pegawai (per unit, per jenis cuti) dari API eksternal
 * SIKAWAN. Response mentahnya flat per baris (satu baris =
 * satu pegawai + satu jenis cuti), service ini tanggung jawab
 * ngelompokkin ulang jadi per-unit -> per-pegawai -> rincian jenis cuti,
 * sekaligus ngitung status kesehatan cuti tiap pegawai.
 *
 * Sama kayak BezettingApiService: di-cache biar gak nembak API tiap request,
 * dan ada fallback array kosong kalau API-nya lagi bermasalah — dashboard
 * tetap render, cuma nunjukin empty-state.
 */
class CutiApiService
{
    public const STATUS_KRITIS = 'KRITIS';
    public const STATUS_PERHATIAN = 'PERHATIAN';
    public const STATUS_NORMAL = 'NORMAL';

    /**
     * Jenis cuti yang dipakai sebagai acuan status kesehatan pegawai.
     * Cuma jenis ini yang punya jatah/kuota tahunan yang jelas — jenis lain
     */
    protected const JENIS_CUTI_UTAMA = 'Cuti Tahunan';

    /** Ambang persentase terpakai buat status "perlu perhatian". */
    protected const AMBANG_PERSEN_PERHATIAN = 75;

    protected string $cacheKey = 'cuti.sisa-cuti.raw';

    /**
     * Ringkasan per unit
     * Tiap unit sudah dibungkus daftar pegawai (rincian per jenis
     * cuti) + summary status, biar Blade tinggal render tanpa ngitung
     */
    public function getRingkasanPerUnit(): array
    {
        $raw = $this->fetchRaw();

        $perUnit = collect($raw)->groupBy('unit');

        $ringkasan = $perUnit->map(function ($rows, $namaUnit) {
            $pegawai = $this->kelompokkanPegawai($rows);

            return [
                'unit' => $namaUnit,
                'slug' => Str::slug($namaUnit),
                'pegawai' => $pegawai,
                'summary' => $this->summarizeUnit($pegawai),
            ];
        })->values()->all();

        // Unit dengan pegawai paling banyak berstatus KRITIS ditaruh paling atas
        usort($ringkasan, fn ($a, $b) => $b['summary']['jumlah_kritis'] <=> $a['summary']['jumlah_kritis']
            ?: $b['summary']['jumlah_perhatian'] <=> $a['summary']['jumlah_perhatian']);

        return $ringkasan;
    }

    /**
     * Ringkasan eksekutif buat KPI card di paling atas halaman.
     */
    public function getRingkasanEksekutif(): array
    {
        $ringkasan = $this->getRingkasanPerUnit();
        $semuaPegawai = collect($ringkasan)->flatMap(fn ($u) => $u['pegawai']);

        $totalPegawai = $semuaPegawai->count();
        $jumlahKritis = $semuaPegawai->where('status', self::STATUS_KRITIS)->count();
        $jumlahPerhatian = $semuaPegawai->where('status', self::STATUS_PERHATIAN)->count();
        $jumlahNormal = $totalPegawai - $jumlahKritis - $jumlahPerhatian;

        // Rata-rata persentase cuti tahunan terpakai, cuma dihitung dari
        // pegawai yang punya jatah > 0 (biar gak bias ke 0 karena pegawai
        // tanpa jatah cuti tahunan).
        $rataRataPersen = (int) round(
            $semuaPegawai->where('punya_jatah_utama', true)->avg('persen_terpakai') ?? 0
        );

        return [
            'total_pegawai' => $totalPegawai,
            'total_unit' => count($ringkasan),
            'jumlah_kritis' => $jumlahKritis,
            'jumlah_perhatian' => $jumlahPerhatian,
            'jumlah_normal' => max(0, $jumlahNormal),
            'rata_rata_persen_terpakai' => $rataRataPersen,
        ];
    }

    /**
     * Satu kalimat kesimpulan naratif inti insight dari halaman
     */
    public function getKesimpulan(): string
    {
        $eksekutif = $this->getRingkasanEksekutif();

        if ($eksekutif['total_pegawai'] === 0) {
            return 'Belum ada data cuti pegawai yang bisa ditampilkan saat ini.';
        }

        $teks = "Dari {$eksekutif['total_pegawai']} pegawai di {$eksekutif['total_unit']} unit, "
            . "{$eksekutif['jumlah_kritis']} pegawai sudah habis jatah cuti tahunannya "
            . "dan {$eksekutif['jumlah_perhatian']} pegawai lagi mendekati batas "
            . "(rata-rata pemakaian {$eksekutif['rata_rata_persen_terpakai']}%).";

        if ($eksekutif['jumlah_kritis'] > 0) {
            $topUnit = collect($this->getRingkasanPerUnit())->sortByDesc('summary.jumlah_kritis')->first();
            if ($topUnit && $topUnit['summary']['jumlah_kritis'] > 0) {
                $teks .= " Unit dengan pegawai kritis terbanyak: {$topUnit['unit']} ({$topUnit['summary']['jumlah_kritis']} pegawai).";
            }
        } else {
            $teks .= ' Secara umum pemakaian cuti masih dalam batas wajar.';
        }

        return $teks;
    }

    /**
     * Daftar pegawai paling kritis lintas semua unit — dipakai buat ranking
     * list & bar chart "pemakaian cuti tertinggi" di dashboard.
     *
     * @param int|null $limit Batasi jumlah hasil. Null = semua.
     */
    public function getTopPegawaiKritis(?int $limit = 8): array
    {
        $semuaPegawai = collect($this->getRingkasanPerUnit())
            ->flatMap(fn ($u) => collect($u['pegawai'])->map(fn ($p) => [...$p, 'unit' => $u['unit']]))
            ->filter(fn ($p) => $p['punya_jatah_utama'])
            ->sortByDesc('persen_terpakai')
            ->values();

        return $limit ? $semuaPegawai->take($limit)->all() : $semuaPegawai->all();
    }

    /**
     * Data siap pakai buat donut chart distribusi status (Normal / Perhatian / Kritis).
     */
    public function getChartDistribusiStatus(): array
    {
        $eksekutif = $this->getRingkasanEksekutif();

        return [
            'series' => [$eksekutif['jumlah_normal'], $eksekutif['jumlah_perhatian'], $eksekutif['jumlah_kritis']],
            'labels' => ['Normal', 'Perlu Perhatian', 'Kritis'],
            'colors' => ['success', 'warning', 'danger'],
            'size' => 168,
            'totalValue' => $eksekutif['total_pegawai'],
            'totalLabel' => 'Pegawai',
        ];
    }

    /**
     * Data siap pakai buat horizontal bar chart "Top pemakaian cuti tahunan" 
     */
    public function getChartTopPegawai(int $limit = 8): array
    {
        $top = collect($this->getTopPegawaiKritis($limit))->reverse()->values();

        return [
            'labels' => $top->map(fn ($p) => Str::limit($p['nama'], 22))->all(),
            'series' => $top->map(fn ($p) => $p['persen_terpakai'])->all(),
            'seriesName' => '% Terpakai',
            'height' => max(220, $top->count() * 34),
        ];
    }

    /**
     * Kelompokkan baris flat (satu baris = satu pegawai + satu jenis cuti)
     * jadi satu entri per pegawai, dengan rincian semua jenis cuti yang dia
     * punya + status kesehatan cuti tahunannya.
     */
    protected function kelompokkanPegawai($rows): array
    {
        return collect($rows)
            ->groupBy('pegawai_id')
            ->map(function ($rowsPegawai) {
                $rincian = collect($rowsPegawai)->map(fn ($r) => [
                    'jenis_cuti' => $r['jenis_cuti'],
                    'jatah_cuti' => (int) $r['jatah_cuti'],
                    'cuti_diambil' => (int) $r['cuti_diambil'],
                    'sisa_cuti' => (int) $r['sisa_cuti'],
                    'persen_terpakai' => $this->hitungPersenTerpakai((int) $r['jatah_cuti'], (int) $r['cuti_diambil']),
                ])->all();

                $utama = collect($rincian)->firstWhere('jenis_cuti', self::JENIS_CUTI_UTAMA);
                $punyaJatahUtama = $utama && $utama['jatah_cuti'] > 0;

                $first = $rowsPegawai->first();

                return [
                    'pegawai_id' => $first['pegawai_id'],
                    'nama' => trim($first['nama']),
                    'tahun' => $first['tahun'],
                    'inisial' => $this->buatInisial($first['nama']),
                    'rincian' => $rincian,
                    // Ringkasan cuti tahunan — dipakai buat progress bar & badge utama.
                    'jatah_utama' => $utama['jatah_cuti'] ?? 0,
                    'diambil_utama' => $utama['cuti_diambil'] ?? 0,
                    'sisa_utama' => $utama['sisa_cuti'] ?? 0,
                    'persen_terpakai' => $utama['persen_terpakai'] ?? 0,
                    'punya_jatah_utama' => $punyaJatahUtama,
                    'status' => $punyaJatahUtama
                        ? $this->resolveStatus($utama['sisa_cuti'], $utama['persen_terpakai'])
                        : self::STATUS_NORMAL,
                ];
            })
            ->sortBy('nama')
            ->values()
            ->all();
    }

    protected function hitungPersenTerpakai(int $jatah, int $diambil): int
    {
        if ($jatah <= 0) {
            return 0;
        }

        return (int) min(100, round($diambil / $jatah * 100));
    }

    /**
     * Status kesehatan cuti tahunan seorang pegawai:
     * - KRITIS kalau sisa cuti sudah habis (0 atau minus / over-quota)
     * - PERHATIAN kalau pemakaian sudah >= ambang (default 75%) walau sisa masih ada
     * - NORMAL kalau masih jauh dari ambang
     */
    protected function resolveStatus(int $sisaCuti, int $persenTerpakai): string
    {
        if ($sisaCuti <= 0) {
            return self::STATUS_KRITIS;
        }

        if ($persenTerpakai >= self::AMBANG_PERSEN_PERHATIAN) {
            return self::STATUS_PERHATIAN;
        }

        return self::STATUS_NORMAL;
    }

    /**
     * Ringkasan angka + status keseluruhan satu unit, dari daftar pegawai
     * yang sudah dikelompokkan.
     */
    protected function summarizeUnit(array $pegawai): array
    {
        $collection = collect($pegawai);
        $jumlahKritis = $collection->where('status', self::STATUS_KRITIS)->count();
        $jumlahPerhatian = $collection->where('status', self::STATUS_PERHATIAN)->count();

        return [
            'total_pegawai' => $collection->count(),
            'jumlah_kritis' => $jumlahKritis,
            'jumlah_perhatian' => $jumlahPerhatian,
            'jumlah_normal' => $collection->count() - $jumlahKritis - $jumlahPerhatian,
            'rata_rata_persen_terpakai' => (int) round(
                $collection->where('punya_jatah_utama', true)->avg('persen_terpakai') ?? 0
            ),
            // Status keseluruhan unit buat badge header accordion — sama
            // prioritas kayak status pegawai: KRITIS > PERHATIAN > NORMAL.
            'status' => match (true) {
                $jumlahKritis > 0 => self::STATUS_KRITIS,
                $jumlahPerhatian > 0 => self::STATUS_PERHATIAN,
                default => self::STATUS_NORMAL,
            },
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
     * dll), balikin array kosong + catat ke log, biar dashboard tetap render
     * dalam bentuk empty-state, bukan error 500.
     */
    protected function fetchRaw(): array
    {
        return Cache::remember($this->cacheKey, config('services.sikawan.cache_ttl', 900), function () {
            $baseUrl = rtrim(config('services.sikawan.base_url'), '/');
            $endpoint = config('services.sikawan.cuti_endpoint');

            try {
                $response = Http::timeout(config('services.sikawan.timeout', 10))
                    ->acceptJson()
                    // SSL verification dimatikan cuma di environment local.
                    // Di production, ikutin SIKAWAN_VERIFY_SSL di .env (default true).
                    ->withOptions(['verify' => app()->isLocal() ? false : config('services.sikawan.verify_ssl', true)])
                    ->get($baseUrl . $endpoint);

                if (! $response->successful()) {
                    Log::warning('CutiApiService: response tidak sukses', [
                        'status' => $response->status(),
                    ]);

                    return [];
                }

                $body = $response->json();

                // API-nya ngirim data per unit sebagai object { unit, pegawai: [...] },
                // bukan flat per baris,kita ratakan dulu di sini jadi satu baris
                // per (pegawai, jenis_cuti) supaya gampang dikelompokkan ulang di atas.
                $flat = [];
                foreach (($body['data'] ?? []) as $unitBlock) {
                    foreach (($unitBlock['pegawai'] ?? []) as $row) {
                        $flat[] = array_merge($row, ['unit' => $unitBlock['unit']]);
                    }
                }

                return $flat;
            } catch (\Throwable $e) {
                Log::error('CutiApiService: gagal fetch API sisa cuti', [
                    'message' => $e->getMessage(),
                ]);

                return [];
            }
        });
    }
}