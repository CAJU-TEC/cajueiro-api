<?php

namespace App\Http\Requests\API\Trail;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class TrailLevelSubmitRequest extends FormRequest
{
    public function authorize()
    {
        return Auth::check();
    }

    public function rules()
    {
        return [
            'collaborator_id' => 'required|uuid|exists:collaborators,id',
            // Data URI do certificado. Opcional: nível de comportamento não
            // tem certificado para anexar.
            'certificate' => 'nullable|string',
        ];
    }

    public function attributes()
    {
        return [
            'collaborator_id' => 'colaborador',
            'certificate' => 'certificado',
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
