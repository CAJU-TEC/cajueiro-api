<?php

namespace App\Http\Requests\API\Collaborator;

use Illuminate\Foundation\Http\FormRequest;

class CollaboratorUpdateRequest extends FormRequest
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
            //
            'first_name' => 'required',
            'last_name' => 'required',
            'formation' => 'required',
            'birth' => 'required',
            'entrance' => 'required',
            'egress' => '',
            'cpf' => '',
            'cnpj' => '',
            'email' => 'required',
        ];
    }

    public function attributes()
    {
        return [
            'first_name' => 'primeiro nome',
            'last_name' => 'último nome',
            'formation' => 'formação acadêmica/cursos',
            'birth' => 'data nascimento',
            'entrance' => 'ingresso',
            'egress' => 'egresso',
            'email' => 'e-mail',
        ];
    }

    public function messages()
    {
        return [];
    }
}
