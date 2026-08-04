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
     * O campo quer dizer coisas diferentes nas duas competências, e isso é
     * proposital. Em hard skill ele é a natureza da atividade (tarefa, curso,
     * plataforma, teste técnico). Em soft skill é o tema da competência
     * (comunicação, empatia...), porque esses níveis são comportamento do dia
     * a dia — "responder a dúvida de um colega com clareza" não é uma
     * atividade agendável, é uma postura observada. Como o formulário filtra a
     * lista pela competência escolhida, ninguém vê as duas juntas.
     *
     * Manter os temas aqui também deixa o relatório do líder agrupar por tema
     * sem precisar de campo novo.
     */
    public const TYPES = [
        // hard skill: natureza da atividade
        'task',
        'course',
        'platform',
        'technical_test',
        // soft skill: tema da competência
        'communication',
        'empathy',
        'emotional_intelligence',
        'collaboration',
        'proactivity',
        'organization',
        'leadership',
        // serve às duas
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
            // Nota de corte do nivel (R9). Sem informar fica nos 70 do banco.
            'cut_score' => 'nullable|integer|min:0|max:100',
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
            'cut_score' => 'nota de corte',
            'position' => 'ordem',
        ];
    }
}
