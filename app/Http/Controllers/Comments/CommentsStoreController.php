<?php

namespace App\Http\Controllers\Comments;

use App\Events\NotificationTicketsPusher;
use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\EmailTicketNotification;
use App\Notifications\TicketsSuccessful;
use App\Supports\Arrays\Unique;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\HtmlString;
use Exception;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CommentsStoreController extends Controller
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


    public function __construct(private Comment $comment, private Ticket $ticket)
    {
    }

    public function __invoke(Request $request)
    {
        try {
            DB::beginTransaction();
            $ticket = $this->ticket->with(['comments.collaborator.user'])->findOrFail($request->get('ticket_id'));
            $collaborator = User::with(['collaborator'])->find(auth()->user()->id);

            $data = [
                'collaborator_id' => $collaborator->collaborator->id ?? $ticket->collaborator_id,
                'description' => $request->get('description'),
                'status' => $request->get('status'),
            ];

            $ticket->update([
                'status' => $request->status,
                'date_finish_ticket' => now()
            ]);

            throw_if(empty($data['description']), new Exception('Preencha o campo do comentário para interagir'));

            $comment = $ticket->comments()->create($data);

            $dataForSend = $ticket->with(['client'])->find($ticket->id);
            $project = [
                'subject' => '[#' . $dataForSend->code . '] ' . $dataForSend->subject,
                'greeting' => 'Olá, ' . $dataForSend->client->full_name,
                'body' => ($dataForSend->priority == 'yes') ? 'PRIORIDADE' : '',
                'status' => self::STATUS[$data['status']],
                'ticketText' => new HtmlString($data['description']),
                'thanks' => 'Obrigado pela sua atenção.',
                'actionText' => 'RESPONDER PROTOCOLO',
                'warning' => 'Caso tenha a necessidade de responder esse e-mail(protocolo). Por favor, faça-o clicando no link acima.',
                'actionURL' => route('tickets.index'),
                'priority' => $dataForSend->priority,
                'id' => $dataForSend->id,
                'code' => $dataForSend->code,
                'created_at' => $dataForSend->created_at->format('d/m/Y \à\s H:i\h')
            ];

            // if ($dataForSend->status === 'done') {
            //     Notification::route('mail', [
            //         $dataForSend->client->email->description => $dataForSend->client->full_name,
            //     ])->notify(new EmailTicketNotification($project));
            // }
            $collaboratorsForNotifications = (new Unique())->collaborators($ticket->comments);
            foreach ($collaboratorsForNotifications as $collaborator) {
                Notification::send($collaborator->collaborator->user, new TicketsSuccessful($project));
            }

            event(new NotificationTicketsPusher($collaboratorsForNotifications));

            if ($request->image) {
                foreach ($request->image as $imagem) {
                    $name = $this->nomearArquivo($imagem);
                    $uri = storage_path('app/public/images/') . $name;

                    $this->uploadFiles($imagem, $uri);
                    $comment->image()->create([
                        'uri' => $name
                    ]);
                }
            }

            // if ($request->image) {
            //     $name = $comment->id . '.' . explode(
            //         '/',
            //         explode(
            //             ':',
            //             substr(
            //                 $request->image,
            //                 0,
            //                 strpos($request->image, ';')
            //             )
            //         )[1]
            //     )[1];
            //     $uri = storage_path('app/public/images/') . $name;
            //     \Image::make($request->image)->save($uri);

            //     $comment->image()->updateOrCreate([
            //         'uri' => $name
            //     ]);
            // }

            DB::commit();
            return response()->json($comment, 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 500);
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
