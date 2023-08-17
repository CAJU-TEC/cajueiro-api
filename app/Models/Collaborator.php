<?php

namespace App\Models;

use App\Supports\DatesTimes\DateSupport;
use App\Supports\Documents\Cnpj;
use App\Supports\Documents\Cpf;
use App\Supports\Documents\DocumentsSupport;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Collaborator extends Init
{

    protected $guarded = [];

    protected $fillable = [
        'user_id',
        'jobplan_id',
        'first_name',
        'last_name',
        'formation',
        'birth',
        'entrance',
        'egress',
        'cpf',
        'cnpj',
        'email',
        'address',
        'postal',
        'number',
        'pix'
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

    public function image()
    {
        return $this->morphOne(Image::class, 'imageable');
    }

    public function email()
    {
        return $this->morphOne(Email::class, 'emailable');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function jobplan()
    {
        return $this->belongsTo(JobPlans::class);
    }

    protected function birth(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => (new DateSupport())->convertAmericaForBrazil($value),
            set: fn ($value) => (new DateSupport())->convertBrazilForAmerica($value),
        );
    }

    protected function entrance(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => (new DateSupport())->convertAmericaForBrazil($value),
            set: fn ($value) => (new DateSupport())->convertBrazilForAmerica($value),
        );
    }

    protected function egress(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => (new DateSupport())->convertAmericaForBrazil($value),
            set: fn ($value) => (new DateSupport())->convertBrazilForAmerica($value),
        );
    }

    protected function cpf(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => (new DocumentsSupport())->processReturnDocument((new Cpf()), $value),
            // set: fn ($value) => (new DocumentsSupport())->processDocument((new Cpf()), $value),
        );
    }

    protected function cnpj(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => (new DocumentsSupport())->processReturnDocument((new Cnpj()), $value),
            // set: fn ($value) => (new DocumentsSupport())->processDocument((new Cnpj()), $value),
        );
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
