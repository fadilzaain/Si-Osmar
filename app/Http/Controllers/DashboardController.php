<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use App\Services\MonitoringDokumenService;
use App\Services\CutiApiService;
use App\Services\BezettingApiService;
use App\Services\EvkinApiService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $dashboardService;
    protected $monitoringDokumenService;
    protected $cutiApiService;
    protected $bezettingApiService;
    protected $evkinApiService;

    public function __construct(
        DashboardService $dashboardService,
        MonitoringDokumenService $monitoringDokumenService,
        CutiApiService $cutiApiService,
        BezettingApiService $bezettingApiService,
        EvkinApiService $evkinApiService,
    ) {
        $this->dashboardService = $dashboardService;
        $this->monitoringDokumenService = $monitoringDokumenService;
        $this->cutiApiService = $cutiApiService;
        $this->bezettingApiService = $bezettingApiService;
        $this->evkinApiService = $evkinApiService;
    }

    public function index(Request $request)
    {
        // Monitoring Dokumen — ringkasan eksekutif + data donut chart, plus
        // unit paling kritis buat catatan singkat di card.
        $dokumenEksekutif = $this->monitoringDokumenService->getRingkasanEksekutif();
        $dokumenChart = $this->monitoringDokumenService->getChartDistribusiStatus();
        $unitDokumenKritis = collect($this->monitoringDokumenService->getTopUnitKritis(1))->first();

        // Cuti : ringkasan eksekutif + donut chart status kesehatan cuti.
        $cutiEksekutif = $this->cutiApiService->getRingkasanEksekutif();

        // Capaian Kinerja : ringkasan eksekutif + donut chart, sumbernya
        // sama persis dengan halaman detail (monitoring-evkin), biar
        // angkanya selalu sinkron dan gak dobel logika di Blade.
        $ekinerjaEksekutif = $this->evkinApiService->getRingkasanEksekutif();
        $ekinerjaChartData = $this->evkinApiService->getChartCapaianKinerja();

        $pelatihan = $this->dashboardService->getPelatihanSummary();

        $sdmRedistribusi = $this->bezettingApiService->getPeluangRedistribusi(4);
        $sdmTotalPeluang = count($this->bezettingApiService->getPeluangRedistribusi());

        return view('dashboard.index', compact(
            'dokumenEksekutif', 'dokumenChart', 'unitDokumenKritis',
            'cutiEksekutif',
            'ekinerjaEksekutif', 'ekinerjaChartData', 'pelatihan',
            'sdmRedistribusi', 'sdmTotalPeluang'
        ));
    }
}