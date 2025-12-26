<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pedidos\CrearPedidoRequest;
use App\Models\Pedido;
use App\Models\DetallePedido;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PedidoController extends Controller
{
    /**
     * US-020: Crear Pedido
     * POST /api/pedidos
     */
    public function store(CrearPedidoRequest $request)
    {
        try {
            DB::beginTransaction();

            // Obtener cliente autenticado (puede ser Usuario o Cliente)
            $clienteId = null;
            if (auth('sanctum')->check()) {
                $user = auth('sanctum')->user();
                // Si es de tabla clientes, usar su ID directamente
                if ($user instanceof \App\Models\Cliente) {
                    $clienteId = $user->id;
                }
                // Si es de tabla usuarios (trabajadores), dejar cliente_id como null
            }

            // Calcular totales
            $subtotal = 0;
            $items = $request->input('items');

            foreach ($items as $item) {
                $producto = Producto::findOrFail($item['producto_id']);
                $subtotal += $producto->precio_base * $item['cantidad'];
            }

            // Obtener datos opcionales
            $costoEntrega = $request->input('costo_entrega', 0);
            $montoDescuento = $request->input('monto_descuento', 0);

            // Calcular impuesto (10% por ejemplo, puede venir de configuración)
            $impuesto = $subtotal * 0.10;

            // Total final
            $total = $subtotal + $impuesto + $costoEntrega - $montoDescuento;

            // Crear pedido
            $pedido = Pedido::create([
                'numero_pedido' => Pedido::generarNumeroPedido(),
                'cliente_id' => $clienteId,
                'subtotal' => $subtotal,
                'impuesto' => $impuesto,
                'costo_entrega' => $costoEntrega,
                'monto_descuento' => $montoDescuento,
                'total' => $total,
                'estado' => Pedido::ESTADO_PENDIENTE,
                'notas' => $request->input('notas'),
            ]);

            // Crear detalles del pedido y actualizar stock
            foreach ($items as $item) {
                $producto = Producto::findOrFail($item['producto_id']);

                // Crear detalle
                DetallePedido::create([
                    'pedido_id' => $pedido->id,
                    'producto_id' => $producto->id,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $producto->precio_base,
                    'notas' => $item['notas'] ?? null,
                ]);

                // Reducir stock
                $producto->stock_disponible -= $item['cantidad'];
                $producto->save();
            }

            DB::commit();

            // Cargar relaciones
            $pedido->load(['detalles.producto', 'cliente']);

            return response()->json([
                'success' => true,
                'message' => 'Pedido creado exitosamente',
                'data' => [
                    'pedido' => $pedido,
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear pedido: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear el pedido',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * US-021: Confirmar Pedido Manual
     * PATCH /api/pedidos/{id}/confirmar
     */
    public function confirmar($id)
    {
        try {
            $pedido = Pedido::with(['detalles.producto', 'cliente'])->findOrFail($id);

            // Validar que el pedido pueda ser confirmado
            if (!$pedido->esPendiente() && $pedido->estado !== Pedido::ESTADO_TICKET_ENVIADO) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden confirmar pedidos en estado PENDIENTE o TICKET_ENVIADO',
                ], 400);
            }

            // Actualizar estado y fecha de confirmación
            $pedido->estado = Pedido::ESTADO_CONFIRMADO;
            $pedido->fecha_confirmacion = now();
            $pedido->metodo_confirmacion = 'manual';
            $pedido->save();

            return response()->json([
                'success' => true,
                'message' => 'Pedido confirmado exitosamente',
                'data' => [
                    'pedido' => $pedido,
                ],
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pedido no encontrado',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al confirmar pedido: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al confirmar el pedido',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * US-022: Ver Estado Pedido
     * GET /api/pedidos/{id}
     */
    public function show($id)
    {
        try {
            $pedido = Pedido::with(['detalles.producto', 'cliente'])->findOrFail($id);

            // Si hay cliente autenticado, verificar que sea el dueño del pedido
            if (auth('sanctum')->check()) {
                $user = auth('sanctum')->user();
                
                // Si es un cliente, solo puede ver sus propios pedidos
                if ($user instanceof \App\Models\Cliente && $pedido->cliente_id !== $user->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No tiene permisos para ver este pedido',
                    ], 403);
                }
                // Si es un usuario (trabajador), puede ver cualquier pedido
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'pedido' => $pedido,
                ],
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pedido no encontrado',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al consultar pedido: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al consultar el pedido',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Listar pedidos (opcional, para historial)
     * GET /api/pedidos
     */
    public function index(Request $request)
    {
        try {
            $query = Pedido::with(['detalles.producto', 'cliente']);

            // Si es un cliente, solo ver sus pedidos
            if (auth('sanctum')->check()) {
                $user = auth('sanctum')->user();
                if ($user instanceof \App\Models\Cliente) {
                    $query->where('cliente_id', $user->id);
                }
            }

            // Filtros opcionales
            if ($request->has('estado')) {
                $query->where('estado', $request->input('estado'));
            }

            // Ordenar por más reciente
            $query->orderBy('created_at', 'desc');

            $pedidos = $query->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $pedidos,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error al listar pedidos: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al listar pedidos',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
