<?php

namespace App\Http\Requests\API\Schedule;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ScheduleStoreRequest extends FormRequest
{
    public function authorize()
    {
        return Auth::check();
    }

    public function rules()
    {
        return [
            'title' => 'required|max:255',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'lunch_start_time' => 'required|date_format:H:i',
            'lunch_duration_minutes' => 'required|integer|min:1',
            'end_time' => 'required|date_format:H:i',
            'collaborator_ids' => 'required|array|min:1',
            'collaborator_ids.*' => 'exists:collaborators,id',
        ];
    }

    public function attributes()
    {
        return [
            'title' => 'título',
            'date' => 'data',
            'start_time' => 'horário de início',
            'lunch_start_time' => 'horário de início do almoço',
            'lunch_duration_minutes' => 'duração do almoço',
            'end_time' => 'horário de fim',
            'collaborator_ids' => 'colaboradores',
        ];
    }

    public function messages()
    {
        return [
            'collaborator_ids.required' => 'Adicione ao menos um colaborador.',
            'collaborator_ids.min' => 'Adicione ao menos um colaborador.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $start = $this->toMinutes($this->start_time);
            $lunchStart = $this->toMinutes($this->lunch_start_time);
            $end = $this->toMinutes($this->end_time);
            $lunchEnd = $lunchStart !== null ? $lunchStart + (int) $this->lunch_duration_minutes : null;

            if ($start === null || $lunchStart === null || $end === null) {
                return;
            }

            if ($start >= $lunchStart) {
                $validator->errors()->add('lunch_start_time', 'O início do almoço deve ser depois do início da escala.');
            }

            if ($lunchEnd > $end) {
                $validator->errors()->add('lunch_duration_minutes', 'O almoço não cabe entre o início do almoço e o fim da escala.');
            } elseif ($lunchEnd >= $end) {
                $validator->errors()->add('end_time', 'Não sobra tempo de trabalho depois do almoço.');
            }
        });
    }

    private function toMinutes(?string $time): ?int
    {
        if (!$time) {
            return null;
        }

        [$hours, $minutes] = array_map('intval', explode(':', $time));

        return ($hours * 60) + $minutes;
    }
}
