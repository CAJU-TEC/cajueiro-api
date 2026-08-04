<?php

namespace App\Http\Requests\API\Trail;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * Concluir o nível é o ato de avaliar (R9), então nota e resposta são
 * obrigatórias. Não dá para usar o `TrailAdvanceRequest`: ele também serve ao
 * desfazer e ao avanço da etapa, onde as duas não fazem sentido.
 */
class TrailLevelCompleteRequest extends FormRequest
{
    public function authorize()
    {
        return Auth::check();
    }

    public function rules()
    {
        return [
            'collaborator_id' => 'required|uuid|exists:collaborators,id',
            'score' => 'required|integer|min:0|max:100',
            'note' => 'required|string',
        ];
    }

    public function attributes()
    {
        return [
            'collaborator_id' => 'colaborador',
            'note' => 'resposta',
            'score' => 'nota',
        ];
    }

    public function messages()
    {
        return [
            'collaborator_id.required' => 'Selecione o colaborador.',
            'collaborator_id.exists' => 'Colaborador não encontrado.',
            'score.required' => 'Informe a nota do nível.',
            'note.required' => 'Escreva a resposta ao colaborador.',
        ];
    }
}
