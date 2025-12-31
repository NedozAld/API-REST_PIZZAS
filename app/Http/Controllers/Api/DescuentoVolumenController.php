<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DescuentoVolumen;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DescuentoVolumenController extends Controller
{
    /**
     * US-083: Listar descuentos por volumen
     * GET /api/descuentos-volumen
     */
    public function index(Request $request): JsonResponse
    {
        $query = DescuentoVolumen::query();

        // Filtro por estado
        if ($request->has('activo')) {
            $activo = filter_var($request->activo, FILTER_VALIDATE_BOOLEAN);
            $query->where('activo', $activo);
        }

        $descuentos = $query->orderBy('monto_minimo')->get();

        return response()->json([
            'exito' => true,
            'datos' => $descuentos
        ], 200);
    }

    /**
     * Crear descuento por volumen
     * POST /api/descuentos-volumen
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'monto_minimo' => 'required|numeric|min:0',
            'monto_maximo' => 'nullable|numeric|min:0|gt:monto_minimo',
            'porcentaje_descuento' => 'required|numeric|min:0|max:100',
            'activo' => 'boolean',
            'descripcion' => 'nullable|string'
        ]);

        $validated['activo'] = $validated['activo'] ?? true;

        $descuento = DescuentoVolumen::create($validated);

        return response()->json([
            'exito' => true,
            'mensaje' => 'Descuento por volumen creado exitosamente',
            'datos' => $descuento
        ], 201);
    }

    /**
     * Ver detalle de descuento
     * GET /api/descuentos-volumen/{id}
     */
    public function show(int $id): JsonResponse
    {
        $descuento = DescuentoVolumen::find($id);

        if (!$descuento) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Descuento no encontrado'
            ], 404);
        }

        return response()->json([
            'exito' => true,
            'datos' => $descuento
        ], 200);
    }

    /**
     * Actualizar descuento
     * PUT /api/descuentos-volumen/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $descuento = DescuentoVolumen::find($id);

        if (!$descuento) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Descuento no encontrado'
            ], 404);
        }

        $validated = $request->validate([
            'monto_minimo' => 'sometimes|numeric|min:0',
            'monto_maximo' => 'nullable|numeric|min:0|gt:monto_minimo',
            'porcentaje_descuento' => 'sometimes|numeric|min:0|max:100',
            'activo' => 'boolean',
            'descripcion' => 'nullable|string'
        ]);

        $descuento->update($validated);

        return response()->json([
            'exito' => true,
            'mensaje' => 'Descuento actualizado exitosamente',
            'datos' => $descuento
        ], 200);
    }

    /**
     * Eliminar descuento
     * DELETE /api/descuentos-volumen/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $descuento = DescuentoVolumen::find($id);

        if (!$descuento) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Descuento no encontrado'
            ], 404);
        }

        $descuento->delete();

        return response()->json([
            'exito' => true,
            'mensaje' => 'Descuento eliminado exitosamente'
        ], 200);
    }

    /**
     * Obtener descuento aplicable para un monto
     * POST /api/descuentos-volumen/calcular
     */
    public function calcular(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'monto' => 'required|numeric|min:0'
        ]);

        $monto = $validated['monto'];
        $descuento = DescuentoVolumen::obtenerDescuentoPara($monto);

        if (!$descuento) {
            return response()->json([
                'exito' => true,
                'mensaje' => 'No aplica descuento por volumen',
                'datos' => [
                    'monto_original' => $monto,
                    'descuento_porcentaje' => 0,
                    'monto_descuento' => 0,
                    'monto_final' => $monto
                ]
            ], 200);
        }

        $montoDescuento = $descuento->calcularDescuento($monto);
        $montoFinal = $monto - $montoDescuento;

        return response()->json([
            'exito' => true,
            'mensaje' => 'Descuento por volumen aplicable',
            'datos' => [
                'monto_original' => $monto,
                'descuento' => $descuento,
                'descuento_porcentaje' => $descuento->porcentaje_descuento,
                'monto_descuento' => $montoDescuento,
                'monto_final' => $montoFinal,
                'informacion' => $descuento->informacion_formateada
            ]
        ], 200);
    }

    /**
     * Listar todas las ofertas vigentes
     * GET /api/descuentos-volumen/vigentes
     */
    public function vigentes(): JsonResponse
    {
        $descuentos = DescuentoVolumen::activos()
            ->orderBy('monto_minimo')
            ->get()
            ->map(function($d) {
                return [
                    'monto_minimo' => $d->monto_minimo,
                    'monto_maximo' => $d->monto_maximo,
                    'porcentaje' => $d->porcentaje_descuento,
                    'descripcion' => $d->descripcion,
                    'informacion' => $d->informacion_formateada
                ];
            });

        return response()->json([
            'exito' => true,
            'total' => $descuentos->count(),
            'datos' => $descuentos
        ], 200);
    }
}
