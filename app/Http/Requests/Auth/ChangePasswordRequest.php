<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'password_actual' => 'required|string|min:6',
            'password_nueva' => 'required|string|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[a-zA-Z\d@$!%*?&]/',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'password_actual.required' => 'La contraseña actual es obligatoria',
            'password_actual.min' => 'La contraseña actual debe tener mínimo 6 caracteres',
            'password_nueva.required' => 'La nueva contraseña es obligatoria',
            'password_nueva.min' => 'La nueva contraseña debe tener mínimo 8 caracteres',
            'password_nueva.confirmed' => 'Las contraseñas no coinciden',
            'password_nueva.regex' => 'La contraseña debe contener mayúsculas, minúsculas, números y caracteres especiales',
        ];
    }
}
