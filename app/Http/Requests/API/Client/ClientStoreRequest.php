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
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'email' => 'required|email|unique:clients',
            'address' => 'required'
        ];
    }

    public function attributes()
    {
        return [
            'first_name' => 'primeiro nome',
            'last_name' => 'último nome',
            'email' => 'endereço eletrônico',
            'address' => 'endereço',
        ];
    }

    public function messages()
    {
        return [];
    }
}
