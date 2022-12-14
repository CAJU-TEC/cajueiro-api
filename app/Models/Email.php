<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Email extends Init
{
    protected $guarded = [];
    protected $keyType = 'string';
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Model $model) {
            $model->setAttribute($model->getKeyName(), Str::uuid());
        });
    }
}
