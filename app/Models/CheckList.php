<?php

namespace App\Models;

class CheckList extends Init
{
    protected $guarded = [];

    protected $fillable = [
        'code',
        'description',
    ];

    public function collaborators()
    {
        return $this->belongsToMany(Collaborator::class);
    }

    public function tickets()
    {
        return $this->belongsToMany(Ticket::class);
    }
}
