<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobPlans extends Init
{
    protected $guarded = [];

    protected $fillable = [
        'team_id',
        'description',
        'value',
        'time',
        'note',
        'color',
        'badge_icon',
        'badge_color',
        'position',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
