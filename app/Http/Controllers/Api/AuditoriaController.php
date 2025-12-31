<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Auditoria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AuditoriaController extends Controller
{
    /**
     * US-064: Auditoría de Acciones
     * GET /api/auditoria
     * Parámetros query:
     * - usuario_id: filtrar por usuario
     * - fecha_desde: filtrar desde fecha
     * - fecha_hasta: filtrar hasta fecha
     * - tipo_accion: CREAR, ACTUALIZAR, ELIMINAR
     * - tabla_afectada: nombre de tabla
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Auditoria::with('usuario');

            // Filtro por usuario
            if ($request->has('usuario_id') && $request->usuario_id) {
                $query->where('usuario_id', $request->usuario_id);
            }

            // Filtro por tipo de acción
            if ($request->has('tipo_accion') && $request->tipo_accion) {
                $query->where('tipo_accion', strtoupper($request->tipo_accion));
            }

            // Filtro por tabla afectada
            if ($request->has('tabla_afectada') && $request->tabla_afectada) {
                $query->where('tabla_afectada', $request->tabla_afectada);
            }

            // Filtro por fecha desde
            if ($request->has('fecha_desde') && $request->fecha_desde) {
                $query->whereDate('fecha_accion', '>=', $request->fecha_desde);
            }

            // Filtro por fecha hasta
            if ($request->has('fecha_hasta') && $request->fecha_hasta) {
                $query->whereDate('fecha_accion', '<=', $request->fecha_hasta);
            }

            // Ordenar por fecha descendente (más reciente primero)
            $auditoria = $query->orderBy('fecha_accion', 'desc')
                ->paginate(20);

            return response()->json([
                'exito' => true,
                'total' => $auditoria->total(),
                'por_pagina' => $auditoria->per_page(),
                'pagina_actual' => $auditoria->current_page(),
                'total_paginas' => $auditoria->last_page(),
                'filtros' => [
                    'usuario_id' => $request->usuario_id ?? null,
                    'tipo_accion' => $request->tipo_accion ?? null,
                    'tabla_afectada' => $request->tabla_afectada ?? null,
                    'fecha_desde' => $request->fecha_desde ?? null,
                    'fecha_hasta' => $request->fecha_hasta ?? null,
                ],
                'datos' => $auditoria->items(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'exito' => false,
                'error' => 'Error al obtener auditoría: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de auditoría
     * GET /api/auditoria/estadisticas
     */
    public function estadisticas(): JsonResponse
    {
        try {
            $totalAcciones = Auditoria::count();
            $accionesPorTipo = Auditoria::selectRaw('tipo_accion, COUNT(*) as total')
                ->groupBy('tipo_accion')
                ->get();

            $usuariosMasActivos = Auditoria::selectRaw('usuario_id, nombre_usuario, COUNT(*) as total_acciones')
                ->groupBy('usuario_id', 'nombre_usuario')
                ->orderByDesc('total_acciones')
                ->limit(10)
                ->get();

            $tablasAfectadas = Auditoria::selectRaw('tabla_afectada, COUNT(*) as total')
                ->groupBy('tabla_afectada')
                ->get();

            $accionesUlimas24h = Auditoria::where('fecha_accion', '>=', now()->subHours(24))
                ->count();

            return response()->json([
                'exito' => true,
                'total_acciones' => $totalAcciones,
                'acciones_ultimas_24h' => $accionesUlimas24h,
                'acciones_por_tipo' => $accionesPorTipo,
                'usuarios_mas_activos' => $usuariosMasActivos,
                'tablas_afectadas' => $tablasAfectadas,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'exito' => false,
                'error' => 'Error al obtener estadísticas: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener auditoria de un usuario específico
     * GET /api/auditoria/usuario/{usuario_id}
     */
    public function usuarioAuditoria(int $usuario_id): JsonResponse
    {
        try {
            $auditoria = Auditoria::where('usuario_id', $usuario_id)
                ->orderBy('fecha_accion', 'desc')
                ->paginate(20);

            return response()->json([
                'exito' => true,
                'usuario_id' => $usuario_id,
                'total' => $auditoria->total(),
                'datos' => $auditoria->items(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'exito' => false,
                'error' => 'Error al obtener auditoría del usuario: ' . $e->getMessage(),
            ], 500);
        }
    }
}
