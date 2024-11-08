<?php

namespace App\Http\Controllers\Tickets;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketsTimeStoreController extends Controller
{
    public function __construct(private Ticket $ticket) {}

    public function __invoke(Request $request)
    {
        try {
            $ticket = $this->ticket->find($request->params['ticket_id']);
            $ticket->time = $request->params['data'];
            $ticket->update();
            return response()->json($ticket, 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }
}
