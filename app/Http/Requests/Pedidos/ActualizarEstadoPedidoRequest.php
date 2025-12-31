<?php

namespace App\Http\Requests\Pedidos;

use Illuminate\Foundation\Http\FormRequest;

class ActualizarEstadoPedidoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'estado' => 'required|string|in:PENDIENTE,TICKET_ENVIADO,CONFIRMADO,EN_PREPARACION,LISTO,EN_ENTREGA,ENTREGADO,CANCELADO',
            'motivo_cancelacion' => 'nullable|string|max:255',
        ];
    }
}
