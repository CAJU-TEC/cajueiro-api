<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Duty extends Init
{
    protected $guarded = [];
    protected $keyType = 'string';
    protected $table = "duties";
    public $incrementing = false;

    public function dutyable()
    {
        return $this->morphTo();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Model $model) {
            $model->setAttribute($model->getKeyName(), Str::uuid());
        });
    }
}
