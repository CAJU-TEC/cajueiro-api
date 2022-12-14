<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Ticket extends Init
{
    protected $guarded = [];

    protected $fillable = [
        'client_id',
        'collaborator_id',
        'impact_id',
        'code',
        'code',
        'priority',
        'subject',
        'message',
        'status',
    ];

    protected $appends = [
        'letter'
    ];

    public function getLetterAttribute()
    {
        if ($this->subject)
            return strtoupper(substr($this->subject, 0, 1));
    }

    public function image()
    {
        return $this->morphOne(Image::class, 'imageable');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function collaborator()
    {
        return $this->belongsTo(Collaborator::class);
    }

    public function impact()
    {
        return $this->belongsTo(Impact::class);
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
                $model->image()->delete();
            }
        });
    }
}
