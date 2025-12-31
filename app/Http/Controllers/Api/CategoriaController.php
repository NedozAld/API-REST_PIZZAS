<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CategoriaController extends Controller
{
    /**
     * US-013: Listar todas las categorías
     * GET /api/categorias
     */
    public function index(Request $request): JsonResponse
    {
        $query = Categoria::query();

        // Filtro opcional por estado
        if ($request->has('estado')) {
            $estado = filter_var($request->estado, FILTER_VALIDATE_BOOLEAN);
            $query->where('estado', $estado);
        }

        // Incluir conteo de productos si se solicita
        if ($request->has('con_productos')) {
            $query->withCount(['productos' => function($q) {
                $q->where('activo', true);
            }]);
        }

        // Incluir productos si se solicita
        if ($request->has('incluir_productos')) {
            $query->with(['productos' => function($q) {
                $q->where('activo', true)
                  ->where('disponible', true)
                  ->orderBy('nombre');
            }]);
        }

        $categorias = $query->orderBy('nombre')->get();

        return response()->json([
            'exito' => true,
            'datos' => $categorias
        ], 200);
    }

    /**
     * Crear nueva categoría
     * POST /api/categorias
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:categorias,nombre',
            'descripcion' => 'nullable|string',
            'estado' => 'boolean'
        ]);

        $validated['estado'] = $validated['estado'] ?? true;

        $categoria = Categoria::create($validated);

        return response()->json([
            'exito' => true,
            'mensaje' => 'Categoría creada exitosamente',
            'datos' => $categoria
        ], 201);
    }

    /**
     * Ver detalle de categoría
     * GET /api/categorias/{id}
     */
    public function show(int $id): JsonResponse
    {
        $categoria = Categoria::with(['productos' => function($q) {
            $q->where('activo', true)
              ->orderBy('nombre');
        }])->find($id);

        if (!$categoria) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Categoría no encontrada'
            ], 404);
        }

        return response()->json([
            'exito' => true,
            'datos' => $categoria
        ], 200);
    }

    /**
     * Actualizar categoría
     * PUT /api/categorias/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $categoria = Categoria::find($id);

        if (!$categoria) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Categoría no encontrada'
            ], 404);
        }

        $validated = $request->validate([
            'nombre' => 'sometimes|string|max:100|unique:categorias,nombre,' . $id,
            'descripcion' => 'nullable|string',
            'estado' => 'boolean'
        ]);

        $categoria->update($validated);

        return response()->json([
            'exito' => true,
            'mensaje' => 'Categoría actualizada exitosamente',
            'datos' => $categoria
        ], 200);
    }

    /**
     * Eliminar categoría
     * DELETE /api/categorias/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $categoria = Categoria::withCount('productos')->find($id);

        if (!$categoria) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Categoría no encontrada'
            ], 404);
        }

        // Verificar si tiene productos asociados
        if ($categoria->productos_count > 0) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'No se puede eliminar la categoría porque tiene productos asociados',
                'productos_asociados' => $categoria->productos_count
            ], 400);
        }

        $categoria->delete();

        return response()->json([
            'exito' => true,
            'mensaje' => 'Categoría eliminada exitosamente'
        ], 200);
    }

    /**
     * Obtener estadísticas de categoría
     * GET /api/categorias/{id}/estadisticas
     */
    public function estadisticas(int $id): JsonResponse
    {
        $categoria = Categoria::withCount([
            'productos',
            'productos as productos_activos' => function($q) {
                $q->where('activo', true);
            },
            'productos as productos_disponibles' => function($q) {
                $q->where('disponible', true)->where('activo', true);
            },
            'productos as productos_stock_bajo' => function($q) {
                $q->whereColumn('stock_disponible', '<', 'stock_minimo')
                  ->where('activo', true);
            }
        ])->find($id);

        if (!$categoria) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Categoría no encontrada'
            ], 404);
        }

        return response()->json([
            'exito' => true,
            'datos' => [
                'categoria' => $categoria->nombre,
                'total_productos' => $categoria->productos_count,
                'productos_activos' => $categoria->productos_activos,
                'productos_disponibles' => $categoria->productos_disponibles,
                'productos_stock_bajo' => $categoria->productos_stock_bajo,
            ]
        ], 200);
    }
}
