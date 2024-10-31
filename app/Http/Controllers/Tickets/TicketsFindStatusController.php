<?php

namespace App\Http\Controllers\Tickets;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketsFindStatusController extends Controller
{
    public function __invoke(Request $request)
    {
        $tickets = Ticket::query();

        if ($request->has('params.status') && !empty($request->params['status'])) {
            $statusIds = array_map(function ($item) {
                return $item['id'];
            }, $request->params['status']);

            $tickets->whereIn('status', $statusIds);
        }

        if ($request->has('params.corporate_id') && !empty($request->params['corporate_id'])) {
            $corporateIds = array_map(function ($item) {
                return $item['id'];
            }, $request->params['corporate_id']);

            $tickets->whereHas('clientCorporate', function ($query) use ($corporateIds) {
                $query->whereIn('corporates.id', $corporateIds);
            });
        }


        if (empty($request->params['status']) && empty($request->params['corporate_id'])) {
            return response()->json(null, 200);
        }

        return response()->json($tickets->get(), 200);
    }
}
