<?php

namespace App\Http\Requests\Productos;

use Illuminate\Foundation\Http\FormRequest;

class ActualizarPrecioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'precio_base' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'precio_base.required' => 'El precio es obligatorio',
            'precio_base.numeric' => 'El precio debe ser numérico',
            'precio_base.min' => 'El precio no puede ser negativo',
        ];
    }
}
