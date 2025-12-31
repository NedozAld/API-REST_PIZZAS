<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Clientes\CrearDireccionRequest;
use App\Models\DireccionCliente;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DireccionClienteController extends Controller
{
    /**
     * US-044: Listar direcciones del cliente
     * GET /api/clientes/{cliente_id}/direcciones
     */
    public function index(int $cliente_id): JsonResponse
    {
        try {
            $direcciones = DireccionCliente::where('cliente_id', $cliente_id)
                ->where('activa', true)
                ->orderByDesc('favorita')
                ->orderByDesc('created_at')
                ->get();

            return response()->json([
                'exito' => true,
                'total' => $direcciones->count(),
                'datos' => $direcciones->map(function ($dir) {
                    return [
                        'id' => $dir->id,
                        'nombre_direccion' => $dir->nombre_direccion,
                        'calle' => $dir->calle,
                        'numero' => $dir->numero,
                        'apartamento' => $dir->apartamento,
                        'ciudad' => $dir->ciudad,
                        'codigo_postal' => $dir->codigo_postal,
                        'provincia' => $dir->provincia,
                        'referencia' => $dir->referencia,
                        'favorita' => $dir->favorita,
                        'direccion_completo' => $dir->direccion_completo,
                    ];
                }),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'exito' => false,
                'error' => 'Error al obtener direcciones: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * US-044: Crear nueva dirección
     * POST /api/clientes/{cliente_id}/direcciones
     */
    public function store(int $cliente_id, CrearDireccionRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Si es favorita, desactivar otras favoritas
            if ($request->favorita) {
                DireccionCliente::where('cliente_id', $cliente_id)
                    ->update(['favorita' => false]);
            }

            $direccion = DireccionCliente::create([
                'cliente_id' => $cliente_id,
                'nombre_direccion' => $request->nombre_direccion,
                'calle' => $request->calle,
                'numero' => $request->numero,
                'apartamento' => $request->apartamento,
                'ciudad' => $request->ciudad,
                'codigo_postal' => $request->codigo_postal,
                'provincia' => $request->provincia,
                'referencia' => $request->referencia,
                'favorita' => $request->favorita ?? false,
                'activa' => true,
            ]);

            DB::commit();

            return response()->json([
                'exito' => true,
                'mensaje' => 'Dirección agregada exitosamente',
                'direccion' => [
                    'id' => $direccion->id,
                    'nombre_direccion' => $direccion->nombre_direccion,
                    'calle' => $direccion->calle,
                    'numero' => $direccion->numero,
                    'apartamento' => $direccion->apartamento,
                    'ciudad' => $direccion->ciudad,
                    'codigo_postal' => $direccion->codigo_postal,
                    'provincia' => $direccion->provincia,
                    'referencia' => $direccion->referencia,
                    'favorita' => $direccion->favorita,
                    'direccion_completo' => $direccion->direccion_completo,
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'exito' => false,
                'error' => 'Error al crear dirección: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * US-044: Obtener una dirección específica
     * GET /api/clientes/{cliente_id}/direcciones/{id}
     */
    public function show(int $cliente_id, int $id): JsonResponse
    {
        try {
            $direccion = DireccionCliente::where('cliente_id', $cliente_id)
                ->findOrFail($id);

            return response()->json([
                'exito' => true,
                'direccion' => [
                    'id' => $direccion->id,
                    'nombre_direccion' => $direccion->nombre_direccion,
                    'calle' => $direccion->calle,
                    'numero' => $direccion->numero,
                    'apartamento' => $direccion->apartamento,
                    'ciudad' => $direccion->ciudad,
                    'codigo_postal' => $direccion->codigo_postal,
                    'provincia' => $direccion->provincia,
                    'referencia' => $direccion->referencia,
                    'favorita' => $direccion->favorita,
                    'activa' => $direccion->activa,
                    'direccion_completo' => $direccion->direccion_completo,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'exito' => false,
                'error' => 'Dirección no encontrada',
            ], 404);
        }
    }

    /**
     * US-044: Actualizar dirección
     * PUT /api/clientes/{cliente_id}/direcciones/{id}
     */
    public function update(int $cliente_id, int $id, CrearDireccionRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $direccion = DireccionCliente::where('cliente_id', $cliente_id)
                ->findOrFail($id);

            // Si es favorita, desactivar otras favoritas
            if ($request->favorita && !$direccion->favorita) {
                DireccionCliente::where('cliente_id', $cliente_id)
                    ->where('id', '!=', $id)
                    ->update(['favorita' => false]);
            }

            $direccion->update([
                'nombre_direccion' => $request->nombre_direccion,
                'calle' => $request->calle,
                'numero' => $request->numero,
                'apartamento' => $request->apartamento,
                'ciudad' => $request->ciudad,
                'codigo_postal' => $request->codigo_postal,
                'provincia' => $request->provincia,
                'referencia' => $request->referencia,
                'favorita' => $request->favorita ?? $direccion->favorita,
            ]);

            DB::commit();

            return response()->json([
                'exito' => true,
                'mensaje' => 'Dirección actualizada exitosamente',
                'direccion' => [
                    'id' => $direccion->id,
                    'nombre_direccion' => $direccion->nombre_direccion,
                    'calle' => $direccion->calle,
                    'numero' => $direccion->numero,
                    'apartamento' => $direccion->apartamento,
                    'ciudad' => $direccion->ciudad,
                    'codigo_postal' => $direccion->codigo_postal,
                    'provincia' => $direccion->provincia,
                    'referencia' => $direccion->referencia,
                    'favorita' => $direccion->favorita,
                    'direccion_completo' => $direccion->direccion_completo,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'exito' => false,
                'error' => 'Error al actualizar dirección: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * US-044: Eliminar dirección (soft delete)
     * DELETE /api/clientes/{cliente_id}/direcciones/{id}
     */
    public function destroy(int $cliente_id, int $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $direccion = DireccionCliente::where('cliente_id', $cliente_id)
                ->findOrFail($id);

            $direccion->update(['activa' => false]);

            DB::commit();

            return response()->json([
                'exito' => true,
                'mensaje' => 'Dirección eliminada exitosamente',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'exito' => false,
                'error' => 'Error al eliminar dirección: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Marcar dirección como favorita
     * PATCH /api/clientes/{cliente_id}/direcciones/{id}/favorita
     */
    public function marcarFavorita(int $cliente_id, int $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $direccion = DireccionCliente::where('cliente_id', $cliente_id)
                ->findOrFail($id);

            // Desmarcar otras favoritas
            DireccionCliente::where('cliente_id', $cliente_id)
                ->where('id', '!=', $id)
                ->update(['favorita' => false]);

            $direccion->update(['favorita' => true]);

            DB::commit();

            return response()->json([
                'exito' => true,
                'mensaje' => 'Dirección marcada como favorita',
                'direccion' => [
                    'id' => $direccion->id,
                    'nombre_direccion' => $direccion->nombre_direccion,
                    'favorita' => true,
                    'direccion_completo' => $direccion->direccion_completo,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'exito' => false,
                'error' => 'Error al marcar favorita: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener dirección favorita
     * GET /api/clientes/{cliente_id}/direcciones/favorita/obtener
     */
    public function obtenerFavorita(int $cliente_id): JsonResponse
    {
        try {
            $direccion = DireccionCliente::where('cliente_id', $cliente_id)
                ->where('favorita', true)
                ->where('activa', true)
                ->first();

            if (!$direccion) {
                return response()->json([
                    'exito' => false,
                    'error' => 'No hay dirección favorita definida',
                ], 404);
            }

            return response()->json([
                'exito' => true,
                'direccion' => [
                    'id' => $direccion->id,
                    'nombre_direccion' => $direccion->nombre_direccion,
                    'calle' => $direccion->calle,
                    'numero' => $direccion->numero,
                    'apartamento' => $direccion->apartamento,
                    'ciudad' => $direccion->ciudad,
                    'codigo_postal' => $direccion->codigo_postal,
                    'provincia' => $direccion->provincia,
                    'referencia' => $direccion->referencia,
                    'direccion_completo' => $direccion->direccion_completo,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'exito' => false,
                'error' => 'Error al obtener dirección favorita: ' . $e->getMessage(),
            ], 500);
        }
    }
}
