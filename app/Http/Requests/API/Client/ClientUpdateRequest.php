<?php

namespace App\Http\Requests\API\Client;

use Illuminate\Foundation\Http\FormRequest;

class ClientUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'email' => 'required|email|unique:clients,email,' . $this->id,
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
}
