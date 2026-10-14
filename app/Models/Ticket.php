<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use App\Enums\Tickets\Status;
use App\Support\TicketAuditSummary;
use App\Traits\HasTelegramNotifications;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
use Carbon\Carbon;
use InvalidArgumentException;
use DB;

class Ticket extends Init implements Auditable
{
    use HasTelegramNotifications;
    use AuditableTrait {
        isEventAuditable as protected packageIsEventAuditable;
    }

    protected $auditExclude = ['deleted_at'];

    // audita criar, editar, apagar (exclusão lógica: deleted_at) e restaurar
    protected $auditEvents = ['created', 'updated', 'deleted', 'restored'];

    // apagar de vez (forceDelete) também não é auditado: só o apagar normal
    protected function isEventAuditable($event): bool
    {
        if ($event === 'deleted' && $this->isForceDeleting()) return false;

        return $this->packageIsEventAuditable($event);
    }

    /**
     * Completa a linha de auditoria: quem fez, número do protocolo, frase legível e a descrição
     * (que é grande) em colunas próprias, fora do JSON de valores.
     */
    public function transformAudit(array $data): array
    {
        $old = $data['old_values'] ?? [];
        $new = $data['new_values'] ?? [];

        if ($data['event'] === 'updated' && array_key_exists('message', $new)) {
            $data['message_before'] = $old['message'] ?? null;
            $data['message_after'] = $new['message'];
            $old['message'] = $new['message'] = null; // marca que mudou, sem repetir o texto
        } else {
            unset($old['message'], $new['message']);
        }

        // colunas que não guardamos (usuário fica só em user_name; o pacote sempre envia estas chaves)
        unset($data['user_id'], $data['user_type'], $data['tags']);

        // id e code já estão em auditable_id e ticket_code
        unset($old['id'], $new['id'], $old['code'], $new['code']);

        // criado/apagado/restaurado: o registro já está na tabela tickets, não repete aqui
        if (in_array($data['event'], ['created', 'deleted', 'restored'], true)) {
            $old = $new = [];
        }

        $data['old_values'] = $old;
        $data['new_values'] = $new;
        $data['ticket_code'] = $this->code ?? DB::table('tickets')->where('id', $this->id)->value('code');

        // a auditoria nunca deve impedir o salvamento: se a frase/nome falhar, grava sem eles
        try {
            $data['user_name'] = $this->resolveUser()?->name;
            $data['summary'] = TicketAuditSummary::build($data['event'], $old, $new);
        } catch (\Throwable $e) {
            \Log::error('Falha ao montar o resumo da auditoria do protocolo', ['ticket_id' => $this->id, 'erro' => $e->getMessage()]);
        }

        return $data;
    }

    protected $guarded = [];

    protected $fillable = [
        'client_id',
        'collaborator_id',
        'tester_id',
        'impact_id',
        'created_id',
        'code',
        'priority',
        'platform',
        'type',
        'dufy',
        'validated',
        'subject',
        'message',
        'status',
        'date_attribute_ticket',
        'time'
    ];

    protected $appends = [
        'letter',
        'dateFinishTicket',
        'statusCast',
        'timeForHuman',
        'testing',      
    ];

    protected $casts = [];

    public function getStatusCastAttribute()
    {
        $status = status::tryFrom($this->status);

        return $status ? [
            'description' => $status->description(),
            'color' => $status->color()
        ] : null;
    }

    public function getTestingAttribute(): bool
    {
    $comment = $this->latestComment;

    return $comment
        && $comment->status === 'test'
        && $comment->testing === true;
    }

    public function getTimeForHumanAttribute()
    {
        if ($this->time) {
            $minutes = round($this->time * 60); // Converte horas para minutos com arredondamento exato

            if ($minutes < 60) {
                return "{$minutes} minutos"; // Exibe minutos para valores menores que 1 hora
            } elseif ($minutes === 60) {
                return '1 hora'; // Caso especial para 1 hora exata
            } else {
                $remainingMinutes = $minutes % 60;
                $fullHours = floor($minutes / 60); // Horas completas

                // Retorna string diferenciando entre "X horas" e "X horas e Y minutos"
                return $remainingMinutes > 0
                    ? "{$fullHours} hora(s) e {$remainingMinutes} minutos"
                    : "{$fullHours} horas";
            }
        }
    }

    public function getLetterAttribute()
    {
        if ($this->subject)
            return strtoupper(substr($this->subject, 0, 1));
    }

    public function getDateFinishTicketAttribute()
    {
        if ($comment = $this->comments()->latest()->first()) {
            return $comment->created_at;
        }
        return null;
    }

    public function image()
    {
        return $this->morphOne(Image::class, 'imageable');
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
    public function latestComment()
    {
    return $this
        ->morphOne(Comment::class, 'commentable')
        ->latestOfMany('created_at');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_id');
    }

    public function collaborator()
    {
        return $this->belongsTo(Collaborator::class, 'collaborator_id');
    }

    public function tester()
    {
        return $this->belongsTo(Collaborator::class);
    }

    public function clientCorporate(): HasOneThrough
    {
        return $this->hasOneThrough(Corporate::class, Client::class, 'id', 'id', 'client_id', 'corporate_id');
    }

    public function impact()
    {
        return $this->belongsTo(Impact::class);
    }

    public function scopeStartsBefore(Builder $query, $date): Builder
    {
        return $query->whereDate('created_at', '=', Carbon::parse($date)->toDateString());
    }

    public function scopeStatusNot(Builder $query, $status): Builder
    {
        return $query->whereNotIn('status', Arr::wrap($status));
    }

    public function scopeToday(Builder $query, $date): Builder
    {
        return $query->whereHas('comments', function ($q) use ($date) {
            $q->whereDate('created_at', '=', Carbon::parse($date)->toDateString());
        });
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function (Model $model) {
            if (!empty($model->image?->uri)) {
                $file = 'images/' . $model->image->uri ?? '';
                if (Storage::disk('public')->exists($file)) {
                    Storage::disk('public')->delete($file);
                }
                $model->image()->delete();
            }
        });
    }
}
