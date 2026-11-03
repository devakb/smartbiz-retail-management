<?php

namespace Modules\Report\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Modules\Report\Services\ReportService;

class DashboardController extends Controller
{
    public function __construct(private readonly ReportService $reportService) {}

    public function __invoke(): View
    {
        return view('report::dashboard', [
            'title' => 'Dashboard',
            'widgets' => $this->reportService->dashboardWidgets(),
        ]);
    }
}
