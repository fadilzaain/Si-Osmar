<?php

namespace App\Http\Controllers;

use App\Services\BezettingApiService;

class SdmBezettingController extends Controller
{
    public function __construct(
        protected BezettingApiService $bezettingService,
    ) {}

    public function index()
    {
        $ringkasan = $this->bezettingService->getRingkasanPerUnit();

        foreach ($ringkasan as &$unit) {
            $unit['redistribusi'] = $this->bezettingService->getPeluangRedistribusiUntukUnit($unit['unit']);
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