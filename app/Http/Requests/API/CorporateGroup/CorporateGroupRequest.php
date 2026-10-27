<?php

namespace App\Http\Requests\API\CorporateGroup;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CorporateGroupRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'nome' => [
                'required', 'string', 'max:125',
                Rule::unique('corporate_groups', 'nome')->ignore($this->route('corporateGroup'))->whereNull('deleted_at'),
            ],
        ];
    }

    public function attributes()
    {
        return ['nome' => 'nome do grupo'];
    }

    public function messages()
    {
        return ['nome.unique' => 'Já existe um grupo com esse nome.'];
    }
}
