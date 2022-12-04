<?php

namespace App\Models;

class JobPlans extends Init
{
    protected $guarded = [];

    protected $fillable = [
        'description',
        'value',
        'time',
        'note',
        'color',
    ];
}
