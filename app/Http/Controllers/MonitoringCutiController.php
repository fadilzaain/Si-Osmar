<?php

namespace App\Http\Controllers;

use App\Services\CutiApiService;

class MonitoringCutiController extends Controller
{
    public function __construct(
        protected CutiApiService $cutiService,
    ) {}

    public function index()
    {
        $ringkasan = $this->cutiService->getRingkasanPerUnit();
        $eksekutif = $this->cutiService->getRingkasanEksekutif();

        return view('monitoring-cuti.index', [
            'ringkasan' => $ringkasan,
            'eksekutif' => $eksekutif,
            'kesimpulan' => $this->cutiService->getKesimpulan(),
            'topPegawaiKritis' => $this->cutiService->getTopPegawaiKritis(8),

            // Data siap pakai buat 2 chart di atas halaman.
            'chartDistribusiStatus' => $this->cutiService->getChartDistribusiStatus(),
            'chartTopPegawai' => $this->cutiService->getChartTopPegawai(8),
        ]);
    }
}
