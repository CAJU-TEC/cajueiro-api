<?php

namespace App\Http\Controllers\Tickets;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
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

    public function __construct(private Ticket $ticket)
    {
        $this->pdf = PDF::loadHTML('');
    }

    public function __invoke(Request $request, $id)
    {
        //
        try {
            $ticket = $this->ticket->with(['impact', 'user'])->find($id);
            $this->pdf->loadView('reports.kanban', [
                'payload' => [
                    'ticket' => $ticket
                ]
            ]);
            $this->pdf->setOptions([
                'page-size' => 'a6',
                'margin-top' => 5,
                'margin-bottom' => 5,
                'margin-left' => 5,
                'margin-right' => 5,
            ]);
            return $this->pdf->stream();
        } catch (Exception $e) {
            return response()->json($e->getMessage());
        }
    }
}
