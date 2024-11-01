<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Facades\Storage;
use App\Enums\Tickets\Status;
use Carbon\Carbon;
use InvalidArgumentException;

class Ticket extends Init
{

    protected $guarded = [];

    protected $fillable = [
        'client_id',
        'collaborator_id',
        'impact_id',
        'created_id',
        'code',
        'priority',
        'type',
        'dufy',
        'subject',
        'message',
        'status',
        'date_attribute_ticket',
    ];

    protected $appends = [
        'letter',
        'dateFinishTicket',
        'statusCast'
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
