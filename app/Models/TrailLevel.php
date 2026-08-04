<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;

class TrailLevel extends Init
{
    protected $guarded = ['id'];

    protected $fillable = [
        'trail_stage_id',
        'description',
        'note',
        'type',
        'skill',
        'position',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    public function stage(): BelongsTo
    {
        return $this->belongsTo(TrailStage::class, 'trail_stage_id');
    }

    public function materials(): MorphMany
    {
        return $this->morphMany(TrailMaterial::class, 'materialable');
    }

    public function collaborators(): BelongsToMany
    {
        return $this->belongsToMany(Collaborator::class, 'trail_level_collaborator', 'trail_level_id', 'collaborator_id')
            ->withPivot(['completed_by', 'completed_at', 'note'])
            ->whereNull('trail_level_collaborator.deleted_at');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function (Model $model) {
            DB::table('trail_level_collaborator')
                ->where('trail_level_id', $model->id)
                ->whereNull('deleted_at')
                ->update(['deleted_at' => now()]);

            $model->materials()->get()->each->delete();
        });
    }
}
