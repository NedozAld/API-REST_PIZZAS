<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Auditoria;

class AuditoriaMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $response = $next($request);
        $duration = (microtime(true) - $startTime) * 1000;

        // Registrar en auditoría solo si hay usuario autenticado y es una ruta que lo requiera
        if ($request->user() && $this->debeRegistrarse($request)) {
            try {
                Auditoria::create([
                    'usuario_id' => $request->user()->id,
                    'nombre_usuario' => $request->user()->email,
                    'tabla_afectada' => $this->obtenerTabla($request),
                    'tipo_accion' => $this->obtenerAccion($request),
                    'descripcion' => $request->path(),
                    'fecha_accion' => now(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->header('User-Agent'),
                    'duracion_operacion_ms' => (int)$duration,
                ]);
            } catch (\Exception $e) {
                \Log::error('Error registrando auditoría: ' . $e->getMessage());
            }
        }

        return $response;
    }

    /**
     * Determinar si la solicitud debe registrarse
     */
    private function debeRegistrarse(Request $request): bool
    {
        // No registrar GET (lectura)
        return !in_array($request->method(), ['GET', 'HEAD', 'OPTIONS']);
    }

    /**
     * Obtener la tabla afectada
     */
    private function obtenerTabla(Request $request): string
    {
        $path = $request->path();
        $partes = explode('/', $path);
        
        if (count($partes) >= 2) {
            return $partes[1]; // ej: 'usuarios', 'productos', etc
        }

        return 'sistema';
    }

    /**
     * Obtener la acción realizada
     */
    private function obtenerAccion(Request $request): string
    {
        return match ($request->method()) {
            'POST' => 'CREATE',
            'PUT', 'PATCH' => 'UPDATE',
            'DELETE' => 'DELETE',
            default => 'UNKNOWN',
        };
    }
}
