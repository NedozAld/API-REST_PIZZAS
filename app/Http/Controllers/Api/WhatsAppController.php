<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Services\WhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WhatsAppController extends Controller
{
    public function __construct(private WhatsAppService $whatsappService)
    {
    }

    public function enviarTicket(int $pedidoId): JsonResponse
    {
        $pedido = Pedido::with('detalles')->find($pedidoId);
        if (!$pedido) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Pedido no encontrado',
            ], 404);
        }

        $resultado = $this->whatsappService->enviarTicket($pedido);

        return response()->json($resultado, $resultado['exito'] ? 200 : 400);
    }

    public function notificarCliente(int $pedidoId): JsonResponse
    {
        $pedido = Pedido::with(['cliente', 'detalles'])->find($pedidoId);
        if (!$pedido) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Pedido no encontrado',
            ], 404);
        }

        $resultado = $this->whatsappService->enviarNotificacionCliente($pedido);

        return response()->json($resultado, $resultado['exito'] ? 200 : 400);
    }

    public function webhook(Request $request): JsonResponse
    {
        $resultado = $this->whatsappService->procesarWebhook($request->all());

        return response()->json($resultado, $resultado['exito'] ? 200 : 400);
    }
}
