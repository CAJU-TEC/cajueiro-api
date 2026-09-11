<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Schedule extends Init
{
    protected $table = 'schedules';

    protected $guarded = ['id'];

    protected $fillable = [
        'title',
        'date',
        'start_time',
        'lunch_start_time',
        'lunch_duration_minutes',
        'end_time',
    ];

    public function collaborators(): BelongsToMany
    {
        return $this->belongsToMany(Collaborator::class, 'schedule_collaborator', 'schedule_id', 'collaborator_id')
            ->withPivot(['position'])
            ->orderByPivot('position');
    }
}
