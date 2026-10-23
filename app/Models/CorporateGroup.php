<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class CorporateGroup extends Init
{
    protected $fillable = ['nome'];

    public function corporates(): HasMany
    {
        return $this->hasMany(Corporate::class);
    }
}
