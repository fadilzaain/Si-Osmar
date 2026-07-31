<?php

namespace App\Http\Controllers;

use App\Services\PelatihanApiService;

class PelatihanController extends Controller
{
    public function __construct(
        protected PelatihanApiService $pelatihanService,
    ) {}

    public function index()
    {
        return view('pelatihan.index', [
            'ringkasan' => $this->pelatihanService->getRingkasanPerUnit(),
            'eksekutif' => $this->pelatihanService->getRingkasanEksekutif(),
            'kepesertaan' => $this->pelatihanService->getRingkasanKepesertaan(),
            'kesimpulan' => $this->pelatihanService->getKesimpulan(),
            'topPegawai' => $this->pelatihanService->getTopPegawai(8),
            'pegawaiJamPalingSedikit' => $this->pelatihanService->getPegawaiJamPalingSedikit(8),

            // Data siap pakai buat chart
            'chartRataRataPerUnit' => $this->pelatihanService->getChartRataRataPerUnit(8),
            'chartTopPegawai' => $this->pelatihanService->getChartTopPegawai(8),
        ]);
    }
}