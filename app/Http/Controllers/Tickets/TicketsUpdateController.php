<?php

namespace App\Http\Controllers\Tickets;

use App\Enums\Tickets\Status;
use App\Events\TicketsListPusher;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\Ticket\TicketUpdateRequest;
use App\Models\Ticket;
use App\Support\TicketAuditSummary;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use OwenIt\Auditing\Events\AuditCustom;

class TicketsUpdateController extends Controller
{
    private const FIELDS = [
        'client_id',
        'impact_id',
        'priority',
        'platform',
        'type',
        'dufy',
        'validated',
        'subject',
        'message',
    ];

    private const EXTENSIONS = [
        'png' => 'png',
        'jpeg' => 'jpg',
        'jpg' => 'jpg',
        'gif' => 'gif',
        'pdf' => 'pdf',
        'vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
    ];

    public function __invoke(TicketUpdateRequest $request, $id)
    {
        try {
            DB::beginTransaction();

            $ticket = Ticket::findOrFail($id);
            $data = $request->only(self::FIELDS);

            // web_mobile: o protocolo atual fica com uma plataforma e um novo protocolo recebe a outra
            $splitPlatform = ($data['platform'] ?? null) === 'web_mobile';
            $clonePlatform = null;
            if ($splitPlatform) {
                $clonePlatform = $ticket->platform === 'mobile' ? 'web' : 'mobile';
                $data['platform'] = $clonePlatform === 'mobile' ? 'web' : 'mobile';
            }

            // a auditoria é gravada pelo pacote (trait Auditable do model Ticket)
            $ticket->update($data);

            $clone = $splitPlatform ? $this->cloneTicket($ticket, $clonePlatform) : null;

            foreach ((array) $request->image as $image) {
                if (is_string($image) && str_starts_with($image, 'data:')) {
                    $this->storeImage($ticket, $image);
                    if ($clone) $this->storeImage($clone, $image);
                }
            }

            DB::commit();

            return response()->json(
                $ticket->fresh()->setAttribute('clone', $clone?->only(['id', 'code', 'platform'])),
                200
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    private function cloneTicket(Ticket $ticket, string $platform): Ticket
    {
        $clone = Ticket::create([
            'client_id' => $ticket->client_id,
            'impact_id' => $ticket->impact_id,
            'created_id' => $ticket->created_id,
            'priority' => $ticket->priority,
            'platform' => $platform,
            'type' => $ticket->type,
            'dufy' => $ticket->dufy,
            'validated' => $ticket->validated,
            'subject' => $ticket->subject,
            'message' => $ticket->message,
            'status' => Status::TODO->value,
        ])->fresh(); // recarrega para trazer o code gerado pelo banco

        try {
            // o pacote já gravou a linha "created" do novo protocolo: vira "split_created", com a origem
            $cloneNew = [
                'platform' => $platform,
                'created_from' => (string) $ticket->code,
            ];
            $clone->audits()->where('event', 'created')->update([
                'event' => 'split_created',
                'new_values' => json_encode($cloneNew),
                'summary' => TicketAuditSummary::build('split_created', [], $cloneNew),
            ]);

            // e o original registra qual protocolo ele gerou, para o rastro valer nos dois sentidos
            $ticket->auditEvent = 'split';
            $ticket->isCustomEvent = true;
            $ticket->auditCustomOld = [];
            $ticket->auditCustomNew = ['split_to' => (string) $clone->code];
            Event::dispatch(AuditCustom::class, [$ticket]);
            $ticket->isCustomEvent = false;
        } catch (\Throwable $e) {
            Log::error('Falha ao gravar a auditoria da divisão do protocolo', [
                'ticket_id' => $ticket->id,
                'erro' => $e->getMessage(),
            ]);
        }

        event(new TicketsListPusher($clone->load('client')));

        return $clone;
    }

    private function storeImage(Ticket $ticket, string $image): void
    {
        $mime = explode('/', explode(';', substr($image, 5))[0])[1] ?? '';
        $extension = self::EXTENSIONS[$mime] ?? null;
        if (!$extension) return;

        $name = Str::ulid() . '.' . $extension;
        list(, $content) = explode(',', $image);
        file_put_contents(storage_path('app/public/images/') . $name, base64_decode($content));

        $ticket->image()->create(['uri' => $name]);
    }
}
