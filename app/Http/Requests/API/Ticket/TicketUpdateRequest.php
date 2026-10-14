<?php

namespace App\Http\Requests\API\Ticket;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Auth;

class TicketUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return Auth::check();
    }

    public function rules()
    {
        return [
            'client_id' => ['sometimes', 'required'],
            'impact_id' => ['sometimes', 'required'],
            'subject' => ['sometimes', 'required', 'string'],
            'message' => ['sometimes', 'required', 'string'],
            'priority' => ['sometimes', Rule::in(['yes', 'no'])],
            'dufy' => ['sometimes', Rule::in(['yes', 'no'])],
            'validated' => ['sometimes', Rule::in(['yes', 'no'])],
            'type' => ['sometimes', Rule::in(['implementation', 'maintenance'])],
            'platform' => ['sometimes', Rule::in(['web', 'mobile', 'notifiq', 'web_mobile'])],
        ];
    }
}
