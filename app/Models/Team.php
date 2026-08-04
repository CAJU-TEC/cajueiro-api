<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Init
{
    protected $table = 'teams';

    protected $guarded = ['id'];

    protected $fillable = [
        'name',
        'description',
        'color',
    ];

    public function collaborators(): HasMany
    {
        return $this->hasMany(Collaborator::class);
    }

    public function jobPlans(): HasMany
    {
        return $this->hasMany(JobPlans::class)->orderBy('position');
    }

    public function trails(): HasMany
    {
        return $this->hasMany(Trail::class);
    }

    public function getLetterAttribute()
    {
        if ($this->name)
            return strtoupper(substr($this->name, 0, 1));
    }
}
