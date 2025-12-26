<?php

namespace App\Http\Requests\Productos;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActualizarProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productoId = $this->route('id');

        return [
            'nombre' => [
                'sometimes',
                'string',
                'max:150',
                Rule::unique('productos', 'nombre')->ignore($productoId)
            ],
            'descripcion' => 'nullable|string',
            'precio_base' => 'sometimes|numeric|min:0',
            'categoria_id' => 'sometimes|exists:categorias,id',
            'stock_disponible' => 'sometimes|integer|min:0',
            'stock_minimo' => 'sometimes|integer|min:0',
            'disponible' => 'sometimes|boolean',
            'imagen_url' => 'nullable|url|max:500',
            'costo' => 'nullable|numeric|min:0',
            'activo' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.unique' => 'Ya existe un producto con ese nombre',
            'nombre.max' => 'El nombre no puede exceder 150 caracteres',
            'precio_base.numeric' => 'El precio debe ser numérico',
            'precio_base.min' => 'El precio no puede ser negativo',
            'categoria_id.exists' => 'La categoría no existe',
            'stock_disponible.integer' => 'El stock debe ser un número entero',
            'stock_disponible.min' => 'El stock no puede ser negativo',
            'imagen_url.url' => 'La URL de la imagen no es válida',
        ];
    }
}
