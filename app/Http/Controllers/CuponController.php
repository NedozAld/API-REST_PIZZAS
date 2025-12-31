<?php

namespace App\Http\Controllers;

use App\Models\Cupon;
use App\Http\Requests\CrearCuponRequest;
use App\Http\Requests\ActualizarCuponRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CuponController extends Controller
{
    /**
     * US-080: Listar todos los cupones
     * GET /api/cupones
     */
    public function index(Request $request): JsonResponse
    {
        $query = Cupon::query();

        // Filtros opcionales
        if ($request->has('activo')) {
            $query->where('activo', filter_var($request->activo, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->has('vigentes')) {
            $query->vigentes();
        }

        if ($request->has('disponibles')) {
            $query->disponibles();
        }

        $cupones = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $cupones
        ], 200);
    }

    /**
     * US-080: Crear un nuevo cupón
     * POST /api/cupones
     */
    public function store(CrearCuponRequest $request): JsonResponse
    {
        $datos = $request->validated();
        
        // Valores por defecto
        $datos['usos_actuales'] = 0;
        $datos['activo'] = $datos['activo'] ?? true;

        $cupon = Cupon::create($datos);

        return response()->json([
            'success' => true,
            'message' => 'Cupón creado exitosamente',
            'data' => $cupon
        ], 201);
    }

    /**
     * Mostrar un cupón específico
     * GET /api/cupones/{id}
     */
    public function show(string $id): JsonResponse
    {
        $cupon = Cupon::find($id);

        if (!$cupon) {
            return response()->json([
                'success' => false,
                'message' => 'Cupón no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $cupon
        ], 200);
    }

    /**
     * Actualizar un cupón
     * PUT/PATCH /api/cupones/{id}
     */
    public function update(ActualizarCuponRequest $request, string $id): JsonResponse
    {
        $cupon = Cupon::find($id);

        if (!$cupon) {
            return response()->json([
                'success' => false,
                'message' => 'Cupón no encontrado'
            ], 404);
        }

        $cupon->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Cupón actualizado exitosamente',
            'data' => $cupon
        ], 200);
    }

    /**
     * Eliminar un cupón
     * DELETE /api/cupones/{id}
     */
    public function destroy(string $id): JsonResponse
    {
        $cupon = Cupon::find($id);

        if (!$cupon) {
            return response()->json([
                'success' => false,
                'message' => 'Cupón no encontrado'
            ], 404);
        }

        $cupon->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cupón eliminado exitosamente'
        ], 200);
    }

    /**
     * Validar un cupón
     * POST /api/cupones/validar
     */
    public function validar(Request $request): JsonResponse
    {
        $request->validate([
            'codigo' => 'required|string',
            'monto' => 'required|numeric|min:0',
            'cliente_id' => 'nullable|exists:clientes,id'
        ]);

        $cupon = Cupon::where('codigo', $request->codigo)->first();

        if (!$cupon) {
            return response()->json([
                'success' => false,
                'message' => 'Cupón no encontrado'
            ], 404);
        }

        // Verificar si el cupón es válido
        $validacion = $cupon->esValido($request->monto);

        if (!$validacion['valido']) {
            return response()->json([
                'success' => false,
                'message' => $validacion['mensaje']
            ], 400);
        }

        // Verificar si el cliente ya usó el cupón
        if ($request->cliente_id && $cupon->fueUsadoPor($request->cliente_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Ya has usado este cupón anteriormente'
            ], 400);
        }

        // Calcular descuento
        $descuento = $cupon->calcularDescuento($request->monto);
        $montoFinal = $request->monto - $descuento;

        return response()->json([
            'success' => true,
            'message' => 'Cupón válido',
            'data' => [
                'cupon' => $cupon,
                'monto_original' => $request->monto,
                'descuento' => $descuento,
                'monto_final' => $montoFinal,
                'informacion' => $cupon->informacion_formateada
            ]
        ], 200);
    }

    /**
     * Obtener estadísticas de un cupón
     * GET /api/cupones/{id}/estadisticas
     */
    public function estadisticas(string $id): JsonResponse
    {
        $cupon = Cupon::with('clientes')->find($id);

        if (!$cupon) {
            return response()->json([
                'success' => false,
                'message' => 'Cupón no encontrado'
            ], 404);
        }

        $estadisticas = [
            'codigo' => $cupon->codigo,
            'descripcion' => $cupon->descripcion,
            'usos_totales' => $cupon->usos_actuales,
            'usos_maximos' => $cupon->usos_maximos,
            'usos_disponibles' => $cupon->usos_maximos ? ($cupon->usos_maximos - $cupon->usos_actuales) : 'Ilimitado',
            'porcentaje_uso' => $cupon->usos_maximos ? round(($cupon->usos_actuales / $cupon->usos_maximos) * 100, 2) : 0,
            'clientes_unicos' => $cupon->clientes()->distinct('cliente_id')->count(),
            'fecha_inicio' => $cupon->fecha_inicio->format('Y-m-d'),
            'fecha_fin' => $cupon->fecha_fin->format('Y-m-d'),
            'activo' => $cupon->activo,
            'vigente' => $cupon->esValido()['valido']
        ];

        return response()->json([
            'success' => true,
            'data' => $estadisticas
        ], 200);
    }
}
