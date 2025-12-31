<?php

namespace App\Http\Requests\Pedidos;

use Illuminate\Foundation\Http\FormRequest;

class AgregarNotasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('sanctum')->check();
    }

    public function rules(): array
    {
        return [
            'notas' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'notas.max' => 'Las notas no pueden superar 1000 caracteres',
        ];
    }
}
