<?php

namespace App\Http\Controllers\Tickets;

use App\Http\Controllers\Controller;
use App\Models\Ticket;

class TicketsRestoreController extends Controller
{
    // desfaz a exclusão lógica; a auditoria registra o evento "restored"
    public function __invoke($id)
    {
        $ticket = Ticket::onlyTrashed()->findOrFail($id);
        $ticket->restore();

        return response()->json(['id' => $ticket->id, 'code' => $ticket->code], 200);
    }
}
