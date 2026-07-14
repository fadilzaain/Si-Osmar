<?php

namespace App\Http\Controllers;

use App\Services\BezettingApiService;
use App\Services\MutasiService;

class SdmBezettingController extends Controller
{
    public function __construct(
        protected BezettingApiService $bezettingService,
        protected MutasiService $mutasiService,
    ) {}

    public function index()
    {
        $ringkasan = $this->bezettingService->getRingkasanPerUnit();

        // Aktivitas rotasi per unit ditempel langsung ke tiap item ringkasan
        // di sini (bukan lazy-load terpisah) — jumlah unit & barisnya kecil,
        // jadi render sekaligus lebih smooth ketimbang round-trip AJAX
        // tiap kali card dibuka.
        foreach ($ringkasan as &$unit) {
            $unit['rotasi'] = $this->mutasiService->getRecentByUnit($unit['unit']);
        }
        unset($unit);

        $totalUnitKurang = collect($ringkasan)
            ->where('summary.status', BezettingApiService::STATUS_KURANG)
            ->count();

        return view('sdm-bezetting.index', [
            'ringkasan' => $ringkasan,
            'totalUnit' => count($ringkasan),
            'totalUnitKurang' => $totalUnitKurang,
        ]);
    }
}
