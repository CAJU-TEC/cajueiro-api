<?php

namespace App\Http\Requests\API\Team;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class TeamStoreRequest extends FormRequest
{
    public function authorize()
    {
        return Auth::check();
    }

    public function rules()
    {
        return [
            'name' => 'required|max:255',
            'description' => 'nullable|max:255',
            'color' => 'nullable|max:30',
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'nome',
            'description' => 'descrição',
            'color' => 'cor',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Informe o nome do time.',
        ];
    }
}
