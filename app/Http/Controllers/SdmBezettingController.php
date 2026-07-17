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
}