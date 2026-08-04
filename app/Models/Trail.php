<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Trail extends Init
{
    protected $guarded = ['id'];

    protected $fillable = [
        'team_id',
        'description',
        'note',
        'color',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function stages(): HasMany
    {
        return $this->hasMany(TrailStage::class)->orderBy('position');
    }

    public function collaborators(): BelongsToMany
    {
        return $this->belongsToMany(Collaborator::class, 'trail_collaborator', 'trail_id', 'collaborator_id')
            ->withPivot(['started_at', 'finished_at'])
            ->whereNull('trail_collaborator.deleted_at');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function (Model $model) {
            DB::table('trail_collaborator')
                ->where('trail_id', $model->id)
                ->whereNull('deleted_at')
                ->update(['deleted_at' => now()]);

            $model->stages()->get()->each->delete();
        });
    }
}
