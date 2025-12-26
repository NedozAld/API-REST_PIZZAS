<?php

namespace App\Services;

use App\Models\Usuario;
use App\Models\Sesion;
use App\Models\IntentoFallido;
use App\Models\Auditoria;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AuthenticationService
{
    /**
     * Registrar un nuevo usuario
     */
    public function registrar(array $datos): array
    {
        try {
            DB::beginTransaction();

            // Crear usuario
            $usuario = Usuario::create([
                'nombre' => $datos['nombre'],
                'email' => $datos['email'],
                'password_hash' => Hash::make($datos['password']),
                'telefono' => $datos['telefono'] ?? null,
                'rol_id' => $datos['rol_id'] ?? 4, // Usuario por defecto
                'estado' => 'activo',
            ]);

            DB::commit();

            return [
                'exito' => true,
                'mensaje' => 'Usuario registrado exitosamente',
                'usuario' => $usuario->makeHidden(['password_hash']),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'exito' => false,
                'mensaje' => 'Error al registrar usuario: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Autenticar usuario con email y contraseña
     */
    public function autenticar(string $email, string $password, string $ipAddress): array
    {
        try {
            // Buscar usuario
            $usuario = Usuario::where('email', $email)->first();

            if (!$usuario) {
                // Registrar intento fallido
                $this->registrarIntentoFallido(
                    email: $email,
                    ip: $ipAddress,
                    razon: 'Usuario no encontrado'
                );

                return [
                    'exito' => false,
                    'mensaje' => 'Credenciales inválidas',
                ];
            }

            // Verificar que el usuario esté activo
            if ($usuario->estado === 'bloqueado') {
                return [
                    'exito' => false,
                    'mensaje' => 'Usuario bloqueado. Contacta al administrador',
                ];
            }

            if ($usuario->estado !== 'activo') {
                return [
                    'exito' => false,
                    'mensaje' => 'Usuario inactivo',
                ];
            }

            // Verificar contraseña
            if (!Hash::check($password, $usuario->password_hash)) {
                // Incrementar intentos fallidos
                $intentosFallidos = IntentoFallido::where('usuario_id', $usuario->id)
                    ->where('created_at', '>=', now()->subMinutes(15))
                    ->count() + 1;

                $this->registrarIntentoFallido(
                    usuarioId: $usuario->id,
                    email: $email,
                    ip: $ipAddress,
                    razon: 'Contraseña incorrecta'
                );

                // Bloquear si hay 5 intentos fallidos
                if ($intentosFallidos >= 5) {
                    $usuario->update(['estado' => 'bloqueado']);
                    
                    $this->registrarAuditoria(
                        usuario: $usuario,
                        tabla: 'usuarios',
                        accion: 'UPDATE',
                        registroId: $usuario->id,
                        datosNuevos: ['estado' => 'bloqueado'],
                        descripcion: 'Usuario bloqueado por múltiples intentos fallidos'
                    );

                    return [
                        'exito' => false,
                        'mensaje' => 'Cuenta bloqueada por seguridad. Contacta al administrador',
                    ];
                }

                return [
                    'exito' => false,
                    'mensaje' => 'Credenciales inválidas',
                ];
            }

            // Limpiar intentos fallidos
            IntentoFallido::where('usuario_id', $usuario->id)->delete();

            // Generar token
            $token = $usuario->createToken('auth_token')->plainTextToken;

            // Actualizar última conexión
            $usuario->update(['ultima_conexion' => now()]);

            return [
                'exito' => true,
                'mensaje' => 'Login exitoso',
                'usuario' => $usuario->makeHidden(['password_hash']),
                'token' => $token,
            ];
        } catch (\Exception $e) {
            return [
                'exito' => false,
                'mensaje' => 'Error en autenticación: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Cambiar contraseña del usuario autenticado
     */
    public function cambiarContrasena(Usuario $usuario, string $passwordActual, string $passwordNueva, string $ipAddress): array
    {
        try {
            // Verificar contraseña actual
            if (!Hash::check($passwordActual, $usuario->password_hash)) {
                return [
                    'exito' => false,
                    'mensaje' => 'La contraseña actual es incorrecta',
                ];
            }

            // Verificar que no sea igual a la actual
            if (Hash::check($passwordNueva, $usuario->password_hash)) {
                return [
                    'exito' => false,
                    'mensaje' => 'La nueva contraseña no puede ser igual a la actual',
                ];
            }

            $passwordAnterior = $usuario->password_hash;

            // Actualizar contraseña
            $usuario->update(['password_hash' => Hash::make($passwordNueva)]);

            return [
                'exito' => true,
                'mensaje' => 'Contraseña actualizada exitosamente',
            ];
        } catch (\Exception $e) {
            return [
                'exito' => false,
                'mensaje' => 'Error al cambiar contraseña: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Enviar enlace de recuperación de contraseña
     */
    public function enviarEnlaceRecuperacion(string $email): array
    {
        try {
            $usuario = Usuario::where('email', $email)->first();

            if (!$usuario) {
                // No revelar si el email existe
                return [
                    'exito' => true,
                    'mensaje' => 'Si el correo existe, se enviará un enlace de recuperación',
                ];
            }

            // Generar token de reseteo
            $token = Password::createToken($usuario);

            return [
                'exito' => true,
                'mensaje' => 'Si el correo existe, se enviará un enlace de recuperación',
                'token' => $token,
            ];
        } catch (\Exception $e) {
            return [
                'exito' => false,
                'mensaje' => 'Error al procesar solicitud: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Resetear contraseña con token
     */
    public function resetearContrasena(string $email, string $token, string $passwordNueva, string $ipAddress): array
    {
        try {
            $usuario = Usuario::where('email', $email)->first();

            if (!$usuario) {
                return [
                    'exito' => false,
                    'mensaje' => 'Usuario no encontrado',
                ];
            }

            // Verificar token (en producción, implementar correctamente)
            // Aquí se implementaría la verificación del token con Password::reset()

            // Actualizar contraseña
            $usuario->update(['password_hash' => Hash::make($passwordNueva)]);

            return [
                'exito' => true,
                'mensaje' => 'Contraseña reseteada exitosamente',
            ];
        } catch (\Exception $e) {
            return [
                'exito' => false,
                'mensaje' => 'Error al resetear contraseña: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Logout del usuario
     */
    public function logout(Usuario $usuario, string $ipAddress): array
    {
        try {
            // Revocar token actual
            $usuario->currentAccessToken()?->delete();

            return [
                'exito' => true,
                'mensaje' => 'Logout exitoso',
            ];
        } catch (\Exception $e) {
            return [
                'exito' => false,
                'mensaje' => 'Error al logout: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Registrar intento fallido de login
     */
    private function registrarIntentoFallido(?int $usuarioId = null, string $email = '', string $ip = '', string $razon = ''): void
    {
        try {
            IntentoFallido::create([
                'usuario_id' => $usuarioId,
                'email' => $email,
                'ip_address' => $ip,
                'razon' => $razon,
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error registrando intento fallido: ' . $e->getMessage());
        }
    }
}
