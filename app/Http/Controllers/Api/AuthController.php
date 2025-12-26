<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Services\AuthenticationService;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    protected AuthenticationService $authService;

    public function __construct(AuthenticationService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Registrar nuevo usuario
     * POST /api/auth/register
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        Log::info('Register endpoint hit', [
            'path' => $request->path(),
            'headers' => $request->headers->all(),
            'body_keys' => array_keys($request->all()),
        ]);
        $resultado = $this->authService->registrar($request->validated());

        if ($resultado['exito']) {
            return response()->json($resultado, 201);
        }

        return response()->json($resultado, 400);
    }

    /**
     * Login de usuario
     * POST /api/auth/login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $ipAddress = $request->ip();
        $resultado = $this->authService->autenticar(
            email: $request->email,
            password: $request->password,
            ipAddress: $ipAddress
        );

        if ($resultado['exito']) {
            return response()->json($resultado, 200);
        }

        return response()->json($resultado, 401);
    }

    /**
     * Obtener usuario autenticado
     * GET /api/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        $usuario = $request->user();

        if (!$usuario) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'No autenticado',
            ], 401);
        }

        return response()->json([
            'exito' => true,
            'usuario' => $usuario->makeHidden(['password_hash']),
            'rol' => $usuario->rol,
        ], 200);
    }

    /**
     * Logout del usuario
     * POST /api/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $usuario = $request->user();

        if (!$usuario) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'No autenticado',
            ], 401);
        }

        $resultado = $this->authService->logout(
            usuario: $usuario,
            ipAddress: $request->ip()
        );

        return response()->json($resultado, 200);
    }

    /**
     * Cambiar contraseña
     * POST /api/auth/change-password
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $usuario = $request->user();

        if (!$usuario) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'No autenticado',
            ], 401);
        }

        $resultado = $this->authService->cambiarContrasena(
            usuario: $usuario,
            passwordActual: $request->password_actual,
            passwordNueva: $request->password_nueva,
            ipAddress: $request->ip()
        );

        if ($resultado['exito']) {
            return response()->json($resultado, 200);
        }

        return response()->json($resultado, 400);
    }

    /**
     * Solicitar recuperación de contraseña
     * POST /api/auth/forgot-password
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $resultado = $this->authService->enviarEnlaceRecuperacion(
            email: $request->email
        );

        return response()->json($resultado, 200);
    }

    /**
     * Resetear contraseña con token
     * POST /api/auth/reset-password
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $resultado = $this->authService->resetearContrasena(
            email: $request->email,
            token: $request->token,
            passwordNueva: $request->password,
            ipAddress: $request->ip()
        );

        if ($resultado['exito']) {
            return response()->json($resultado, 200);
        }

        return response()->json($resultado, 400);
    }

    /**
     * Verificar token JWT
     * GET /api/auth/verify-token
     */
    public function verifyToken(Request $request): JsonResponse
    {
        $usuario = $request->user();

        if (!$usuario) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Token inválido o expirado',
            ], 401);
        }

        return response()->json([
            'exito' => true,
            'mensaje' => 'Token válido',
            'usuario_id' => $usuario->id,
        ], 200);
    }
}
