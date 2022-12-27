<?php

namespace App\Http\Controllers\Tickets;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketsStoreController extends Controller
{
    public function __construct(private Ticket $ticket)
    {
    }

    public function __invoke(Request $request)
    {
        $ticket = $this->ticket->create($request->only([
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

        return response()->json($ticket, 201);
    }
}
