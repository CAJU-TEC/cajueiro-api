<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Client extends Init
{

    protected $guarded = [];

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'address'
    ];

    protected $appends = [
        'full_name'
    ];

    public function getLetterAttribute()
    {
        if ($this->first_name)
            return strtoupper(substr($this->first_name, 0, 1));
    }

    public function getFullNameAttribute()
    {
        if ($this->first_name && $this->last_name)
            return $this->first_name . ' ' . $this->last_name;
    }

    public function image()
    {
        return $this->morphOne(Image::class, 'imageable');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function (Model $model) {
            $file = 'images/' . $model->image->uri ?? '';
            if (Storage::disk('public')->exists($file)) {
                Storage::disk('public')->delete($file);
            }
            $model->image()->delete();
        });
    }
}
