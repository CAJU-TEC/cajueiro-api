<?php

namespace App\Http\Requests\API\Trail;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TrailMaterialStoreRequest extends FormRequest
{
    public function authorize()
    {
        return Auth::check();
    }

    public function rules()
    {
        return [
            'description' => 'required|max:255',
            'url' => 'required|url',
            'type' => ['nullable', Rule::in(['course', 'platform', 'technical_test', 'documentation', 'other'])],
            'materialable_id' => 'required_without:id|uuid',
            'materialable_type' => ['required_without:id', Rule::in(['stage', 'level'])],
        ];
    }

    public function attributes()
    {
        return [
            'description' => 'descrição',
            'url' => 'URL',
            'type' => 'tipo',
            'materialable_id' => 'destino do material',
            'materialable_type' => 'tipo de destino',
        ];
    }

    public function messages()
    {
        return [
            'url.url' => 'Informe uma URL válida.',
            'url.required' => 'Informe uma URL válida.',
        ];
    }
}
