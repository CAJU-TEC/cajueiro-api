<?php

namespace App\Http\Requests\API\Trail;

use App\Models\JobPlans;
use App\Models\Trail;
use App\Models\TrailLevel;
use App\Models\TrailStage;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class TrailStageStoreRequest extends FormRequest
{
    public function authorize()
    {
        return Auth::check();
    }

    public function rules()
    {
        return [
            'description' => 'required|max:255',
            'note' => 'nullable',
            'job_plan_id' => 'nullable|uuid|exists:job_plans,id',
            'position' => 'nullable|integer|min:0',
            'required_count' => 'nullable|integer|min:1',
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function (Validator $validator) {
            $stage = $this->resolveStage();
            $trail = $this->resolveTrail($stage);

            // R4 - o plano-alvo precisa ser do mesmo time da trilha.
            if ($this->filled('job_plan_id') && $trail?->team_id) {
                $jobPlan = JobPlans::find($this->input('job_plan_id'));

                if ($jobPlan && $jobPlan->team_id !== $trail->team_id) {
                    $validator->errors()->add('job_plan_id', 'O plano-alvo deve pertencer ao time da trilha.');
                }
            }

            // O mínimo de níveis não pode exceder os níveis já cadastrados.
            if ($stage && $this->filled('required_count')) {
                $levelsCount = TrailLevel::where('trail_stage_id', $stage->id)->count();

                if ($levelsCount > 0 && (int) $this->input('required_count') > $levelsCount) {
                    $validator->errors()->add(
                        'required_count',
                        'O mínimo de níveis não pode ser maior que a quantidade de níveis da etapa.'
                    );
                }
            }
        });
    }

    public function attributes()
    {
        return [
            'description' => 'descrição',
            'note' => 'observações',
            'job_plan_id' => 'plano-alvo',
            'position' => 'ordem',
            'required_count' => 'mínimo de níveis',
        ];
    }

    private function resolveStage(): ?TrailStage
    {
        $stage = $this->route('stage');

        if (!$stage) {
            return null;
        }

        return $stage instanceof TrailStage ? $stage : TrailStage::find($stage);
    }

    private function resolveTrail(?TrailStage $stage): ?Trail
    {
        $trail = $this->route('trail');

        if ($trail) {
            return $trail instanceof Trail ? $trail : Trail::find($trail);
        }

        return $stage ? Trail::find($stage->trail_id) : null;
    }
}
