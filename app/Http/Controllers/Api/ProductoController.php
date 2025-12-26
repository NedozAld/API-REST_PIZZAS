<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Productos\CrearProductoRequest;
use App\Http\Requests\Productos\ActualizarProductoRequest;
use App\Http\Requests\Productos\ActualizarPrecioRequest;
use App\Models\Producto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    /**
     * US-010: Crear producto
     * POST /api/productos
     */
    public function store(CrearProductoRequest $request): JsonResponse
    {
        $data = $request->validated();
        // Valores por defecto
        $data['stock_disponible'] = $data['stock_disponible'] ?? 0;
        $data['stock_minimo'] = $data['stock_minimo'] ?? 0;
        $data['disponible'] = $data['disponible'] ?? true;
        $data['activo'] = $data['activo'] ?? true;

        $producto = Producto::create($data);

        return response()->json([
            'exito' => true,
            'mensaje' => 'Producto creado exitosamente',
            'producto' => $producto->load('categoria'),
        ], 201);
    }

    /**
     * US-012: Ver menú público
     * GET /api/menu
     */
    public function menuPublico(Request $request): JsonResponse
    {
        $productos = Producto::query()
            ->where('disponible', true)
            ->where('activo', true)
            ->with('categoria')
            ->orderBy('categoria_id')
            ->orderBy('nombre')
            ->get();

        return response()->json([
            'exito' => true,
            'items' => $productos,
        ], 200);
    }

    /**
     * US-011: Editar precio
     * PATCH /api/productos/{id}/precio
     */
    public function actualizarPrecio(ActualizarPrecioRequest $request, int $id): JsonResponse
    {
        $producto = Producto::find($id);
        if (!$producto) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Producto no encontrado',
            ], 404);
        }

        $producto->update(['precio_base' => $request->validated()['precio_base']]);

        return response()->json([
            'exito' => true,
            'mensaje' => 'Precio actualizado exitosamente',
            'producto' => $producto,
        ], 200);
    }

    /**
     * Actualizar producto completo
     * PATCH /api/productos/{id}
     */
    public function update(ActualizarProductoRequest $request, int $id): JsonResponse
    {
        $producto = Producto::find($id);
        if (!$producto) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Producto no encontrado',
            ], 404);
        }

        $producto->update($request->validated());

        return response()->json([
            'exito' => true,
            'mensaje' => 'Producto actualizado exitosamente',
            'producto' => $producto->load('categoria'),
        ], 200);
    }
}
