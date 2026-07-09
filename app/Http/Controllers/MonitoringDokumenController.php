<?php

namespace App\Http\Controllers;

use App\Services\MonitoringDokumenService;
use Illuminate\Http\Request;

class MonitoringDokumenController extends Controller
{
    public function __construct(protected MonitoringDokumenService $service) {}

    public function index(Request $request)
    {
        return view('monitoring-str-sip.index', [
            'ringkasan' => $this->service->getRingkasanPerRuangan(),
            'ruanganAktif' => $request->query('ruangan'),
        ]);
    }

    public function detail(string $ruangan)
    {
        return response()->json(
            $this->service->getDetailByRuangan($ruangan)
        );
    }
}