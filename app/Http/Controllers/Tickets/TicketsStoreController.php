<?php

namespace App\Http\Controllers\Tickets;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\EmailTicketNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\HtmlString;
use DB;

class TicketsStoreController extends Controller
{
    const STATUS = [
        'backlog' => 'AGUARDANDO',
        'todo' => 'A FAZER',
        'analyze' => 'ANALISE',
        'development' => 'DESENVOLVIMENTO',
        'test' => 'TESTE',
        'pending' => 'PENDENTE',
        'done' => 'FINALIZADO',
    ];

    public function __construct(private Ticket $ticket)
    {
    }

    public function __invoke(Request $request)
    {
        try {
            // DB::beginTransaction();
            $ticket = $this->ticket->create($request->only([
                'client_id',
                'collaborator_id',
                'impact_id',
                'code',
                'priority',
                'subject',
                'message',
                'status',
            ]));
            return $request->all();

            $dataForSend = $ticket->with(['client'])->find($ticket->id);

            $project = [
                'subject' => '[#' . $dataForSend->code . '] ' . $dataForSend->subject,
                'greeting' => 'Olá, ' . $dataForSend->client->full_name,
                'body' => ($dataForSend->priority == 'yes') ? 'PRIORIDADE' : '',
                'status' => self::STATUS[$dataForSend->status],
                'ticketText' => new HtmlString($dataForSend->message),
                'thanks' => 'Obrigado pela sua atenção.',
                'actionText' => 'RESPONDER PROTOCOLO',
                'warning' => 'Caso tenha a necessidade de responder esse e-mail(protocolo). Por favor, faça-o clicando no link acima.',
                'actionURL' => route('tickets.index'),
                'priority' => $dataForSend->priority,
                'id' => $dataForSend->id
            ];

            Notification::route('mail', [
                $dataForSend->client->email->description => $dataForSend->client->full_name,
            ])->notify(new EmailTicketNotification($project));


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
            // DB::beginTransaction();
            return response()->json($ticket, 201);
        } catch (\Exception $th) {
            // DB::rollBack();
            throw $th->getMessage();
        }
    }
}
