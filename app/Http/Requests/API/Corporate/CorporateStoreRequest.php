<?php

namespace App\Http\Requests\API\Corporate;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CorporateStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'address' => 'required',
            'corporate_group_id' => ['nullable', Rule::exists('corporate_groups', 'id')->whereNull('deleted_at')],
        ];
    }

    public function attributes()
    {
        return [
            'first_name' => 'primeiro nome',
            'last_name' => 'último nome',
            'address' => 'endereço',
            'corporate_group_id' => 'grupo',
        ];
    }

    public function messages()
    {
        return [];
    }
}
