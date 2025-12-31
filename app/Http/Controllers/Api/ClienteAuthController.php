<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Clientes\ClienteLoginRequest;
use App\Http\Requests\Clientes\ClienteRegisterRequest;
use App\Models\Cliente;
use App\Models\Pedido;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ClienteAuthController extends Controller
{
    public function register(ClienteRegisterRequest $request): JsonResponse
    {
        try {
            $cliente = Cliente::create([
                'nombre' => $request->nombre,
                'email' => $request->email,
                'telefono' => $request->telefono,
                'direccion' => $request->direccion,
                'password' => Hash::make($request->password),
                'estado' => 'activo',
            ]);

            $token = $cliente->createToken('cliente_auth_token')->plainTextToken;

            return response()->json([
                'exito' => true,
                'mensaje' => 'Cliente registrado exitosamente',
                'cliente' => $cliente->makeHidden(['password', 'remember_token']),
                'token' => $token,
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Error registrando cliente', [
                'mensaje' => $e->getMessage(),
            ]);

            return response()->json([
                'exito' => false,
                'mensaje' => 'No se pudo registrar el cliente',
            ], 500);
        }
    }

    public function login(ClienteLoginRequest $request): JsonResponse
    {
        $cliente = Cliente::where('email', $request->email)->first();

        if (!$cliente || !Hash::check($request->password, $cliente->password)) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Credenciales inválidas',
            ], 401);
        }

        if ($cliente->estado !== 'activo') {
            return response()->json([
                'exito' => false,
                'mensaje' => 'La cuenta está inactiva, contacta al soporte',
            ], 403);
        }

        $token = $cliente->createToken('cliente_auth_token')->plainTextToken;

        return response()->json([
            'exito' => true,
            'mensaje' => 'Login exitoso',
            'cliente' => $cliente->makeHidden(['password', 'remember_token']),
            'token' => $token,
        ], 200);
    }

    public function me(Request $request): JsonResponse
    {
        $cliente = $request->user();

        if (!$cliente || !$cliente instanceof Cliente) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'No autenticado como cliente',
            ], 401);
        }

        return response()->json([
            'exito' => true,
            'cliente' => $cliente->makeHidden(['password', 'remember_token']),
        ], 200);
    }

    public function pedidos(Request $request): JsonResponse
    {
        $cliente = $request->user();

        if (!$cliente || !$cliente instanceof Cliente) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'No autenticado como cliente',
            ], 401);
        }

        $pedidos = Pedido::with(['detalles.producto'])
            ->where('cliente_id', $cliente->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'exito' => true,
            'pedidos' => $pedidos,
        ], 200);
    }

    public function logout(Request $request): JsonResponse
    {
        $cliente = $request->user();

        if (!$cliente || !$cliente instanceof Cliente) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'No autenticado como cliente',
            ], 401);
        }

        $cliente->currentAccessToken()?->delete();

        return response()->json([
            'exito' => true,
            'mensaje' => 'Logout exitoso',
        ], 200);
    }
}
