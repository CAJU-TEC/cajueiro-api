<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Comment extends Init
{

    protected $guarded = [];
    protected $keyType = 'string';
    public $incrementing = false;

    public function commentable()
    {
        return $this->morphTo();
    }

    public function image()
    {
        return $this->morphOne(Image::class, 'imageable');
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function collaborator()
    {
        return $this->belongsTo(Collaborator::class, 'collaborator_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Model $model) {
            $model->setAttribute($model->getKeyName(), Str::uuid());
        });
    }
}
