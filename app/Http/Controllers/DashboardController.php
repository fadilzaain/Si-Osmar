<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use App\Services\MonitoringDokumenService;
use App\Services\CutiApiService;
use App\Services\BezettingApiService;
use App\Services\EvkinApiService;
use App\Services\PelatihanApiService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $dashboardService;
    protected $monitoringDokumenService;
    protected $cutiApiService;
    protected $bezettingApiService;
    protected $evkinApiService;
    protected $pelatihanApiService;


    public function __construct(
        DashboardService $dashboardService,
        MonitoringDokumenService $monitoringDokumenService,
        CutiApiService $cutiApiService,
        BezettingApiService $bezettingApiService,
        EvkinApiService $evkinApiService,
        PelatihanApiService $pelatihanApiService,
    ) {
        $this->dashboardService = $dashboardService;
        $this->monitoringDokumenService = $monitoringDokumenService;
        $this->cutiApiService = $cutiApiService;
        $this->bezettingApiService = $bezettingApiService;
        $this->evkinApiService = $evkinApiService;
        $this->pelatihanApiService = $pelatihanApiService;
    }

    public function index(Request $request)
    {
        // Monitoring Dokumen 
        $dokumenEksekutif = $this->monitoringDokumenService->getRingkasanEksekutif();
        $dokumenChart = $this->monitoringDokumenService->getChartDistribusiStatus();
        $unitDokumenKritis = collect($this->monitoringDokumenService->getTopUnitKritis(1))->first();

        // Cuti : ringkasan eksekutif + donut chart status kesehatan cuti.
        $cutiEksekutif = $this->cutiApiService->getRingkasanEksekutif();

        // Capaian Kinerja 
        $ekinerjaEksekutif = $this->evkinApiService->getRingkasanEksekutif();
        $ekinerjaChartData = $this->evkinApiService->getChartCapaianKinerja();

        // Pelatihan buat isi tile Pelatihan di Dashboard Executive.
        $pelatihanEksekutif = $this->pelatihanApiService->getRingkasanEksekutif();
        $pelatihanChartPerUnit = $this->pelatihanApiService->getChartRataRataPerUnit(5, compact: true);
        // SDM layout
        $sdmEksekutif = $this->bezettingApiService->getRingkasanEksekutif();
        $sdmUnitKritisList = $this->bezettingApiService->getTopUnitKritis(3);

        return view('dashboard.index', compact(
            'dokumenEksekutif', 'dokumenChart', 'unitDokumenKritis',
            'cutiEksekutif',
            'ekinerjaEksekutif', 'ekinerjaChartData', 'pelatihanEksekutif', 'pelatihanChartPerUnit',
            'sdmEksekutif', 'sdmUnitKritisList'
        ));
    }
}