<?php

namespace App\Http\Controllers\Tickets;

use App\Filters\AllowedNullableFilter;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class TicketsGraphDashboardController extends Controller
{
    private $month;

    public function __construct(private Ticket $tickets)
    {
        $this->month = null;
    }

    //
    public function __invoke(Request $request)
    {
        try {
            $this->month = !empty($request->get('month')) ? $request->get('month') : Carbon::now()->format('m');
            return $this->month;
            return response()->json(QueryBuilder::for(Ticket::class)
                ->allowedFilters([
                    'code',
                    'priority',
                    'status',
                    // AllowedFilter::custom('tickets.date_finish_ticket', new AllowedFinishedFilter()),
                    AllowedFilter::custom('tickets.collaborator_id', new AllowedNullableFilter()),
                ])
                ->select([
                    'tickets.*'
                ])
                ->leftJoin('comments', 'comments.commentable_id', '=', 'tickets.id')
                ->with([
                    'image',
                    'client.corporate.image',
                    'collaborator.email',
                    'collaborator.image',
                    'comments',
                    'impact',
                    'user.collaborator.image'
                ])
                ->when(!empty($this->month), function ($query) {
                    $query->whereMonth('comments.created_at', $this->month);
                })
                ->groupBy('tickets.id')
                ->get(), 200);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
}
