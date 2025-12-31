<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyTwoFactorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth('sanctum')->check() || $this->has('email');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'secret' => 'nullable|string|min:16',  // Secret key puede ser null si es verify sin secret
            'codigo' => 'required|string|regex:/^\d{6}$/',  // Código TOTP siempre 6 dígitos
            'email' => 'nullable|email',  // Para verify-login
        ];
    }

    public function messages(): array
    {
        return [
            'codigo.required' => 'El código 2FA es requerido',
            'codigo.regex' => 'El código debe ser de 6 dígitos',
            'secret.min' => 'El secret key es inválido',
            'email.email' => 'El email debe ser válido',
        ];
    }
}
