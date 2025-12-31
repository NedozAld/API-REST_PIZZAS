<?php

namespace App\Http\Requests\Pedidos;

use Illuminate\Foundation\Http\FormRequest;

class MarcarEntregadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('sanctum')->check();
    }

    public function rules(): array
    {
        return [
            'fecha_entrega' => 'nullable|date|after_or_equal:today',
            'comentario' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'fecha_entrega.date' => 'La fecha debe ser válida',
            'fecha_entrega.after_or_equal' => 'La fecha debe ser hoy o posterior',
            'comentario.max' => 'El comentario no puede superar 500 caracteres',
        ];
    }
}
