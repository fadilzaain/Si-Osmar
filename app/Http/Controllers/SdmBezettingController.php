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
        $ringkasan = $this->bezettingService->getRingkasanPerUnit();

        foreach ($ringkasan as &$unit) {
            $unit['redistribusi'] = $this->bezettingService->getPeluangRedistribusiUntukUnit($unit['unit']);
        }
        unset($unit);

        $totalUnitKurang = collect($ringkasan)
            ->where('summary.status', BezettingApiService::STATUS_KURANG)
            ->count();

        return view('sdm-bezetting.index', [
            'ringkasan' => $ringkasan,
            'totalUnit' => count($ringkasan),
            'totalUnitKurang' => $totalUnitKurang,

            // Buat bagian ringkasan eksekutif 
            'eksekutif' => $this->bezettingService->getRingkasanEksekutif(),
            'kesimpulan' => $this->bezettingService->getKesimpulan(),
            'chartUnitKritis' => $this->bezettingService->getChartUnitKritis(6),
            'topJabatanKritis' => $this->bezettingService->getKekuranganPerJabatan(6),
            'peluangRedistribusiGlobal' => $this->bezettingService->getPeluangRedistribusi(8),
        ]);
    }

    /**
     * Halaman diagnostic sementara — nunjukin detail error terakhir pas
     * gagal fetch API SI KAWAN (kalau ada), tanpa perlu buka log file di
     * server. Diproteksi middleware 'auth' (lihat routes/web.php), jadi
     * cuma bisa diakses setelah login. Hapus route + method ini kalau
     * masalah API-nya udah kelar dan gak dibutuhin lagi.
     */
    public function diagnostic()
    {
        return response()->json([
            'last_error' => $this->bezettingService->getLastError(),
            'keterangan' => 'last_error null artinya fetch terakhir SUKSES (atau belum pernah dicoba sejak cache dibersihkan).',
        ]);
    }
}