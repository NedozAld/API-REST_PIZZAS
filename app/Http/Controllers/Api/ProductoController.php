<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Productos\CrearProductoRequest;
use App\Http\Requests\Productos\ActualizarProductoRequest;
use App\Http\Requests\Productos\ActualizarPrecioRequest;
use App\Models\Producto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

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

        // US-100: Invalidar caché de productos
        Cache::forget('productos_menu');
        Cache::forget('productos_all');

        return response()->json([
            'exito' => true,
            'mensaje' => 'Producto creado exitosamente',
            'producto' => $producto->load('categoria'),
        ], 201);
    }

    /**
     * US-012: Ver menú público
     * GET /api/menu
     * US-014: Filtrar por categoría (GET /api/menu?categoria=pizza)
     * US-082: Incluye precio con descuento
     * US-100: Caché Redis (TTL: 1 hora)
     */
    public function menuPublico(Request $request): JsonResponse
    {
        // Si hay filtros, no usar caché
        if ($request->has('categoria')) {
            $categoriaParam = $request->categoria;
            
            $query = Producto::query()
                ->where('disponible', true)
                ->where('activo', true)
                ->with('categoria');

            if (is_numeric($categoriaParam)) {
                $query->where('categoria_id', $categoriaParam);
            } else {
                $query->whereHas('categoria', function($q) use ($categoriaParam) {
                    $q->where('nombre', 'ILIKE', $categoriaParam);
                });
            }

            $productos = $query->orderBy('categoria_id')->orderBy('nombre')->get();
        } else {
            // US-100: Caché completo del menú (1 hora = 3600 segundos)
            $productos = Cache::remember('productos_menu', 3600, function() {
                return Producto::query()
                    ->where('disponible', true)
                    ->where('activo', true)
                    ->with('categoria')
                    ->orderBy('categoria_id')
                    ->orderBy('nombre')
                    ->get();
            });
        }

        $items = $productos->map(function($producto) {
            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'descripcion' => $producto->descripcion,
                'precio_base' => $producto->precio_base,
                'descuento_porcentaje' => $producto->descuento_porcentaje,
                'precio_con_descuento' => $producto->precio_con_descuento, // US-082
                'monto_descuento' => $producto->monto_descuento_producto,
                'categoria' => $producto->categoria,
                'imagen_url' => $producto->imagen_url,
                'stock_disponible' => $producto->stock_disponible
            ];
        });

        return response()->json([
            'exito' => true,
            'items' => $items,
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

        // US-100: Invalidar caché de productos
        Cache::forget('productos_menu');
        Cache::forget('productos_all');

        return response()->json([
            'exito' => true,
            'mensaje' => 'Precio actualizado exitosamente',
            'producto' => $producto,
        ], 200);
    }

    /**
     * US-082: Actualizar descuento por producto
     * PATCH /api/productos/{id}/descuento
     */
    public function actualizarDescuento(Request $request, int $id): JsonResponse
    {
        $producto = Producto::find($id);
        if (!$producto) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Producto no encontrado',
            ], 404);
        }

        $validated = $request->validate([
            'descuento_porcentaje' => 'required|numeric|min:0|max:100'
        ]);

        $producto->update($validated);

        // US-100: Invalidar caché de productos
        Cache::forget('productos_menu');
        Cache::forget('productos_all');

        return response()->json([
            'exito' => true,
            'mensaje' => 'Descuento actualizado exitosamente',
            'producto' => [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'precio_base' => $producto->precio_base,
                'descuento_porcentaje' => $producto->descuento_porcentaje,
                'precio_con_descuento' => $producto->precio_con_descuento,
                'monto_descuento' => $producto->monto_descuento_producto
            ]
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

        // US-100: Invalidar caché de productos
        Cache::forget('productos_menu');
        Cache::forget('productos_all');

        return response()->json([
            'exito' => true,
            'mensaje' => 'Producto actualizado exitosamente',
            'producto' => $producto->load('categoria'),
        ], 200);
    }

    /**
     * US-015: Listar productos con stock bajo
     * GET /api/productos/stock-bajo
     */
    public function stockBajo(): JsonResponse
    {
        $productos = Producto::query()
            ->whereColumn('stock_disponible', '<', 'stock_minimo')
            ->where('activo', true)
            ->with('categoria')
            ->orderBy('stock_disponible', 'asc')
            ->get()
            ->map(function($producto) {
                return [
                    'id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'categoria' => $producto->categoria->nombre,
                    'stock_disponible' => $producto->stock_disponible,
                    'stock_minimo' => $producto->stock_minimo,
                    'diferencia' => $producto->stock_minimo - $producto->stock_disponible,
                    'alerta' => $producto->stock_disponible == 0 ? 'CRITICO' : 'BAJO'
                ];
            });

        return response()->json([
            'exito' => true,
            'total' => $productos->count(),
            'productos' => $productos
        ], 200);
    }

    /**
     * Listar todos los productos (con filtros)
     * GET /api/productos
     */
    public function index(Request $request): JsonResponse
    {
        $query = Producto::with('categoria');

        // Filtrar por categoría (US-014)
        if ($request->has('categoria')) {
            $categoriaParam = $request->categoria;
            
            if (is_numeric($categoriaParam)) {
                $query->where('categoria_id', $categoriaParam);
            } else {
                $query->whereHas('categoria', function($q) use ($categoriaParam) {
                    $q->where('nombre', 'ILIKE', $categoriaParam);
                });
            }
        }

        // Filtrar por disponibilidad
        if ($request->has('disponible')) {
            $disponible = filter_var($request->disponible, FILTER_VALIDATE_BOOLEAN);
            $query->where('disponible', $disponible);
        }

        // Filtrar por estado activo
        if ($request->has('activo')) {
            $activo = filter_var($request->activo, FILTER_VALIDATE_BOOLEAN);
            $query->where('activo', $activo);
        }

        // Filtrar por stock bajo (US-015)
        if ($request->has('stock_bajo') && filter_var($request->stock_bajo, FILTER_VALIDATE_BOOLEAN)) {
            $query->whereColumn('stock_disponible', '<', 'stock_minimo');
        }

        // Búsqueda por nombre
        if ($request->has('buscar')) {
            $query->where('nombre', 'ILIKE', '%' . $request->buscar . '%');
        }

        $productos = $query->orderBy('nombre')->get();

        return response()->json([
            'exito' => true,
            'total' => $productos->count(),
            'productos' => $productos
        ], 200);
    }
}
