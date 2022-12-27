<?php

namespace App\Http\Controllers\Tickets;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use DomainException;
use Illuminate\Http\Request;

class TicketsUpdateController extends Controller
{
    //
    public function __invoke(Request $request, $id)
    {
        try {
            $ticket = Ticket::with(['image'])->find($id);
            $ticket->update($request->only([
                'client_id',
                'collaborator_id',
                'impact_id',
                'code',
                'code',
                'priority',
                'subject',
                'message',
                'status',
            ]));

            if ($request->image) {
                $name = $ticket->id . '.' . explode(
                    '/',
                    explode(
                        ':',
                        substr(
                            $request->image,
                            0,
                            strpos($request->image, ';')
                        )
                    )[1]
                )[1];
                $uri = storage_path('app/public/images/') . $name;
                \Image::make($request->image)->save($uri);

                $ticket->image()->updateOrCreate([
                    'uri' => $name
                ]);
            }

            return response()->json($ticket, 200);
        } catch (DomainException $e) {
            return response()->json($e->getMessage(), 422);
        }
    }
}
