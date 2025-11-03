<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Reports\Support\TicketReportController as SupportReport;
use App\Http\Controllers\Reports\Development\TicketReportController as DevReport;
use App\Http\Controllers\Reports\QA\TicketReportController as QAReport;

Route::prefix('reports')->group(function () {
    // Suporte
    Route::get('support/tickets-por-colaborador', [SupportReport::class, 'byCollaborator']);
    Route::get('support/tickets-por-cliente', [SupportReport::class, 'byClient']);

    // Desenvolvimento
    Route::get('development/quantidade', [DevReport::class, 'countByCollaborator']);
    Route::get('development/tempo-medio', [DevReport::class, 'averageCompletionTime']);

    // QA
    Route::get('qa/tickets-por-qa', [QAReport::class, 'byQA']);
});
