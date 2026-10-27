<?php

namespace App\Http\Requests\API\Dump;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DumpStoreRequest extends FormRequest
{
    public function authorize()
    {
        return Auth::check();
    }

    public function rules()
    {
        return [
            'corporate_id' => ['required', Rule::exists('corporates', 'id')->whereNull('deleted_at')],
            'destino' => 'required|in:dev,hmg',
        ];
    }

    public function attributes()
    {
        return [
            'corporate_id' => 'organização',
            'destino' => 'destino',
        ];
    }
}
