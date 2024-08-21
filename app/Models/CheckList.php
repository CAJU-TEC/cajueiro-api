<?php

namespace App\Models;

use App\Supports\DatesTimes\DateSupport;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    public function collaborators(): BelongsToMany
    {
        return $this->belongsToMany(Collaborator::class, 'check_list_collaborator', 'collaborator_id', 'check_list_id');
    }

    public function tickets(): BelongsToMany
    {
        return $this->belongsToMany(Ticket::class);
    }

    protected function started(): Attribute
    {
        return Attribute::make(
            get: fn($value) => (new DateSupport())->convertAmericaForBrazil($value),
            set: fn($value) => (new DateSupport())->convertBrazilForAmerica($value),
        );
    }

    protected function delivered(): Attribute
    {
        return Attribute::make(
            get: fn($value) => (new DateSupport())->convertAmericaForBrazil($value),
            set: fn($value) => (new DateSupport())->convertBrazilForAmerica($value),
        );
    }
}
