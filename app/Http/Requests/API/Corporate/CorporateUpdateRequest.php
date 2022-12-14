<?php

namespace App\Http\Requests\API\Corporate;

use Illuminate\Foundation\Http\FormRequest;

class CorporateUpdateRequest extends FormRequest
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
}
