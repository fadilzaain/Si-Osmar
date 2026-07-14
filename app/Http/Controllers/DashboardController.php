<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use App\Services\MonitoringDokumenService;
use App\Services\BezettingApiService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $dashboardService;
    protected $monitoringDokumenService;
    protected $bezettingApiService;

    public function __construct(
        DashboardService $dashboardService,
        MonitoringDokumenService $monitoringDokumenService,
        BezettingApiService $bezettingApiService,
    ) {
        $this->dashboardService = $dashboardService;
        $this->monitoringDokumenService = $monitoringDokumenService;
        $this->bezettingApiService = $bezettingApiService;
    }

    public function index(Request $request)
    {
        $ringkasan = $this->monitoringDokumenService->getRingkasanPerRuangan();
        $ekinerja = $this->dashboardService->getEkinerjaSummary();
        $pelatihan = $this->dashboardService->getPelatihanSummary();
        $sdm = $this->dashboardService->getSdmSummaryChart();
        $cuti = $this->dashboardService->getCutiSummary();

        $sdmRedistribusi = $this->bezettingApiService->getPeluangRedistribusi(4);
        $sdmTotalPeluang = count($this->bezettingApiService->getPeluangRedistribusi());

        $totalPegawai = array_sum(array_column($ringkasan, 'total_pegawai'));
        $totalBermasalah = array_sum(array_column($ringkasan, 'bermasalah'));
        $ruanganKritis = collect($ringkasan)->sortByDesc('bermasalah')->first();

        return view('dashboard.index', compact(
            'ringkasan', 'ekinerja', 'pelatihan', 'sdm', 'cuti',
            'sdmRedistribusi', 'sdmTotalPeluang',
            'totalPegawai', 'totalBermasalah', 'ruanganKritis'
        ));
    }
}