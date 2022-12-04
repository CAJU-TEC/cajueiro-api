<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Client extends Init
{

    protected $guarded = [];

    protected $fillable = [
        'description',
        'email',
        'address'
    ];

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
