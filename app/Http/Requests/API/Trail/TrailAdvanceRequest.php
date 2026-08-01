<?php

namespace App\Http\Requests\API\Trail;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class TrailAdvanceRequest extends FormRequest
{
    public function authorize()
    {
        return Auth::check();
    }

    public function rules()
    {
        return [
            'collaborator_id' => 'required|uuid|exists:collaborators,id',
            'note' => 'nullable',
        ];
    }

    public function attributes()
    {
        return [
            'collaborator_id' => 'colaborador',
            'note' => 'observações',
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
