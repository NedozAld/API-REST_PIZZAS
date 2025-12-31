<?php

namespace App\Services;

use App\Models\Pedido;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    public function enviarTicket(Pedido $pedido): array
    {
        $sid = env('TWILIO_ACCOUNT_SID');
        $token = env('TWILIO_AUTH_TOKEN');
        $from = env('TWILIO_WHATSAPP_FROM');
        $to = env('TWILIO_WHATSAPP_OWNER');

        if (!$sid || !$token || !$from || !$to) {
            return [
                'exito' => false,
                'mensaje' => 'Faltan variables TWILIO_* en el entorno',
            ];
        }

        $mensaje = sprintf(
            'Nuevo pedido %s Total: $%s. Items: %d. Confirmar en dashboard o responder CONFIRMAR %d.',
            $pedido->numero_pedido,
            $pedido->total,
            $pedido->detalles()->count(),
            $pedido->id
        );

        try {
            $response = Http::withBasicAuth($sid, $token)
                ->asForm()
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                    'From' => 'whatsapp:' . $from,
                    'To' => 'whatsapp:' . $to,
                    'Body' => $mensaje,
                ]);

            if (!$response->successful()) {
                return [
                    'exito' => false,
                    'mensaje' => 'Twilio respondió con error',
                    'detalle' => $response->json(),
                ];
            }

            $data = $response->json();

            $pedido->update([
                'estado' => Pedido::ESTADO_TICKET_ENVIADO,
                'fecha_ticket_enviado' => now(),
                'whatsapp_message_sid' => $data['sid'] ?? null,
            ]);

            return [
                'exito' => true,
                'mensaje' => 'Ticket enviado por WhatsApp',
                'twilio_sid' => $data['sid'] ?? null,
            ];
        } catch (\Throwable $e) {
            Log::error('Error enviando WhatsApp', [
                'pedido_id' => $pedido->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'exito' => false,
                'mensaje' => 'Error enviando mensaje',
            ];
        }
    }

    public function enviarNotificacionCliente(Pedido $pedido): array
    {
        $sid = env('TWILIO_ACCOUNT_SID');
        $token = env('TWILIO_AUTH_TOKEN');
        $from = env('TWILIO_WHATSAPP_FROM');

        $telefonoCliente = $pedido->cliente->telefono ?? null;
        if (!$telefonoCliente) {
            return [
                'exito' => false,
                'mensaje' => 'El cliente no tiene teléfono registrado',
            ];
        }

        if (!$sid || !$token || !$from) {
            return [
                'exito' => false,
                'mensaje' => 'Faltan variables TWILIO_* en el entorno',
            ];
        }

        $mensaje = sprintf(
            'Hola %s, tu pedido %s ha sido confirmado. Total: $%s. Gracias!',
            $pedido->cliente->nombre,
            $pedido->numero_pedido,
            $pedido->total
        );

        try {
            $response = Http::withBasicAuth($sid, $token)
                ->asForm()
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                    'From' => 'whatsapp:' . $from,
                    'To' => 'whatsapp:' . $telefonoCliente,
                    'Body' => $mensaje,
                ]);

            if (!$response->successful()) {
                return [
                    'exito' => false,
                    'mensaje' => 'Twilio respondió con error',
                    'detalle' => $response->json(),
                ];
            }

            return [
                'exito' => true,
                'mensaje' => 'Notificación enviada al cliente',
                'twilio_sid' => $response->json('sid'),
            ];
        } catch (\Throwable $e) {
            Log::error('Error notificando cliente por WhatsApp', [
                'pedido_id' => $pedido->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'exito' => false,
                'mensaje' => 'Error enviando notificación',
            ];
        }
    }

    public function procesarWebhook(array $payload): array
    {
        $body = $payload['Body'] ?? '';
        $from = $payload['From'] ?? '';
        $mensaje = strtoupper(trim($body));

        if (!$mensaje) {
            return [
                'exito' => false,
                'mensaje' => 'Sin contenido',
            ];
        }

        // Buscar ID de pedido en mensaje (CONFIRMAR 123)
        $pedidoId = null;
        if (preg_match('/(CONFIRMAR|OK|LISTO)\s+(PED-\d{8}-\d+|\d+)/', $mensaje, $matches)) {
            $candidato = $matches[2];
            if (str_starts_with($candidato, 'PED-')) {
                $pedido = Pedido::where('numero_pedido', $candidato)->first();
            } else {
                $pedido = Pedido::find($candidato);
            }
            $pedidoId = $pedido?->id;
        }

        if (!$pedidoId) {
            return [
                'exito' => false,
                'mensaje' => 'No se identificó pedido en el mensaje',
            ];
        }

        $pedido = Pedido::find($pedidoId);
        if (!$pedido) {
            return [
                'exito' => false,
                'mensaje' => 'Pedido no encontrado',
            ];
        }

        if ($pedido->estado === Pedido::ESTADO_CONFIRMADO) {
            return [
                'exito' => true,
                'mensaje' => 'Pedido ya estaba confirmado',
                'pedido_id' => $pedido->id,
            ];
        }

        $pedido->update([
            'estado' => Pedido::ESTADO_CONFIRMADO,
            'fecha_confirmacion_whatsapp' => now(),
            'metodo_confirmacion' => 'whatsapp_webhook',
        ]);

        return [
            'exito' => true,
            'mensaje' => 'Pedido confirmado via WhatsApp',
            'pedido_id' => $pedido->id,
            'from' => $from,
        ];
    }
}
