<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateVacationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $vacation = $this->route('vacation');
        
        return Auth::user()->hasRole('Admin') || Auth::id() === $vacation->user_id;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $vacation = $this->route('vacation');

        return [
            'year' => [
                'required',
                'integer',
                Rule::unique('vacations')->where(function ($query) use ($vacation) {
                    return $query->where('user_id', $vacation->user_id);
                })->ignore($vacation->id),
            ],
            'mode' => 'required|string',
            'period_1_start' => 'required|date',
            'period_1_end' => 'required|date|after_or_equal:period_1_start',
            'period_2_start' => 'nullable|date',
            'period_2_end' => 'nullable|date|after_or_equal:period_2_start',
            'period_3_start' => 'nullable|date',
            'period_3_end' => 'nullable|date|after_or_equal:period_3_start',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'year.unique' => 'Este usuário já possui outro cadastro de férias para este ano.',
            'period_1_end.after_or_equal' => 'A data final deve ser igual ou posterior à data inicial.',
        ];
    }
}
