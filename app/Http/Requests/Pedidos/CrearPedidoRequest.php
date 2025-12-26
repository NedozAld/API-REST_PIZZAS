<?php

namespace App\Http\Requests\Pedidos;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Producto;

class CrearPedidoRequest extends FormRequest
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
            'items.*.notas' => 'nullable|string|max:500',
            'notas' => 'nullable|string|max:1000',
            'costo_entrega' => 'nullable|numeric|min:0',
            'monto_descuento' => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Debe incluir al menos un producto en el pedido.',
            'items.array' => 'Los items deben ser un arreglo.',
            'items.min' => 'Debe incluir al menos un producto en el pedido.',
            'items.*.producto_id.required' => 'El ID del producto es obligatorio.',
            'items.*.producto_id.exists' => 'El producto seleccionado no existe.',
            'items.*.cantidad.required' => 'La cantidad es obligatoria.',
            'items.*.cantidad.integer' => 'La cantidad debe ser un número entero.',
            'items.*.cantidad.min' => 'La cantidad mínima es 1.',
            'items.*.notas.max' => 'Las notas del item no pueden exceder 500 caracteres.',
            'notas.max' => 'Las notas del pedido no pueden exceder 1000 caracteres.',
            'costo_entrega.numeric' => 'El costo de entrega debe ser un número.',
            'costo_entrega.min' => 'El costo de entrega no puede ser negativo.',
            'monto_descuento.numeric' => 'El monto de descuento debe ser un número.',
            'monto_descuento.min' => 'El monto de descuento no puede ser negativo.',
        ];
    }

    /**
     * Validación adicional: verificar stock disponible
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $items = $this->input('items', []);

            foreach ($items as $index => $item) {
                $producto = Producto::find($item['producto_id'] ?? null);

                if ($producto) {
                    // Verificar que el producto esté disponible
                    if (!$producto->disponible) {
                        $validator->errors()->add(
                            "items.{$index}.producto_id",
                            "El producto '{$producto->nombre}' no está disponible actualmente."
                        );
                    }

                    // Verificar que el producto esté activo
                    if (!$producto->activo) {
                        $validator->errors()->add(
                            "items.{$index}.producto_id",
                            "El producto '{$producto->nombre}' no está activo."
                        );
                    }

                    // Verificar stock disponible
                    $cantidadSolicitada = $item['cantidad'] ?? 0;
                    if ($producto->stock_disponible < $cantidadSolicitada) {
                        $validator->errors()->add(
                            "items.{$index}.cantidad",
                            "Stock insuficiente para '{$producto->nombre}'. Disponible: {$producto->stock_disponible}, solicitado: {$cantidadSolicitada}."
                        );
                    }
                }
            }
        });
    }
}
