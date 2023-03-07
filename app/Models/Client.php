<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Client extends Init
{

    protected $guarded = [];

    protected $fillable = [
        'corporate_id',
        'first_name',
        'last_name',
        'address'
    ];

    protected $appends = [
        'full_name',
        'letter'
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

    public function corporate()
    {
        return $this->belongsTo(Corporate::class);
    }

    public function image()
    {
        return $this->morphOne(Image::class, 'imageable');
    }

    public function email()
    {
        return $this->morphOne(Email::class, 'emailable');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function (Model $model) {
            if (!empty($model->image?->uri)) {
                $file = 'images/' . $model->image->uri ?? '';
                if (Storage::disk('public')->exists($file)) {
                    Storage::disk('public')->delete($file);
                }
            }
            $model->image()->delete();
        });
    }
}
