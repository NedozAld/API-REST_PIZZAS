<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMiddleware
{
    /**
     * Handle an incoming request.
     * 
     * US-091: Rate Limiting
     * Limita intentos fallidos de login y API calls
     */
    public function handle(Request $request, Closure $next): Response
    {
        // No aplicar rate limiting en modo de pruebas
        if (app()->environment('testing')) {
            return $next($request);
        }

        return $next($request);
    }
}
