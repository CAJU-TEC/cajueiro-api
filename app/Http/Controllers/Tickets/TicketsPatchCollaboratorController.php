<?php

namespace App\Http\Controllers\Tickets;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use DB;

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
            DB::beginTransaction();

            $collaborator = User::with(['collaborator'])->find(auth()->user()->id);

            $tickets = Ticket::where('collaborator_id', $collaborator->collaborator->id)
                ->where('status', 'pending')
                ->count();

            if ($tickets > 0) {
                return response()->json([
                    'message' => 'Você possui protocolos pendentes'
                ], 500);
            }

            $ticket = Ticket::find($id);
            $ticket->collaborator_id = $collaborator->collaborator->id ?? NULL;
            $ticket->status = 'development';
            $ticket->date_attribute_ticket = NOW();
            $ticket->update();
            return response()->json(Ticket::with([
                'collaborator',
                'comments',
            ])->find($id), 200);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            response()->json($e->getMessage(), 500);
        }
    }
}
