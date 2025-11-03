<?php

// app/Http/Controllers/Reports/Support/TicketReportController.php
namespace App\Http\Controllers\Reports\Support;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\Request;

class TicketReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function byCollaborator(Request $request)
    {
        return response()->json(
            $this->reportService->getSupportTicketsByCollaborator($request->get('month'), $request->get('year'))
        );
    }

    public function byClient(Request $request)
    {
        return response()->json(
            $this->reportService->getSupportTicketsByClient($request->get('month'), $request->get('year'))
        );
    }
}
