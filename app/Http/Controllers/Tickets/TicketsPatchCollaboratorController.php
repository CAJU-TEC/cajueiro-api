<?php

namespace App\Http\Controllers\Tickets;

use App\Http\Controllers\Controller;
use App\Models\Impact;
use App\Models\Ticket;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;

class TicketsPatchCollaboratorController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, $id)
    {
        //
        try {
            $collaborator = User::with(['collaborator'])->find(auth()->user()->id);
            $ticket = Ticket::find($id);
            $ticket->collaborator_id = $collaborator->collaborator->id ?? NULL;
            $ticket->status = 'development';
            $ticket->date_attribute_ticket = NOW();
            $ticket->update();
            return response()->json(Ticket::with(['collaborator'])->find($id), 200);
        } catch (Exception $e) {
            response()->json($e->getMessage(), 500);
        }
    }
}
