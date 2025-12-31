<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Mostrar carrito
     */
    public function show()
    {
        $cart = session('cart', []);
        $total = collect($cart)->sum(fn($item) => $item['precio'] * $item['cantidad']);
        
        return view('cart.show', compact('cart', 'total'));
    }

    /**
     * Agregar producto al carrito
     */
    public function add(Request $request)
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|integer|min:1|max:10',
            'precio' => 'required|numeric|min:0'
        ]);

        $producto = Producto::findOrFail($request->producto_id);

        // Verificar stock
        if ($producto->stock_disponible < $request->cantidad) {
            return response()->json([
                'success' => false,
                'message' => 'Stock insuficiente'
            ], 400);
        }

        $cart = session('cart', []);
        
        // Si ya existe, aumentar cantidad
        if (isset($cart[$request->producto_id])) {
            $cart[$request->producto_id]['cantidad'] += $request->cantidad;
        } else {
            $cart[$request->producto_id] = [
                'producto_id' => $producto->id,
                'nombre' => $producto->nombre,
                'precio' => $request->precio,
                'cantidad' => $request->cantidad,
                'imagen' => $producto->imagen_url
            ];
        }

        session(['cart' => $cart]);
        session(['cart_count' => count($cart)]);

        return response()->json([
            'success' => true,
            'message' => 'Producto agregado al carrito',
            'cart_count' => count($cart)
        ]);
    }

    /**
     * Actualizar cantidad de producto
     */
    public function update(Request $request, $productoId)
    {
        $request->validate([
            'cantidad' => 'required|integer|min:1|max:10'
        ]);

        $cart = session('cart', []);
        
        if (isset($cart[$productoId])) {
            $cart[$productoId]['cantidad'] = $request->cantidad;
            session(['cart' => $cart]);
            
            return redirect()->back()->with('success', 'Carrito actualizado');
        }

        return redirect()->back()->with('error', 'Producto no encontrado');
    }

    /**
     * Eliminar producto del carrito
     */
    public function remove($productoId)
    {
        $cart = session('cart', []);
        
        if (isset($cart[$productoId])) {
            unset($cart[$productoId]);
            session(['cart' => $cart]);
            session(['cart_count' => count($cart)]);
            
            return redirect()->back()->with('success', 'Producto eliminado del carrito');
        }

        return redirect()->back()->with('error', 'Producto no encontrado');
    }

    /**
     * Vaciar carrito
     */
    public function clear()
    {
        session()->forget('cart');
        session()->forget('cart_count');
        
        return redirect()->route('home')->with('success', 'Carrito vaciado');
    }
}
