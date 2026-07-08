<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller; 
use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index(Request $request)
    {
        $metrics = $this->dashboardService->getMetrics();
        $distribution = $this->dashboardService->getStaffDistribution();
        $trend = $this->dashboardService->getStaffTrend();
        $activities = $this->dashboardService->getRecentActivities();

        return view('dashboard.index', compact('metrics', 'distribution', 'trend', 'activities'));
    }
}