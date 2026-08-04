<?php

namespace App\Http\Requests\API\Trail;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TrailLevelStoreRequest extends FormRequest
{
    /**
     * Tipos aceitos. Vive aqui e não num enum de banco: tipo novo é ajuste de
     * vocabulário, não de esquema.
     *
     * `technical_test` só faz sentido em hard skill; `mentoring`,
     * `presentation`, `dynamic` e `reading` só em soft. A combinação é
     * oferecida pelo formulário, que filtra a lista pela competência
     * escolhida — aqui a validação é só do valor em si.
     */
    public const TYPES = [
        'task',
        'course',
        'platform',
        'technical_test',
        'mentoring',
        'presentation',
        'dynamic',
        'reading',
        'other',
    ];

    public function authorize()
    {
        return Auth::check();
    }

    public function rules()
    {
        return [
            'description' => 'required|max:255',
            'note' => 'nullable',
            'type' => ['nullable', Rule::in(self::TYPES)],
            'skill' => ['nullable', Rule::in(['soft', 'hard'])],
            'position' => 'nullable|integer|min:0',
        ];
    }

    public function attributes()
    {
        return [
            'description' => 'descrição',
            'note' => 'observações',
            'type' => 'tipo',
            'skill' => 'competência',
            'position' => 'ordem',
        ];
    }
}
