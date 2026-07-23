<?php

namespace App\Http\Controllers;

use App\Services\EvkinApiService;

class MonitoringEvkinController extends Controller
{
    public function __construct(
        protected EvkinApiService $evkinService,
    ) {}

    public function index()
    {
        $ringkasan = $this->evkinService->getRingkasanPerUnit();
        $eksekutif = $this->evkinService->getRingkasanEksekutif();

        return view('monitoring-evkin.index', [
            'ringkasan' => $ringkasan,
            'eksekutif' => $eksekutif,
            'kesimpulan' => $this->evkinService->getKesimpulan(),

            // Data siap pakai buat chart
            'chartDistribusiPredikat' => $this->evkinService->getChartDistribusiPredikat(),
            'chartUnitPerluPerhatian' => $this->evkinService->getChartUnitPerluPerhatian(8),

            // Peta warna predikat, tone badge, dikirim dari service biar satu sumber kebenaran.
            'tonePredikat' => EvkinApiService::TONE_PREDIKAT,
        ]);
    }
}