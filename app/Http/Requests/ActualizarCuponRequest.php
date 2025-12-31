<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActualizarCuponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $cuponId = $this->route('cupon');
        
        return [
            'codigo' => [
                'sometimes',
                'string',
                'max:50',
                Rule::unique('cupones', 'codigo')->ignore($cuponId)
            ],
            'descripcion' => 'sometimes|string|max:255',
            'tipo_descuento' => 'sometimes|in:porcentaje,fijo',
            'valor_descuento' => 'sometimes|numeric|min:0',
            'descuento_maximo' => 'nullable|numeric|min:0',
            'compra_minima' => 'nullable|numeric|min:0',
            'usos_maximos' => 'nullable|integer|min:1',
            'fecha_inicio' => 'sometimes|date',
            'fecha_fin' => 'sometimes|date|after:fecha_inicio',
            'activo' => 'boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'codigo.unique' => 'Este código de cupón ya existe',
            'tipo_descuento.in' => 'El tipo de descuento debe ser: porcentaje o fijo',
            'valor_descuento.min' => 'El valor del descuento debe ser mayor a 0',
            'fecha_fin.after' => 'La fecha de fin debe ser posterior a la fecha de inicio',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('activo')) {
            $this->merge([
                'activo' => filter_var($this->activo, FILTER_VALIDATE_BOOLEAN)
            ]);
        }
    }
}
