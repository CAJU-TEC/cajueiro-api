<?php

namespace App\Http\Requests\API\Corporate;

use Illuminate\Foundation\Http\FormRequest;

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
            'address' => 'required'
        ];
    }

    public function attributes()
    {
        return [
            'first_name' => 'primeiro nome',
            'last_name' => 'último nome',
            'address' => 'endereço',
        ];
    }

    public function messages()
    {
        return [];
    }
}
