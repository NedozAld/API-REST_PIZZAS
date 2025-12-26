<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'nombre' => 'required|string|max:120',
            'email' => 'required|email|unique:usuarios,email|max:255',
            'telefono' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[a-zA-Z\d@$!%*?&]/',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.max' => 'El nombre no puede exceder 120 caracteres',
            'email.required' => 'El correo electrónico es obligatorio',
            'email.email' => 'El correo debe ser válido',
            'email.unique' => 'Este correo ya está registrado',
            'telefono.max' => 'El teléfono no puede exceder 20 caracteres',
            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'La contraseña debe tener mínimo 8 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden',
            'password.regex' => 'La contraseña debe contener mayúsculas, minúsculas, números y caracteres especiales',
        ];
    }
}
