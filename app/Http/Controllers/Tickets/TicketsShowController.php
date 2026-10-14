<?php

namespace App\Http\Controllers\Tickets;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;

class TicketsShowController extends Controller
{
    public function __construct(private Ticket $ticket)
    {
    }

    public function __invoke($id)
    {
        $ticket = $this->ticket->with([
            'impact',
            'client.email',
            'images',
            'user.collaborator.image',
            'collaborator.image',
            'tester.image',
            'latestComment',
            'comments' => function ($builder) {
                return $builder->with([
                    'collaborator.image',
                    'collaborator.email'
                ])
                    ->orderBy('created_at', 'desc');
            },
            'comments.collaborator.image',
            'comments.images',
        ])->findOrFail($id);
        $ticket->setAttribute('audit_notes', $this->auditNotes($ticket));

        return response()->json($ticket, 200);
    }

    // avisos discretos da tela: quem editou por último e quem restaurou, vindos da auditoria
    private function auditNotes(Ticket $ticket): array
    {
        $audits = DB::table('audits')
            ->where('auditable_type', $ticket->getMorphClass())
            ->where('auditable_id', $ticket->id)
            ->whereIn('event', ['updated', 'split', 'restored'])
            ->orderByDesc('id')
            ->get(['event', 'user_name', 'new_values', 'message_after', 'created_at']);

        // só conta como edição o que mexe nos campos do formulário (não troca de status/responsável)
        $editable = ['client_id', 'impact_id', 'priority', 'platform', 'type', 'dufy', 'validated', 'subject'];
        $edited = $audits->first(function ($audit) use ($editable) {
            if ($audit->event === 'split') return true;
            if ($audit->event !== 'updated') return false;
            $keys = array_keys((array) json_decode($audit->new_values, true));
            return $audit->message_after !== null || array_intersect($keys, $editable);
        });
        $restored = $audits->firstWhere('event', 'restored');

        $note = fn ($audit) => $audit ? ['user' => $audit->user_name, 'at' => $audit->created_at] : null;

        return ['edited' => $note($edited), 'restored' => $note($restored)];
    }
}
