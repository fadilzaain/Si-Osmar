<?php

namespace App\Http\Controllers;

use App\Services\MonitoringDokumenService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MonitoringDokumenController extends Controller
{
    public function __construct(protected MonitoringDokumenService $service) {}

    public function index(Request $request)
    {
        $ruanganAktif = $request->query('ruangan');

        return view('monitoring-str-sip.index', [
            'unitList' => $this->service->getUnitList(),
            'eksekutif' => $this->service->getRingkasanEksekutif(),
            'kesimpulan' => $this->service->getKesimpulan(),
            'topUnitKritis' => $this->service->getTopUnitKritis(),
            // Dikirim sebagai slug karena dashboard card ngirim nama unit
            // penuh lewat query ?ruangan=, sedangkan elemen di halaman ini
            // di-ID-in pakai slug (id="unit-{slug}").
            'ruanganAktifSlug' => $ruanganAktif ? Str::slug($ruanganAktif) : null,
        ]);
    }
}