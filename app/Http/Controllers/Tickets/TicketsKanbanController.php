<?php

namespace App\Http\Controllers\Tickets;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use PDF;

class TicketsKanbanController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    private $pdf;

    public function __construct()
    {
        $this->pdf = PDF::loadHTML('');
    }

    public function __invoke(Request $request)
    {
        //
        try {
            $this->pdf->loadView('reports.kanban');
            $this->pdf->setOptions([
                'page-width' => '6.5in',
                'disable-smart-shrinking' => true,
                'page-size' => 'a6'
            ]);
            return $this->pdf->stream();
        } catch (Exception $e) {
            return response()->json($e->getMessage());
        }
    }
}
