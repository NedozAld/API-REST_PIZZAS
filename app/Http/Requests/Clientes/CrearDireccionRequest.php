<?php

namespace App\Http\Requests\Clientes;

use Illuminate\Foundation\Http\FormRequest;

class CrearDireccionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('sanctum')->check();
    }

    public function rules(): array
    {
        return [
            'nombre_direccion' => 'required|string|max:100',
            'calle' => 'required|string|max:255',
            'numero' => 'required|string|max:20',
            'apartamento' => 'nullable|string|max:20',
            'ciudad' => 'required|string|max:100',
            'codigo_postal' => 'required|string|max:20',
            'provincia' => 'nullable|string|max:100',
            'referencia' => 'nullable|string|max:500',
            'favorita' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre_direccion.required' => 'El nombre de la dirección es requerido',
            'nombre_direccion.max' => 'El nombre no puede superar 100 caracteres',
            'calle.required' => 'La calle es requerida',
            'calle.max' => 'La calle no puede superar 255 caracteres',
            'numero.required' => 'El número es requerido',
            'numero.max' => 'El número no puede superar 20 caracteres',
            'ciudad.required' => 'La ciudad es requerida',
            'ciudad.max' => 'La ciudad no puede superar 100 caracteres',
            'codigo_postal.required' => 'El código postal es requerido',
            'codigo_postal.max' => 'El código postal no puede superar 20 caracteres',
            'provincia.max' => 'La provincia no puede superar 100 caracteres',
            'referencia.max' => 'La referencia no puede superar 500 caracteres',
        ];
    }
}
