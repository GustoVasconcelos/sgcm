<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateScheduleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Autorização via middleware (can:ver_pgm_fds)
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'program_id' => 'required|exists:programs,id',
            'date' => 'required|date',
            'start_time' => 'required',
            'duration' => 'required|integer|min:1',
            'custom_info' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'program_id.required' => 'Selecione um programa.',
            'program_id.exists' => 'Programa inválido.',
            'date.required' => 'A data é obrigatória.',
            'start_time.required' => 'O horário é obrigatório.',
            'duration.required' => 'A duração é obrigatória.',
            'duration.min' => 'A duração deve ser de pelo menos 1 minuto.',
        ];
    }
}
