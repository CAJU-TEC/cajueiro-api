<?php

namespace App\Models;

use App\Supports\DatesTimes\DateSupport;
use App\Supports\Documents\Cnpj;
use App\Supports\Documents\Cpf;
use App\Supports\Documents\DocumentsSupport;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Collaborator extends Init
{

    protected $guarded = [];

    protected $fillable = [
        'user_id',
        'jobplan_id',
        'team_id',
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

    public function duty()
    {
        return $this->morphOne(Duty::class, 'dutyable');
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

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function trails()
    {
        return $this->belongsToMany(Trail::class, 'trail_collaborator', 'collaborator_id', 'trail_id')
            ->withPivot(['started_at', 'finished_at'])
            ->whereNull('trail_collaborator.deleted_at');
    }

    public function schedules()
    {
        return $this->belongsToMany(Schedule::class, 'schedule_collaborator', 'collaborator_id', 'schedule_id')
            ->withPivot(['position'])
            ->orderByPivot('position');
    }

    public function completedStages()
    {
        return $this->belongsToMany(TrailStage::class, 'trail_stage_collaborator', 'collaborator_id', 'trail_stage_id')
            ->withPivot(['job_plan_id', 'completed_by', 'completed_at', 'certificate_code'])
            ->whereNull('trail_stage_collaborator.deleted_at')
            ->whereNotNull('trail_stage_collaborator.completed_at');
    }

    public function completedLevels()
    {
        return $this->belongsToMany(TrailLevel::class, 'trail_level_collaborator', 'collaborator_id', 'trail_level_id')
            ->withPivot(['completed_by', 'completed_at'])
            ->whereNull('trail_level_collaborator.deleted_at')
            ->whereNotNull('trail_level_collaborator.completed_at');
    }

    /**
     * Um badge por etapa concluída, herdado do plano-alvo daquela etapa.
     *
     * Fora do $appends de propósito: serializar colaborador em lista (protocolos,
     * por exemplo) dispararia uma query por linha. O front carrega todos de uma vez
     * pelo endpoint trails/badges (TrailBadgesController).
     */
    public function getBadgesAttribute()
    {
        return DB::table('trail_stage_collaborator')
            ->join('job_plans', 'job_plans.id', '=', 'trail_stage_collaborator.job_plan_id')
            ->join('trail_stages', 'trail_stages.id', '=', 'trail_stage_collaborator.trail_stage_id')
            ->where('trail_stage_collaborator.collaborator_id', $this->id)
            ->whereNull('trail_stage_collaborator.deleted_at')
            ->whereNotNull('trail_stage_collaborator.completed_at')
            ->whereNotNull('job_plans.badge_icon')
            ->orderBy('trail_stage_collaborator.completed_at')
            ->orderBy('trail_stages.position')
            ->get([
                'job_plans.id as job_plan_id',
                'job_plans.description as job_plan',
                'job_plans.badge_icon',
                'job_plans.badge_color',
                'trail_stages.id as trail_stage_id',
                'trail_stages.description as trail_stage',
                'trail_stage_collaborator.completed_at',
                'trail_stage_collaborator.certificate_code',
            ]);
    }

    protected function birth(): Attribute
    {
        return Attribute::make(
            get: fn($value) => (new DateSupport())->convertAmericaForBrazil($value),
            set: fn($value) => (new DateSupport())->convertBrazilForAmerica($value),
        );
    }

    protected function entrance(): Attribute
    {
        return Attribute::make(
            get: fn($value) => (new DateSupport())->convertAmericaForBrazil($value),
            set: fn($value) => (new DateSupport())->convertBrazilForAmerica($value),
        );
    }

    protected function egress(): Attribute
    {
        return Attribute::make(
            get: fn($value) => (new DateSupport())->convertAmericaForBrazil($value),
            set: fn($value) => (new DateSupport())->convertBrazilForAmerica($value),
        );
    }

    protected function cpf(): Attribute
    {
        return Attribute::make(
            get: fn($value) => (new DocumentsSupport())->processReturnDocument((new Cpf()), $value),
            // set: fn ($value) => (new DocumentsSupport())->processDocument((new Cpf()), $value),
        );
    }

    protected function cnpj(): Attribute
    {
        return Attribute::make(
            get: fn($value) => (new DocumentsSupport())->processReturnDocument((new Cnpj()), $value),
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
