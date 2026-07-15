<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use App\Services\MonitoringDokumenService;
use App\Services\CutiApiService;
use App\Services\BezettingApiService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $dashboardService;
    protected $monitoringDokumenService;
    protected $cutiApiService;
    protected $bezettingApiService;

    public function __construct(
        DashboardService $dashboardService,
        MonitoringDokumenService $monitoringDokumenService,
        CutiApiService $cutiApiService,
        BezettingApiService $bezettingApiService,
    ) {
        $this->dashboardService = $dashboardService;
        $this->monitoringDokumenService = $monitoringDokumenService;
        $this->cutiApiService = $cutiApiService;
        $this->bezettingApiService = $bezettingApiService;
    }

    public function index(Request $request)
    {
        // Monitoring Dokumen — ringkasan eksekutif + data donut chart, plus
        // unit paling kritis buat catatan singkat di card.
        $dokumenEksekutif = $this->monitoringDokumenService->getRingkasanEksekutif();
        $dokumenChart = $this->monitoringDokumenService->getChartDistribusiStatus();
        $unitDokumenKritis = collect($this->monitoringDokumenService->getTopUnitKritis(1))->first();

        // Cuti — sama pola: ringkasan eksekutif + donut chart status kesehatan cuti.
        $cutiEksekutif = $this->cutiApiService->getRingkasanEksekutif();
        
        //
        $ekinerja = $this->dashboardService->getEkinerjaSummary();
        $pelatihan = $this->dashboardService->getPelatihanSummary();

        $sdmRedistribusi = $this->bezettingApiService->getPeluangRedistribusi(4);
        $sdmTotalPeluang = count($this->bezettingApiService->getPeluangRedistribusi());

        return view('dashboard.index', compact(
            'dokumenEksekutif', 'dokumenChart', 'unitDokumenKritis',
            'cutiEksekutif',
            'ekinerja', 'pelatihan',
            'sdmRedistribusi', 'sdmTotalPeluang'
        ));
    }
}