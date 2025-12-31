<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AplicarCuponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'codigo' => 'required|string|exists:cupones,codigo'
        ];
    }

    public function messages(): array
    {
        return [
            'codigo.required' => 'El código del cupón es obligatorio',
            'codigo.exists' => 'El cupón ingresado no existe'
        ];
    }
}
