<?php

namespace App\Http\Controllers\Tickets;

use App\Events\TicketsListEvent;
use App\Events\TicketsListPusher;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\EmailTicketNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\HtmlString;
use DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Pusher\Pusher;

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
        // try {
        //     DB::beginTransaction();
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

        event(new TicketsListPusher($ticket));

        if ($request->image) {
            foreach ($request->image as $imagem) {
                $name = $this->nomearArquivo($imagem);
                $uri = storage_path('app/public/images/') . $name;

                $this->uploadFiles($imagem, $uri);
                $ticket->image()->create([
                    'uri' => $name
                ]);
            }
        }
        // DB::commit();
        return response()->json($ticket, 201);
        // } catch (\Exception $th) {
        //     DB::rollBack();
        //     throw $th->getMessage();
        // }
    }

    protected function uploadFiles($upload, $uri)
    {
        list(, $upload) = explode(';', $upload);
        list(, $upload) = explode(',', $upload);
        $upload = base64_decode($upload);
        file_put_contents($uri, $upload);
    }

    protected function nomearArquivo($imagem)
    {
        return Str::ulid() . '.' . explode(
            '/',
            explode(
                ':',
                substr(
                    $imagem,
                    0,
                    strpos($imagem, ';')
                )
            )[1]
        )[1];
    }
}
