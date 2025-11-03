<?php

// app/Http/Controllers/Reports/QA/TicketReportController.php
namespace App\Http\Controllers\Reports\QA;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\Request;

class TicketReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function byQA(Request $request)
    {
        return response()->json(
            $this->reportService->getQATicketsByStatus($request->get('month'), $request->get('year'))
        );
    }
}
