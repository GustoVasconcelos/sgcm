<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Autorização já feita via middleware de rota (role:Admin)
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'Este e-mail já está em uso.',
            'password.confirmed' => 'As senhas não conferem.',
            'password.min' => 'A senha deve ter no mínimo 8 caracteres.',
            'roles.required' => 'Selecione pelo menos um grupo.',
            'roles.min' => 'Selecione pelo menos um grupo.',
        ];
    }
}
