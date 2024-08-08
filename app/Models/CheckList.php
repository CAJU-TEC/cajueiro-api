<?php

namespace App\Models;

class CheckList extends Init
{
    protected $guarded = [];

    protected $fillable = [
        'code',
        'description',
    ];
}
