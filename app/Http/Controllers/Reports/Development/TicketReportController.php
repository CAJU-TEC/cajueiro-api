<?php

// app/Http/Controllers/Reports/Development/TicketReportController.php
namespace App\Http\Controllers\Reports\Development;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\Request;

class TicketReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function countByCollaborator(Request $request)
    {
        return response()->json(
            $this->reportService->getDevTicketsCountByCollaborator($request->get('month'), $request->get('year'))
        );
    }

    public function averageCompletionTime(Request $request)
    {
        return response()->json(
            $this->reportService->getDevAverageCompletionTimeByCollaborator($request->get('month'), $request->get('year'))
        );
    }
}
