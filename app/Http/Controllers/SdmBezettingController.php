<?php

namespace App\Http\Controllers;

use App\Services\BezettingApiService;

class SdmBezettingController extends Controller
{
    public function __construct(
        protected BezettingApiService $bezettingService,
    ) {}

    public function index()
    {
        return view('sdm-bezetting.index', $this->buildViewData());
    }

    /**
     * Endpoint ringan buat auto-refresh (dipanggil JS lewat polling tiap
     * 1 menit — sdm-bezetting.js). Balikin HTML partial yang PERSIS SAMA dengan yang dipakai index(), biar gak ada
     * duplikasi markup antara load awal & live-update.
     *
     * Sengaja balikin HTML siap-pakai (bukan JSON) supaya JS tinggal swap
     * innerHTML tanpa perlu nge-render ulang tabel/chart/accordion dari
     * data mentah di sisi client.
     */
    public function data()
    {
        return view('sdm-bezetting.partials.content', $this->buildViewData());
    }

    /**
     * Data buat halaman Bezetting SDM. Dipakai bareng oleh index() (full
     * page) dan data() (partial buat polling), biar query dan susunan
     * datanya gak pernah kebeda antara dua entry point itu.
     */
    protected function buildViewData(): array
    {
        $ringkasan = $this->bezettingService->getRingkasanPerUnit();

        foreach ($ringkasan as &$unit) {
            $unit['redistribusi'] = $this->bezettingService->getPeluangRedistribusiUntukUnit($unit['unit']);
        }
        unset($unit);

        $totalUnitKurang = collect($ringkasan)
            ->where('summary.status', BezettingApiService::STATUS_KURANG)
            ->count();

        return [
            'ringkasan' => $ringkasan,
            'totalUnit' => count($ringkasan),
            'totalUnitKurang' => $totalUnitKurang,

            // Buat bagian ringkasan eksekutif
            'eksekutif' => $this->bezettingService->getRingkasanEksekutif(),
            'kesimpulan' => $this->bezettingService->getKesimpulan(),
            'chartUnitKritis' => $this->bezettingService->getChartUnitKritis(6),
            'topJabatanKritis' => $this->bezettingService->getKekuranganPerJabatan(6),
            'peluangRedistribusiGlobal' => $this->bezettingService->getPeluangRedistribusi(8),
        ];
    }

    /**
     * Halaman diagnostic sementara 
     */
    public function diagnostic()
    {
        return response()->json([
            'last_error' => $this->bezettingService->getLastError(),
            'keterangan' => 'last_error null artinya fetch terakhir SUKSES (atau belum pernah dicoba sejak cache dibersihkan).',
        ]);
    }
}