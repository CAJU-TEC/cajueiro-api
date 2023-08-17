<?php

namespace App\Observers;

use App\Models\Collaborator;
use App\Models\Ticket;
use Exception;
use Illuminate\Support\Facades\Auth;

class TicketObserver
{
    public function __construct(private Ticket $tickets)
    {
    }
    /**
     * Handle the Ticket "created" event.
     *
     * @param  \App\Models\Ticket  $ticket
     * @return void
     */
    public function saving(Ticket $ticket)
    {
        //
        if ($ticket->created_id) return;
        $ticket->created_id = Auth::user()->id;
    }

    public function created(Ticket $ticket)
    {
        //
        // $ticket->created_id = Auth::user()->id;
        // $ticket->save();
    }

    /**
     * Handle the Ticket "updated" event.
     *
     * @param  \App\Models\Ticket  $ticket
     * @return void
     */
    public function updated(Ticket $ticket)
    {
        //
    }

    /**
     * Handle the Ticket "deleted" event.
     *
     * @param  \App\Models\Ticket  $ticket
     * @return void
     */
    public function deleted(Ticket $ticket)
    {
        //
    }

    /**
     * Handle the Ticket "restored" event.
     *
     * @param  \App\Models\Ticket  $ticket
     * @return void
     */
    public function restored(Ticket $ticket)
    {
        //
    }

    /**
     * Handle the Ticket "force deleted" event.
     *
     * @param  \App\Models\Ticket  $ticket
     * @return void
     */
    public function forceDeleted(Ticket $ticket)
    {
        //
    }
}
