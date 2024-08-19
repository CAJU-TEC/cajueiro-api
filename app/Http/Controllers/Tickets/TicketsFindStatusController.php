<?php

namespace App\Http\Controllers\Tickets;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketsFindStatusController extends Controller
{
    public function __invoke(Request $request)
    {
        $tickets = Ticket::query()
            ->with('clientCorporate')
            ->whereHas('clientCorporate', function ($query) use ($request) {
                $query->where('corporates.id', $request->params['corporate_id']['id']);
            })
            ->whereIn('status', array_map(function ($item) {
                return $item['id'];
            }, $request->params['status']))
            ->get();
        return response()->json($tickets, 200);
    }
}
