<?php

namespace App\Http\Requests\API\Trail;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TrailLevelStoreRequest extends FormRequest
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
            'type' => ['nullable', Rule::in(['task', 'course', 'platform', 'technical_test', 'other'])],
            'position' => 'nullable|integer|min:0',
        ];
    }

    public function attributes()
    {
        return [
            'description' => 'descrição',
            'note' => 'observações',
            'type' => 'tipo',
            'position' => 'ordem',
        ];
    }
}
