<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;

class TrailMaterial extends Init
{
    protected $guarded = ['id'];

    protected $fillable = [
        'description',
        'url',
        'type',
        'materialable_id',
        'materialable_type',
    ];

    public function materialable(): MorphTo
    {
        return $this->morphTo();
    }
}
