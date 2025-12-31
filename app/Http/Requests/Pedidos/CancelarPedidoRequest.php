<?php

namespace App\Http\Requests\Pedidos;

use Illuminate\Foundation\Http\FormRequest;

class CancelarPedidoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'motivo' => 'required|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'motivo.required' => 'El motivo de cancelación es requerido',
            'motivo.max' => 'El motivo no puede exceder 500 caracteres',
        ];
    }
}
