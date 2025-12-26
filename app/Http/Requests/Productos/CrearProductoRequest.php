<?php

namespace App\Http\Requests\Productos;

use Illuminate\Foundation\Http\FormRequest;

class CrearProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Permitir; en producción validar permisos/roles
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:150|unique:productos,nombre',
            'descripcion' => 'nullable|string',
            'precio_base' => 'required|numeric|min:0',
            'categoria_id' => 'required|exists:categorias,id',
            'stock_disponible' => 'nullable|integer|min:0',
            'stock_minimo' => 'nullable|integer|min:0',
            'disponible' => 'nullable|boolean',
            'imagen_url' => 'nullable|url|max:500',
            'costo' => 'nullable|numeric|min:0',
            'activo' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.unique' => 'Ya existe un producto con ese nombre',
            'precio_base.required' => 'El precio es obligatorio',
            'precio_base.numeric' => 'El precio debe ser numérico',
            'categoria_id.required' => 'La categoría es obligatoria',
            'categoria_id.exists' => 'La categoría no existe',
        ];
    }
}
