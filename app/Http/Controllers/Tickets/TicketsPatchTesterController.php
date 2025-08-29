<?php

namespace App\Http\Controllers\Tickets;

use App\Http\Controllers\Controller;
use App\Models\Collaborator;
use App\Models\Ticket;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TicketsPatchTesterController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, $id)
    {
        try {

            $idUsuario = Collaborator::with('user')->find($request->get('collaborator_id'))->user->id ?? auth()->user()->id;

            $collaborator = User::with(['collaborator'])->find($idUsuario);

            $ticket = Ticket::find($id);
            $ticket->tester_id = $collaborator->collaborator->id ?? NULL;
            // $ticket->status = 'development';
            $ticket->update();

            return response()->json(Ticket::with([
                'collaborator',
                'tester',
                'comments',
            ])->find($id), Response::HTTP_OK);
        } catch (Exception $e) {
            response()->json($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
