<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pedidos\CrearPedidoRequest;
use App\Http\Requests\Pedidos\ActualizarEstadoPedidoRequest;
use App\Http\Requests\Pedidos\EditarPedidoRequest;
use App\Http\Requests\Pedidos\CancelarPedidoRequest;
use App\Http\Requests\Pedidos\MarcarEntregadoRequest;
use App\Http\Requests\Pedidos\AgregarNotasRequest;
use App\Models\Pedido;
use App\Models\DetallePedido;
use App\Models\Producto;
use App\Models\Cupon;
use App\Models\DescuentoVolumen;
use App\Services\NotificacionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PedidoController extends Controller
{
    public function __construct(private NotificacionService $notificacionService)
    {
    }

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
            $descuentoProductos = 0; // US-082

            foreach ($items as $item) {
                $producto = Producto::findOrFail($item['producto_id']);
                // US-082: Usar precio con descuento si aplica
                $precioUnitario = $producto->precio_con_descuento;
                $subtotal += $precioUnitario * $item['cantidad'];
                
                // Acumular descuento por producto
                if ($producto->tieneDescuentoProducto()) {
                    $descuentoProductos += $producto->monto_descuento_producto * $item['cantidad'];
                }
            }

            // Obtener datos opcionales
            $costoEntrega = $request->input('costo_entrega', 0);
            $montoDescuento = $request->input('monto_descuento', 0);

            // US-083: Aplicar descuento por volumen automáticamente
            $descuentoVolumen = 0;
            $descuentoVolumenObj = DescuentoVolumen::obtenerDescuentoPara($subtotal);
            if ($descuentoVolumenObj) {
                $descuentoVolumen = $descuentoVolumenObj->calcularDescuento($subtotal);
            }

            // Calcular impuesto (10% por ejemplo, puede venir de configuración)
            $impuesto = $subtotal * 0.10;

            // Total final
            // Descuentos: cupón + volumen (se aplica el que sea mayor)
            $descuentoMaximo = max($montoDescuento, $descuentoVolumen);
            $total = $subtotal + $impuesto + $costoEntrega - $descuentoMaximo;

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

                // US-082: Usar precio con descuento
                $precioUnitario = $producto->precio_con_descuento;

                // Crear detalle
                DetallePedido::create([
                    'pedido_id' => $pedido->id,
                    'producto_id' => $producto->id,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $precioUnitario,
                    'notas' => $item['notas'] ?? null,
                ]);

                // Reducir stock
                $producto->stock_disponible -= $item['cantidad'];
                $producto->save();
            }

            DB::commit();

            // Cargar relaciones
            $pedido->load(['detalles.producto', 'cliente']);

            // Notificar llegada de nuevo pedido
            $this->notificacionService->crear(
                tipo: 'pedido_nuevo',
                pedidoId: $pedido->id,
                titulo: 'Nuevo pedido ' . $pedido->numero_pedido,
                descripcion: 'Total: $' . $pedido->total
            );

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

            $this->notificacionService->crear(
                tipo: 'pedido_confirmado',
                pedidoId: $pedido->id,
                titulo: 'Pedido confirmado',
                descripcion: 'Pedido ' . $pedido->numero_pedido . ' confirmado'
            );

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

            // Filtros adicionales (US-025)
            if ($request->has('fecha_desde')) {
                $query->where('created_at', '>=', $request->input('fecha_desde'));
            }

            if ($request->has('fecha_hasta')) {
                $query->where('created_at', '<=', $request->input('fecha_hasta'));
            }

            if ($request->has('cliente_id')) {
                $query->where('cliente_id', $request->input('cliente_id'));
            }

            if ($request->has('numero_pedido')) {
                $query->where('numero_pedido', 'like', '%' . $request->input('numero_pedido') . '%');
            }

            // Ordenar por más reciente
            $query->orderBy('created_at', 'desc');

            $pedidos = $query->paginate(15);

            return response()->json([
                'success' => true,
                'filtros_aplicados' => [
                    'estado' => $request->input('estado'),
                    'fecha_desde' => $request->input('fecha_desde'),
                    'fecha_hasta' => $request->input('fecha_hasta'),
                    'numero_pedido' => $request->input('numero_pedido'),
                ],
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

    /**
     * US-035: Cambiar estado de pedido (cocinero / dashboard)
     * PATCH /api/pedidos/{id}/estado
     */
    public function actualizarEstado(ActualizarEstadoPedidoRequest $request, int $id)
    {
        $pedido = Pedido::find($id);
        if (!$pedido) {
            return response()->json([
                'success' => false,
                'message' => 'Pedido no encontrado',
            ], 404);
        }

        $estadoAnterior = $pedido->estado;

        $pedido->estado = $request->estado;

        if ($request->estado === Pedido::ESTADO_CANCELADO && $request->filled('motivo_cancelacion')) {
            $pedido->motivo_cancelacion = $request->motivo_cancelacion;
        }

        if ($request->estado === Pedido::ESTADO_CONFIRMADO && !$pedido->fecha_confirmacion) {
            $pedido->fecha_confirmacion = now();
            $pedido->metodo_confirmacion = $pedido->metodo_confirmacion ?? 'manual';
        }

        $pedido->save();

        $this->notificacionService->crear(
            tipo: 'pedido_estado',
            pedidoId: $pedido->id,
            titulo: 'Estado actualizado',
            descripcion: 'Pedido ' . $pedido->numero_pedido . ' ahora esta en ' . $pedido->estado
        );

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado',
            'data' => [
                'estado_anterior' => $estadoAnterior,
                'estado_actual' => $pedido->estado,
            ],
        ], 200);
    }

    /**
     * US-024: Editar Pedido (solo si está pendiente)
     * PUT /api/pedidos/{id}
     */
    public function update(EditarPedidoRequest $request, int $id)
    {
        try {
            DB::beginTransaction();

            $pedido = Pedido::with(['detalles.producto', 'cliente'])->findOrFail($id);

            // Validar que el pedido esté pendiente
            if ($pedido->estado !== Pedido::ESTADO_PENDIENTE) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden editar pedidos en estado PENDIENTE',
                ], 400);
            }

            // Restaurar stock de items anteriores
            foreach ($pedido->detalles as $detalle) {
                $producto = $detalle->producto;
                $producto->stock_disponible += $detalle->cantidad;
                $producto->save();
            }

            // Eliminar detalles anteriores
            DetallePedido::where('pedido_id', $pedido->id)->delete();

            // Recalcular totales
            $subtotal = 0;
            $items = $request->input('items');

            foreach ($items as $item) {
                $producto = Producto::findOrFail($item['producto_id']);
                $subtotal += $producto->precio_base * $item['cantidad'];
            }

            $costoEntrega = $request->input('costo_entrega', 0);
            $montoDescuento = $request->input('monto_descuento', 0);
            $impuesto = $subtotal * 0.10;
            $total = $subtotal + $impuesto + $costoEntrega - $montoDescuento;

            // Actualizar pedido
            $pedido->update([
                'subtotal' => $subtotal,
                'impuesto' => $impuesto,
                'costo_entrega' => $costoEntrega,
                'monto_descuento' => $montoDescuento,
                'total' => $total,
                'notas' => $request->input('notas'),
            ]);

            // Crear nuevos detalles y reducir stock
            foreach ($items as $item) {
                $producto = Producto::findOrFail($item['producto_id']);

                DetallePedido::create([
                    'pedido_id' => $pedido->id,
                    'producto_id' => $producto->id,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $producto->precio_base,
                    'notas' => $item['notas'] ?? null,
                ]);

                $producto->stock_disponible -= $item['cantidad'];
                $producto->save();
            }

            DB::commit();

            $pedido->load(['detalles.producto', 'cliente']);

            $this->notificacionService->crear(
                tipo: 'pedido_editado',
                pedidoId: $pedido->id,
                titulo: 'Pedido editado',
                descripcion: 'Pedido ' . $pedido->numero_pedido . ' fue actualizado'
            );

            return response()->json([
                'success' => true,
                'message' => 'Pedido actualizado exitosamente',
                'data' => [
                    'pedido' => $pedido,
                ],
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Pedido o producto no encontrado',
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al editar pedido: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al editar el pedido',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * US-023: Cancelar Pedido
     * DELETE /api/pedidos/{id}
     */
    public function destroy(CancelarPedidoRequest $request, int $id)
    {
        try {
            DB::beginTransaction();

            $pedido = Pedido::findOrFail($id);

            // Validar que pueda ser cancelado
            if (!$pedido->puedeSerCancelado()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El pedido no puede ser cancelado en estado ' . $pedido->estado,
                ], 400);
            }

            // Restaurar stock
            foreach ($pedido->detalles as $detalle) {
                $producto = $detalle->producto;
                $producto->stock_disponible += $detalle->cantidad;
                $producto->save();
            }

            // Marcar como cancelado en lugar de eliminar
            $pedido->update([
                'estado' => Pedido::ESTADO_CANCELADO,
                'motivo_cancelacion' => $request->input('motivo'),
            ]);

            DB::commit();

            $this->notificacionService->crear(
                tipo: 'pedido_cancelado',
                pedidoId: $pedido->id,
                titulo: 'Pedido cancelado',
                descripcion: 'Pedido ' . $pedido->numero_pedido . ' fue cancelado'
            );

            return response()->json([
                'success' => true,
                'message' => 'Pedido cancelado exitosamente',
                'data' => [
                    'pedido_id' => $pedido->id,
                    'estado' => $pedido->estado,
                    'motivo' => $pedido->motivo_cancelacion,
                ],
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Pedido no encontrado',
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al cancelar pedido: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar el pedido',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * US-026: Marcar Pedido como Entregado
     * PATCH /api/pedidos/{id}/entregado
     */
    public function marcarEntregado(int $id, MarcarEntregadoRequest $request)
    {
        try {
            DB::beginTransaction();

            $pedido = Pedido::findOrFail($id);

            // Validar que está en estado CONFIRMADO
            if ($pedido->estado->nombre !== 'CONFIRMADO') {
                return response()->json([
                    'exito' => false,
                    'error' => 'Solo se pueden marcar como entregados pedidos en estado CONFIRMADO',
                ], 400);
            }

            // Cambiar a estado ENTREGADO
            $estadoEntregado = \App\Models\EstadoPedido::where('nombre', 'ENTREGADO')->firstOrFail();
            
            $pedido->update([
                'estado_id' => $estadoEntregado->id,
                'fecha_entrega' => $request->fecha_entrega ?? now(),
            ]);

            // Crear notificación
            $this->notificacionService->crear(
                $pedido->cliente_id,
                'pedido_entregado',
                "Tu pedido #{$pedido->numero_pedido} ha sido entregado",
                $pedido->id
            );

            DB::commit();

            return response()->json([
                'exito' => true,
                'mensaje' => 'Pedido marcado como entregado',
                'pedido' => [
                    'id' => $pedido->id,
                    'numero_pedido' => $pedido->numero_pedido,
                    'estado' => $pedido->estado->nombre,
                    'fecha_entrega' => $pedido->fecha_entrega,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'exito' => false,
                'error' => 'Error al marcar como entregado: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * US-027: Agregar Notas al Pedido
     * PUT /api/pedidos/{id}/notas
     */
    public function agregarNotas(int $id, AgregarNotasRequest $request)
    {
        try {
            DB::beginTransaction();

            $pedido = Pedido::findOrFail($id);

            $pedido->update([
                'notas' => $request->notas,
            ]);

            DB::commit();

            return response()->json([
                'exito' => true,
                'mensaje' => 'Notas actualizado exitosamente',
                'pedido' => [
                    'id' => $pedido->id,
                    'numero_pedido' => $pedido->numero_pedido,
                    'notas' => $pedido->notas,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'exito' => false,
                'error' => 'Error al agregar notas: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * US-028: Búsqueda Avanzada de Pedidos
     * GET /api/pedidos/buscar?q=...&cliente=...&estado=...&fecha_desde=...&fecha_hasta=...&precio_min=...&precio_max=...
     */
    public function buscar(Request $request)
    {
        try {
            $query = Pedido::with('cliente', 'estado', 'detalles.producto');

            // Filtro por número de pedido o cliente
            if ($request->has('q') && $request->q) {
                $buscar = $request->q;
                $query->where(function ($q) use ($buscar) {
                    $q->where('numero_pedido', 'like', "%{$buscar}%")
                      ->orWhereHas('cliente', function ($q) use ($buscar) {
                          $q->where('nombre', 'like', "%{$buscar}%")
                            ->orWhere('email', 'like', "%{$buscar}%");
                      });
                });
            }

            // Filtro por estado
            if ($request->has('estado') && $request->estado) {
                $query->whereHas('estado', function ($q) use ($request) {
                    $q->where('nombre', strtoupper($request->estado));
                });
            }

            // Filtro por cliente
            if ($request->has('cliente_id') && $request->cliente_id) {
                $query->where('cliente_id', $request->cliente_id);
            }

            // Filtro por rango de fechas
            if ($request->has('fecha_desde') && $request->fecha_desde) {
                $query->whereDate('fecha_creacion', '>=', $request->fecha_desde);
            }
            if ($request->has('fecha_hasta') && $request->fecha_hasta) {
                $query->whereDate('fecha_creacion', '<=', $request->fecha_hasta);
            }

            // Filtro por rango de precios
            if ($request->has('precio_min') && $request->precio_min) {
                $query->where('total', '>=', $request->precio_min);
            }
            if ($request->has('precio_max') && $request->precio_max) {
                $query->where('total', '<=', $request->precio_max);
            }

            $pedidos = $query->orderBy('fecha_creacion', 'desc')
                ->paginate(15);

            return response()->json([
                'exito' => true,
                'total' => $pedidos->total(),
                'por_pagina' => $pedidos->per_page(),
                'pagina_actual' => $pedidos->current_page(),
                'total_paginas' => $pedidos->last_page(),
                'filtros' => [
                    'buscar' => $request->q ?? null,
                    'estado' => $request->estado ?? null,
                    'cliente_id' => $request->cliente_id ?? null,
                    'fecha_desde' => $request->fecha_desde ?? null,
                    'fecha_hasta' => $request->fecha_hasta ?? null,
                    'precio_min' => $request->precio_min ?? null,
                    'precio_max' => $request->precio_max ?? null,
                ],
                'datos' => $pedidos->items(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'exito' => false,
                'error' => 'Error en la búsqueda: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * US-029: Reasumir Pedido (Cliente repite su último pedido)
     * POST /api/pedidos/repetir/{pedido_id}
     */
    public function repetirPedido(int $pedido_id)
    {
        try {
            DB::beginTransaction();

            // Obtener pedido original
            $pedidoOriginal = Pedido::findOrFail($pedido_id);

            // Validar que sea del cliente autenticado
            $clienteId = auth('sanctum')->user()->id;
            if ($pedidoOriginal->cliente_id != $clienteId) {
                return response()->json([
                    'exito' => false,
                    'error' => 'No tienes permiso para repetir este pedido',
                ], 403);
            }

            // Crear nuevo pedido
            $numeroPedido = 'PED-' . date('YmdHis') . '-' . random_int(100, 999);
            
            $nuevoPedido = Pedido::create([
                'numero_pedido' => $numeroPedido,
                'cliente_id' => $clienteId,
                'estado_id' => $pedidoOriginal->estado_id,
                'subtotal' => $pedidoOriginal->subtotal,
                'impuesto' => $pedidoOriginal->impuesto,
                'costo_entrega' => $pedidoOriginal->costo_entrega,
                'monto_descuento' => $pedidoOriginal->monto_descuento,
                'total' => $pedidoOriginal->total,
                'notas' => "Repetido del pedido {$pedidoOriginal->numero_pedido}",
            ]);

            // Copiar detalles del pedido original
            foreach ($pedidoOriginal->detalles as $detalle) {
                DetallePedido::create([
                    'pedido_id' => $nuevoPedido->id,
                    'producto_id' => $detalle->producto_id,
                    'cantidad' => $detalle->cantidad,
                    'precio_unitario' => $detalle->precio_unitario,
                    'subtotal' => $detalle->subtotal,
                ]);

                // Reducir stock
                $producto = Producto::find($detalle->producto_id);
                if ($producto && $producto->stock > 0) {
                    $producto->decrement('stock', $detalle->cantidad);
                }
            }

            // Crear notificación
            $this->notificacionService->crear(
                $clienteId,
                'pedido_nuevo',
                "Tu nuevo pedido #{$nuevoPedido->numero_pedido} ha sido creado (repetición)",
                $nuevoPedido->id
            );

            DB::commit();

            return response()->json([
                'exito' => true,
                'mensaje' => 'Pedido repetido exitosamente',
                'pedido' => [
                    'id' => $nuevoPedido->id,
                    'numero_pedido' => $nuevoPedido->numero_pedido,
                    'total' => $nuevoPedido->total,
                    'fecha_creacion' => $nuevoPedido->fecha_creacion,
                    'items' => $nuevoPedido->detalles->count(),
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'exito' => false,
                'error' => 'Error al repetir pedido: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * US-081: Aplicar Cupón a Pedido
     * POST /api/pedidos/{id}/cupon
     */
    public function aplicarCupon(Request $request, int $id)
    {
        try {
            DB::beginTransaction();

            // Validar código de cupón
            $request->validate([
                'codigo' => 'required|string'
            ]);

            // Buscar pedido
            $pedido = Pedido::find($id);
            
            if (!$pedido) {
                return response()->json([
                    'exito' => false,
                    'error' => 'Pedido no encontrado'
                ], 404);
            }

            // Verificar que el pedido no tenga cupón aplicado
            if ($pedido->cupon_id) {
                return response()->json([
                    'exito' => false,
                    'error' => 'Este pedido ya tiene un cupón aplicado'
                ], 400);
            }

            // Verificar que el pedido esté en estado pendiente o confirmado
            if (!in_array($pedido->estado_pedido_id, [1, 2])) {
                return response()->json([
                    'exito' => false,
                    'error' => 'Solo se puede aplicar cupón a pedidos pendientes o confirmados'
                ], 400);
            }

            // Buscar cupón
            $cupon = Cupon::where('codigo', $request->codigo)->first();

            if (!$cupon) {
                return response()->json([
                    'exito' => false,
                    'error' => 'Cupón no encontrado'
                ], 404);
            }

            // Validar cupón
            $validacion = $cupon->esValido($pedido->subtotal);

            if (!$validacion['valido']) {
                return response()->json([
                    'exito' => false,
                    'error' => $validacion['mensaje']
                ], 400);
            }

            // Verificar si el cliente ya usó este cupón
            if ($pedido->cliente_id && $cupon->fueUsadoPor($pedido->cliente_id)) {
                return response()->json([
                    'exito' => false,
                    'error' => 'Este cliente ya ha usado este cupón anteriormente'
                ], 400);
            }

            // Calcular descuento
            $descuento = $cupon->calcularDescuento($pedido->subtotal);
            $nuevoTotal = $pedido->subtotal - $descuento;

            // Actualizar pedido
            $pedido->cupon_id = $cupon->id;
            $pedido->monto_descuento = $descuento;
            $pedido->total = $nuevoTotal;
            $pedido->save();

            // Registrar uso del cupón
            if ($pedido->cliente_id) {
                $cupon->registrarUso($pedido->cliente_id);
            } else {
                // Si no hay cliente_id, solo incrementar contador
                $cupon->increment('usos_actuales');
            }

            DB::commit();

            return response()->json([
                'exito' => true,
                'mensaje' => 'Cupón aplicado exitosamente',
                'datos' => [
                    'pedido_id' => $pedido->id,
                    'cupon' => $cupon->codigo,
                    'descuento_aplicado' => $descuento,
                    'subtotal' => $pedido->subtotal,
                    'total_anterior' => $pedido->subtotal,
                    'total_nuevo' => $nuevoTotal,
                    'informacion_cupon' => $cupon->informacion_formateada
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al aplicar cupón: ' . $e->getMessage());
            
            return response()->json([
                'exito' => false,
                'error' => 'Error al aplicar cupón: ' . $e->getMessage()
            ], 500);
        }
    }
}
