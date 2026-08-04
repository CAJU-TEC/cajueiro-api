<?php

namespace App\Http\Requests\API\Trail;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class TrailLevelPeriodRequest extends FormRequest
{
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * As duas datas são opcionais porque enviar as duas nulas é como o
     * sublíder limpa um prazo colocado errado. A coerência entre elas (ambas
     * ou nenhuma, fim depois do início) é regra de domínio e fica no service,
     * junto das demais.
     */
    public function rules()
    {
        return [
            'collaborator_id' => 'required|uuid|exists:collaborators,id',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date',
        ];
    }

    public function attributes()
    {
        return [
            'collaborator_id' => 'colaborador',
            'starts_at' => 'data de início',
            'ends_at' => 'data de fim',
        ];
    }

    public function messages()
    {
        return [
            'collaborator_id.required' => 'Selecione o colaborador.',
            'collaborator_id.exists' => 'Colaborador não encontrado.',
        ];
    }
}
