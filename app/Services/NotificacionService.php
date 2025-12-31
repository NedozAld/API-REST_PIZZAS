<?php

namespace App\Services;

use App\Models\Notificacion;
use Illuminate\Support\Facades\Log;

class NotificacionService
{
    public function crear(string $tipo, ?int $pedidoId, string $titulo, ?string $descripcion = null): Notificacion
    {
        return Notificacion::create([
            'tipo' => $tipo,
            'pedido_id' => $pedidoId,
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'vista' => false,
        ]);
    }

    public function marcarVista(int $id): bool
    {
        $notificacion = Notificacion::find($id);
        if (!$notificacion) {
            return false;
        }
        $notificacion->vista = true;
        $notificacion->save();
        return true;
    }

    public function ultimasNoVistas(int $limit = 20)
    {
        return Notificacion::where('vista', false)
            ->orderByDesc('created_at')
            ->take($limit)
            ->get();
    }
}
