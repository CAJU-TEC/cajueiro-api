<?php

namespace App\Http\Requests\API\Trail;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class TrailStoreRequest extends FormRequest
{
    public function authorize()
    {
        return Auth::check();
    }

    public function rules()
    {
        return [
            'team_id' => 'required|uuid|exists:teams,id',
            'description' => 'required|max:255',
            'note' => 'nullable',
            'color' => 'nullable|max:30',
            'active' => 'nullable|boolean',
        ];
    }

    public function attributes()
    {
        return [
            'team_id' => 'time',
            'description' => 'descrição',
            'note' => 'observações',
            'color' => 'cor',
        ];
    }

    public function messages()
    {
        return [
            'team_id.required' => 'Selecione o time da trilha.',
            'team_id.exists' => 'Selecione o time da trilha.',
            'description.required' => 'Informe a descrição da trilha.',
        ];
    }
}
