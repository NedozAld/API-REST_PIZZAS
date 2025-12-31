<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notificacion;
use App\Services\NotificacionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NotificacionController extends Controller
{
    public function __construct(private NotificacionService $notificacionService)
    {
    }

    public function index(): JsonResponse
    {
        $notificaciones = Notificacion::orderByDesc('created_at')->paginate(30);

        return response()->json([
            'exito' => true,
            'data' => $notificaciones,
        ], 200);
    }

    public function marcarVista(int $id): JsonResponse
    {
        $ok = $this->notificacionService->marcarVista($id);
        if (!$ok) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Notificacion no encontrada',
            ], 404);
        }

        return response()->json([
            'exito' => true,
            'mensaje' => 'Notificacion marcada como vista',
        ], 200);
    }

    public function stream(Request $request): StreamedResponse
    {
        $response = new StreamedResponse(function () {
            // Permitir que la conexión se mantenga unos segundos enviando heartbeats
            $inicio = time();
            while (time() - $inicio < 25) {
                $items = Notificacion::orderByDesc('created_at')
                    ->take(20)
                    ->get();

                echo 'event: notificaciones' . "\n";
                echo 'data: ' . $items->toJson() . "\n\n";
                ob_flush();
                flush();
                sleep(3);
            }
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');

        return $response;
    }
}
