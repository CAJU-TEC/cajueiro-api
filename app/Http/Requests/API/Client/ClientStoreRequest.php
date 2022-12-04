<?php

namespace App\Http\Requests\API\Client;

use Illuminate\Foundation\Http\FormRequest;

class ClientStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'description' => 'required|max:255',
            'email' => 'required|email|unique:clients',
            'address' => 'required'
        ];
    }

    public function attributes()
    {
        return [
            'description' => 'empresa',
            'email' => 'endereço eletrônico',
            'address' => 'endereço',
        ];
    }

    public function messages()
    {
        return [];
    }
}
