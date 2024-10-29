<?php

namespace App\Models;

use App\Casts\CheckListStatus;
use App\Supports\DatesTimes\DateSupport;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class CheckList extends Init
{
    protected $guarded = ['id'];

    protected $fillable = [
        'code',
        'description',
        'status',
        'started',
        'delivered'
    ];

    protected $casts = [
        'status' => CheckListStatus::class
    ];

    public function collaborators(): BelongsToMany
    {
        return $this->belongsToMany(Collaborator::class, 'check_list_collaborator', 'check_list_id', 'collaborator_id')
            ->whereNull('check_list_collaborator.deleted_at');
    }

    public function tickets(): BelongsToMany
    {
        return $this->belongsToMany(Ticket::class, 'check_list_ticket', 'check_list_id', 'ticket_id')
            ->whereNull('check_list_ticket.deleted_at');
    }

    protected function started(): Attribute
    {
        return Attribute::make(
            get: fn($value) => (new DateSupport())->convertAmericaForBrazil($value),
            // set: fn($value) => (new DateSupport())->convertBrazilForAmerica($value),
        );
    }

    protected function delivered(): Attribute
    {
        return Attribute::make(
            get: fn($value) => (new DateSupport())->convertAmericaForBrazil($value),
            // set: fn($value) => (new DateSupport())->convertBrazilForAmerica($value),
        );
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function (Model $model) {
            foreach ($model->collaborators()->get() as $collaborator) {
                DB::table('check_list_collaborator')->where('collaborator_id', $collaborator->id)->update(['deleted_at' => now()]);
            }
            foreach ($model->tickets()->get() as $ticket) {
                DB::table('check_list_ticket')->where('ticket_id', $ticket->id)->update(['deleted_at' => now()]);
            }
        });
    }
}
