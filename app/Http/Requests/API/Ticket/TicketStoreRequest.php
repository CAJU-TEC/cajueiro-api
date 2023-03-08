<?php

namespace App\Http\Requests\API\Ticket;

use Illuminate\Foundation\Http\FormRequest;
use Auth;

class TicketStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
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
            'client_id' => ['required'],
            'impact_id' => ['required'],
            'subject' => ['required'],
            'message' => ['required'],
        ];
    }
}
