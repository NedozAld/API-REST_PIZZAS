<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ClienteAuthController extends Controller
{
    /**
     * Mostrar formulario de login
     */
    public function showLogin()
    {
        return view('cliente.login');
    }

    /**
     * Procesar login de cliente
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);

        // Buscar cliente
        $cliente = Cliente::where('email', $request->email)->first();

        if (!$cliente || !Hash::check($request->password, $cliente->password)) {
            return back()->withErrors(['email' => 'Credenciales inválidas']);
        }

        if ($cliente->estado !== 'activo') {
            return back()->withErrors(['email' => 'La cuenta está inactiva']);
        }

        // Crear token
        $token = $cliente->createToken('cliente_web_token')->plainTextToken;

        // Guardar en sesión
        session([
            'cliente_token' => $token,
            'cliente_id' => $cliente->id,
            'cliente_nombre' => $cliente->nombre,
            'cliente_email' => $cliente->email,
        ]);

        return redirect()->intended(route('home'))->with('success', 'Bienvenido, ' . $cliente->nombre);
    }

    /**
     * Mostrar formulario de registro
     */
    public function showRegister()
    {
        return view('cliente.register');
    }

    /**
     * Procesar registro de cliente
     */
    public function register(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|min:3|max:255',
            'email' => 'required|email|unique:clientes,email',
            'telefono' => 'required|string|min:7|max:20',
            'direccion' => 'nullable|string|max:255',
            'password' => 'required|string|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/',
        ], [
            'password.regex' => 'La contraseña debe contener mayúsculas, minúsculas, números y caracteres especiales.',
            'email.unique' => 'Este email ya está registrado.'
        ]);

        try {
            // Crear cliente
            $cliente = Cliente::create([
                'nombre' => $request->nombre,
                'email' => $request->email,
                'telefono' => $request->telefono,
                'direccion' => $request->direccion ?? '',
                'password' => Hash::make($request->password),
                'estado' => 'activo',
            ]);

            // Crear token
            $token = $cliente->createToken('cliente_web_token')->plainTextToken;

            // Guardar en sesión
            session([
                'cliente_token' => $token,
                'cliente_id' => $cliente->id,
                'cliente_nombre' => $cliente->nombre,
                'cliente_email' => $cliente->email,
            ]);

            return redirect()->route('home')->with('success', 'Registro exitoso, ¡bienvenido!');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['email' => 'Error en el registro: ' . $e->getMessage()]);
        }
    }

    /**
     * Mostrar perfil de cliente
     */
    public function perfil()
    {
        if (!session('cliente_id')) {
            return redirect()->route('cliente.login');
        }

        $cliente = Cliente::find(session('cliente_id'));

        if (!$cliente) {
            return redirect()->route('cliente.login')->with('error', 'Sesión expirada');
        }

        return view('cliente.perfil', ['cliente' => $cliente->toArray()]);
    }

    /**
     * Mostrar mis pedidos
     */
    public function pedidos()
    {
        if (!session('cliente_id')) {
            return redirect()->route('cliente.login');
        }

        $cliente = Cliente::find(session('cliente_id'));

        if (!$cliente) {
            return redirect()->route('cliente.login')->with('error', 'Sesión expirada');
        }

        $pedidos = $cliente->pedidos()
            ->with(['detalles.producto'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($pedido) {
                return [
                    'id' => $pedido->id,
                    'created_at' => $pedido->created_at,
                    'estado' => $pedido->estado,
                    'total' => $pedido->total,
                    'direccion_entrega' => $pedido->direccion_entrega,
                    'detalles' => $pedido->detalles->map(function($detalle) {
                        return [
                            'nombre' => $detalle->producto->nombre ?? 'Producto',
                            'cantidad' => $detalle->cantidad,
                            'subtotal' => $detalle->subtotal
                        ];
                    })
                ];
            })
            ->toArray();

        return view('cliente.pedidos', compact('pedidos'));
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        // Revocar tokens si existe cliente
        if (session('cliente_id')) {
            $cliente = Cliente::find(session('cliente_id'));
            if ($cliente) {
                $cliente->tokens()->delete();
            }
        }

        // Limpiar sesión
        session()->forget([
            'cliente_token',
            'cliente_id',
            'cliente_nombre',
            'cliente_email'
        ]);

        return redirect()->route('home')->with('success', 'Sesión cerrada correctamente');
    }
}
