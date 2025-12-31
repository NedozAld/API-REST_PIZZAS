<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Pedido;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    /**
     * Mostrar formulario de checkout
     */
    public function show()
    {
        // Verificar autenticación
        if (!session('cliente_id')) {
            return redirect()->route('cliente.login')->with('error', 'Debes iniciar sesión para continuar');
        }

        // Verificar carrito
        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('home')->with('error', 'Tu carrito está vacío');
        }

        // Calcular totales
        $subtotal = collect($cart)->sum(fn($item) => $item['precio'] * $item['cantidad']);
        $descuento = 0; // TODO: implementar cupones
        $total = $subtotal - $descuento;

        $cliente = Cliente::find(session('cliente_id'));

        return view('checkout.show', compact('cart', 'subtotal', 'descuento', 'total', 'cliente'));
    }

    /**
     * Procesar pedido
     */
    public function store(Request $request)
    {
        // Verificar autenticación
        if (!session('cliente_id')) {
            return redirect()->route('cliente.login');
        }

        $request->validate([
            'tipo_entrega' => 'required|in:domicilio,local',
            'direccion_entrega' => 'required_if:tipo_entrega,domicilio|nullable|string|max:255',
            'telefono_contacto' => 'required|string|max:20',
            'notas' => 'nullable|string|max:500',
        ], [
            'direccion_entrega.required_if' => 'La dirección es obligatoria para envío a domicilio.',
        ]);

        $cart = session('cart', []);
        if (empty($cart)) {
            return back()->with('error', 'El carrito está vacío');
        }

        $cliente = Cliente::find(session('cliente_id'));

        try {
            // Preparar detalles del pedido
            $detalles = collect($cart)->map(function($item) {
                return [
                    'producto_id' => $item['producto_id'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio']
                ];
            })->values()->toArray();

            $subtotal = collect($cart)->sum(fn($item) => $item['precio'] * $item['cantidad']);

            // Crear pedido
            $pedido = Pedido::create([
                'cliente_id' => $cliente->id,
                'numero_pedido' => 'PED-' . date('YmdHis') . '-' . $cliente->id,
                'estado' => 'PENDIENTE',
                'subtotal' => $subtotal,
                'monto_descuento' => 0,
                'total' => $subtotal,
                'direccion_entrega' => $request->tipo_entrega === 'domicilio' ? $request->direccion_entrega : 'RETIRO EN LOCAL',
                'telefono_contacto' => $request->telefono_contacto,
                'metodo_pago' => 'pendiente',
                'notas' => $request->notas,
            ]);

            // Crear detalles del pedido
            foreach ($detalles as $detalle) {
                $pedido->detalles()->create([
                    'producto_id' => $detalle['producto_id'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'subtotal' => $detalle['cantidad'] * $detalle['precio_unitario']
                ]);
            }

            // Limpiar carrito
            session()->forget(['cart', 'cart_count']);

            // Enviar ticket por WhatsApp al dueño
            try {
                $whatsappService = new WhatsAppService();
                $resultado = $whatsappService->enviarTicket($pedido);
                
                if (!$resultado['exito']) {
                    \Log::warning('No se pudo enviar ticket por WhatsApp', [
                        'pedido_id' => $pedido->id,
                        'error' => $resultado['mensaje']
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('Error al intentar enviar WhatsApp', [
                    'pedido_id' => $pedido->id,
                    'error' => $e->getMessage()
                ]);
            }

            return redirect()->route('checkout.success', $pedido->id);
        } catch (\Exception $e) {
            return back()->with('error', 'Error al crear el pedido: ' . $e->getMessage());
        }
    }

    /**
     * Página de confirmación
     */
    public function success($pedidoId)
    {
        if (!session('cliente_id')) {
            return redirect()->route('cliente.login');
        }

        $pedido = Pedido::with(['detalles.producto', 'cliente'])
            ->where('id', $pedidoId)
            ->where('cliente_id', session('cliente_id'))
            ->firstOrFail();

        return view('checkout.success', compact('pedido'));
    }
}
