<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SetupTwoFactorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth('sanctum')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // No required fields for setup - just authorization check
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'El campo :attribute es requerido',
        ];
    }
}
