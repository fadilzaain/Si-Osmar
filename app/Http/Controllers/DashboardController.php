<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use App\Services\MonitoringDokumenService;
use App\Services\MutasiService;
use App\Models\Pegawai;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $dashboardService;
    protected $monitoringDokumenService;
    protected $mutasiService;

    public function __construct(
        DashboardService $dashboardService,
        MonitoringDokumenService $monitoringDokumenService,
        MutasiService $mutasiService,
    ) {
        $this->dashboardService = $dashboardService;
        $this->monitoringDokumenService = $monitoringDokumenService;
        $this->mutasiService = $mutasiService;
    }

    public function index(Request $request)
    {
        $ringkasan = $this->monitoringDokumenService->getRingkasanPerRuangan();
        $ekinerja = $this->dashboardService->getEkinerjaSummary();
        $pelatihan = $this->dashboardService->getPelatihanSummary();
        $sdm = $this->dashboardService->getSdmSummaryChart();
        $cuti = $this->dashboardService->getCutiSummary();

        $sdmRotasi = $this->mutasiService->getRecentGlobal(4);
        $sdmTotal = Pegawai::count();

        $totalPegawai = array_sum(array_column($ringkasan, 'total_pegawai'));
        $totalBermasalah = array_sum(array_column($ringkasan, 'bermasalah'));
        $ruanganKritis = collect($ringkasan)->sortByDesc('bermasalah')->first();

        return view('dashboard.index', compact(
            'ringkasan', 'ekinerja', 'pelatihan', 'sdm', 'cuti',
            'sdmRotasi', 'sdmTotal',
            'totalPegawai', 'totalBermasalah', 'ruanganKritis'
        ));
    }
}