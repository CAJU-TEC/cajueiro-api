<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;

class TrailStage extends Init
{
    protected $guarded = ['id'];

    protected $fillable = [
        'trail_id',
        'job_plan_id',
        'description',
        'note',
        'position',
        'required_count',
    ];

    protected $casts = [
        'position' => 'integer',
        'required_count' => 'integer',
    ];

    public function trail(): BelongsTo
    {
        return $this->belongsTo(Trail::class);
    }

    public function jobPlan(): BelongsTo
    {
        return $this->belongsTo(JobPlans::class, 'job_plan_id');
    }

    public function levels(): HasMany
    {
        return $this->hasMany(TrailLevel::class)->orderBy('position');
    }

    public function materials(): MorphMany
    {
        return $this->morphMany(TrailMaterial::class, 'materialable');
    }

    public function collaborators(): BelongsToMany
    {
        return $this->belongsToMany(Collaborator::class, 'trail_stage_collaborator', 'trail_stage_id', 'collaborator_id')
            ->withPivot(['job_plan_id', 'completed_by', 'completed_at', 'certificate_code', 'note'])
            ->whereNull('trail_stage_collaborator.deleted_at');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function (Model $model) {
            DB::table('trail_stage_collaborator')
                ->where('trail_stage_id', $model->id)
                ->whereNull('deleted_at')
                ->update(['deleted_at' => now()]);

            $model->materials()->get()->each->delete();
            $model->levels()->get()->each->delete();
        });
    }
}
