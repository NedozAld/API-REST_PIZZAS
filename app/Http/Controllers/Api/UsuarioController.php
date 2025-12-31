<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Usuarios\CrearUsuarioRequest;
use App\Http\Requests\Usuarios\AsignarRolRequest;
use App\Http\Requests\Usuarios\CambiarEstadoRequest;
use App\Models\User;
use App\Models\Auditoria;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UsuarioController extends Controller
{
    /**
     * US-060: Crear Usuario (Admin)
     * POST /api/usuarios
     */
    public function store(CrearUsuarioRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $usuario = User::create([
                'nombre' => $request->nombre,
                'email' => $request->email,
                'password_hash' => Hash::make($request->password),
                'rol_id' => $request->rol_id,
                'telefono' => $request->telefono,
                'estado' => 'activo',
            ]);

            // Registrar en auditoría
            Auditoria::create([
                'usuario_id' => auth('sanctum')->id(),
                'nombre_usuario' => auth('sanctum')->user()->nombre,
                'tabla_afectada' => 'usuarios',
                'tipo_accion' => 'CREAR',
                'registro_id' => $usuario->id,
                'datos_nuevos' => json_encode([
                    'nombre' => $usuario->nombre,
                    'email' => $usuario->email,
                    'rol_id' => $usuario->rol_id,
                ]),
                'descripcion' => "Usuario creado: {$usuario->nombre}",
                'fecha_accion' => now(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            DB::commit();

            return response()->json([
                'exito' => true,
                'mensaje' => 'Usuario creado exitosamente',
                'usuario' => [
                    'id' => $usuario->id,
                    'nombre' => $usuario->nombre,
                    'email' => $usuario->email,
                    'rol_id' => $usuario->rol_id,
                    'estado' => $usuario->estado,
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'exito' => false,
                'error' => 'Error al crear usuario: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * US-062: Ver Usuarios (Listado)
     * GET /api/usuarios
     */
    public function index(): JsonResponse
    {
        try {
            $usuarios = User::with('rol')
                ->paginate(15);

            return response()->json([
                'exito' => true,
                'total' => $usuarios->total(),
                'por_pagina' => $usuarios->per_page(),
                'pagina_actual' => $usuarios->current_page(),
                'total_paginas' => $usuarios->last_page(),
                'datos' => $usuarios->items(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'exito' => false,
                'error' => 'Error al obtener usuarios: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener usuario por ID
     * GET /api/usuarios/{id}
     */
    public function show(int $id): JsonResponse
    {
        try {
            $usuario = User::with('rol')->findOrFail($id);

            return response()->json([
                'exito' => true,
                'usuario' => $usuario,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'exito' => false,
                'error' => 'Usuario no encontrado',
            ], 404);
        }
    }

    /**
     * US-061: Asignar Rol
     * PUT /api/usuarios/{id}/rol
     */
    public function asignarRol(int $id, AsignarRolRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $usuario = User::findOrFail($id);
            $rolAnterior = $usuario->rol_id;

            $usuario->update([
                'rol_id' => $request->rol_id,
            ]);

            // Registrar en auditoría
            Auditoria::create([
                'usuario_id' => auth('sanctum')->id(),
                'nombre_usuario' => auth('sanctum')->user()->nombre,
                'tabla_afectada' => 'usuarios',
                'tipo_accion' => 'ACTUALIZAR',
                'registro_id' => $usuario->id,
                'datos_anteriores' => json_encode(['rol_id' => $rolAnterior]),
                'datos_nuevos' => json_encode(['rol_id' => $request->rol_id]),
                'descripcion' => "Rol asignado a {$usuario->nombre}: de rol {$rolAnterior} a {$request->rol_id}",
                'fecha_accion' => now(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            DB::commit();

            return response()->json([
                'exito' => true,
                'mensaje' => 'Rol asignado exitosamente',
                'usuario' => $usuario->load('rol'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'exito' => false,
                'error' => 'Error al asignar rol: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * US-063: Desactivar Usuario (Cambiar estado)
     * PATCH /api/usuarios/{id}/estado
     */
    public function cambiarEstado(int $id, CambiarEstadoRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $usuario = User::findOrFail($id);
            $estadoAnterior = $usuario->estado;

            $usuario->update([
                'estado' => $request->estado,
            ]);

            // Registrar en auditoría
            Auditoria::create([
                'usuario_id' => auth('sanctum')->id(),
                'nombre_usuario' => auth('sanctum')->user()->nombre,
                'tabla_afectada' => 'usuarios',
                'tipo_accion' => 'ACTUALIZAR',
                'registro_id' => $usuario->id,
                'datos_anteriores' => json_encode(['estado' => $estadoAnterior]),
                'datos_nuevos' => json_encode(['estado' => $request->estado]),
                'descripcion' => "Estado cambiado para {$usuario->nombre}: de {$estadoAnterior} a {$request->estado}",
                'fecha_accion' => now(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            DB::commit();

            return response()->json([
                'exito' => true,
                'mensaje' => "Usuario {$request->estado}",
                'usuario' => $usuario,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'exito' => false,
                'error' => 'Error al cambiar estado: ' . $e->getMessage(),
            ], 500);
        }
    }
}
