<?php

namespace App\Http\Requests\Pedidos;

use Illuminate\Foundation\Http\FormRequest;

class EditarPedidoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => 'required|array|min:1',
            'items.*.producto_id' => 'required|integer|exists:productos,id',
            'items.*.cantidad' => 'required|integer|min:1',
            'items.*.notas' => 'nullable|string|max:255',
            'costo_entrega' => 'nullable|numeric|min:0',
            'monto_descuento' => 'nullable|numeric|min:0',
            'notas' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Debe incluir al menos un item',
            'items.*.producto_id.required' => 'El ID del producto es requerido',
            'items.*.producto_id.exists' => 'El producto no existe',
            'items.*.cantidad.required' => 'La cantidad es requerida',
            'items.*.cantidad.min' => 'La cantidad debe ser mínimo 1',
        ];
    }
}
