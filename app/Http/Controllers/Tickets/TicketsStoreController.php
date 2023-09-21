<?php

namespace App\Http\Controllers\Tickets;

use App\Events\TicketsListPusher;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\Ticket\TicketStoreRequest;
use App\Models\Ticket;
use App\Notifications\EmailTicketNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\HtmlString;
use DB;
use Illuminate\Support\Str;

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
        'validation' => 'VALIDAÇÃO',
    ];

    const TYPE = [
        'implementation' => 'IMPLEMENTAÇÃO',
        'maintenance' => 'MANUTENÇÃO',
    ];

    // const DUFY = [
    //     'yes' => 'SIM',
    //     'no' => 'NÃO',
    // ];

    const MIME_TYPE = [

        'txt' => 'text/plain',
        'htm' => 'text/html',
        'html' => 'text/html',
        'php' => 'text/html',
        'css' => 'text/css',
        'js' => 'javascript',
        'json' => 'json',
        'xml' => 'xml',
        'swf' => 'x-shockwave-flash',
        'flv' => 'video/x-flv',

        // images
        'png' => 'png',
        'jpeg' => 'jpeg',
        'jpg' => 'jpeg',
        'gif' => 'gif',
        'bmp' => 'bmp',
        'ico' => 'vnd.microsoft.icon',
        'tiff' => 'tiff',
        'tif' => 'tiff',
        'svg' => 'svg+xml',
        'svgz' => 'svg+xml',

        // archives
        'zip' => 'zip',
        'rar' => 'x-rar-compressed',
        'exe' => 'x-msdownload',
        'msi' => 'x-msdownload',
        'cab' => 'vnd.ms-cab-compressed',

        // audio/video
        'mp3' => 'audio/mpeg',
        'qt' => 'video/quicktime',
        'mov' => 'video/quicktime',

        // adobe
        'pdf' => 'pdf',
        'psd' => 'image/vnd.adobe.photoshop',
        'ai' => 'postscript',
        'eps' => 'postscript',
        'ps' => 'postscript',

        // ms office
        'doc' => 'msword',
        'docx' => 'vnd.openxmlformats-officedocument.wordprocessingml.document',
        'rtf' => 'rtf',
        'xls' => 'vnd.ms-excel',
        'xlsx' => 'vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt' => 'vnd.ms-powerpoint',

        // open office
        'odt' => 'vnd.oasis.opendocument.text',
        'ods' => 'vnd.oasis.opendocument.spreadsheet',
    ];

    public function __construct(private Ticket $ticket)
    {
    }

    public function __invoke(TicketStoreRequest $request)
    {
        try {
            DB::beginTransaction();

            $ticket = $this->ticket->create($request->only([
                'client_id',
                'collaborator_id',
                'impact_id',
                'code',
                'priority',
                'type',
                'dufy',
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
                'id' => $dataForSend->id,
                'code' => $dataForSend->code
            ];

            Notification::route('mail', [
                $dataForSend->client->email->description => $dataForSend->client->full_name,
            ])->notify(new EmailTicketNotification($project));

            event(new TicketsListPusher($dataForSend));

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
            DB::commit();
            return response()->json($ticket, 201);
        } catch (\Exception $th) {
            DB::rollBack();
            throw $th->getMessage();
        }
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
        $mime = explode('/', mime_content_type($imagem))[1];
        $extense = array_filter(self::MIME_TYPE, function ($value) use ($mime) {
            return $value == $mime;
        }, ARRAY_FILTER_USE_BOTH);

        return Str::ulid() . '.' . key($extense);
    }
}
