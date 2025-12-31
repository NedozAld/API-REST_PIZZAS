<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CrearCuponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'codigo' => 'required|string|max:50|unique:cupones,codigo',
            'descripcion' => 'required|string|max:255',
            'tipo_descuento' => 'required|in:porcentaje,fijo',
            'valor_descuento' => 'required|numeric|min:0',
            'descuento_maximo' => 'nullable|numeric|min:0',
            'compra_minima' => 'nullable|numeric|min:0',
            'usos_maximos' => 'nullable|integer|min:1',
            'fecha_inicio' => 'required|date|after_or_equal:today',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'activo' => 'boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'codigo.required' => 'El código del cupón es obligatorio',
            'codigo.unique' => 'Este código de cupón ya existe',
            'descripcion.required' => 'La descripción es obligatoria',
            'tipo_descuento.required' => 'El tipo de descuento es obligatorio',
            'tipo_descuento.in' => 'El tipo de descuento debe ser: porcentaje o fijo',
            'valor_descuento.required' => 'El valor del descuento es obligatorio',
            'valor_descuento.min' => 'El valor del descuento debe ser mayor a 0',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria',
            'fecha_inicio.after_or_equal' => 'La fecha de inicio no puede ser anterior a hoy',
            'fecha_fin.required' => 'La fecha de fin es obligatoria',
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
