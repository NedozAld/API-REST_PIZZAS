<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * US-101: Middleware de compresión GZIP
 * Comprime respuestas JSON, HTML, CSS, JS
 * Reduce tamaño de respuestas en ~80%
 */
class CompressResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Solo comprimir si el cliente acepta gzip
        if (!$this->shouldCompress($request, $response)) {
            return $response;
        }

        // Comprimir contenido
        $content = $response->getContent();
        if ($content && strlen($content) > 1000) { // Solo comprimir si > 1KB
            $compressed = gzencode($content, 6); // Nivel 6 (balance velocidad/compresión)
            
            $response->setContent($compressed);
            $response->headers->set('Content-Encoding', 'gzip');
            $response->headers->set('Content-Length', strlen($compressed));
            $response->headers->remove('Transfer-Encoding');
        }

        return $response;
    }

    /**
     * Determinar si se debe comprimir la respuesta
     */
    private function shouldCompress(Request $request, Response $response): bool
    {
        // Verificar que el cliente acepta gzip
        $acceptEncoding = $request->header('Accept-Encoding', '');
        if (stripos($acceptEncoding, 'gzip') === false) {
            return false;
        }

        // Verificar que la respuesta no esté ya comprimida
        if ($response->headers->has('Content-Encoding')) {
            return false;
        }

        // Comprimir solo estos tipos de contenido
        $contentType = $response->headers->get('Content-Type', '');
        $compressibleTypes = [
            'application/json',
            'text/html',
            'text/css',
            'text/javascript',
            'application/javascript',
            'text/xml',
            'application/xml',
        ];

        foreach ($compressibleTypes as $type) {
            if (stripos($contentType, $type) !== false) {
                return true;
            }
        }

        return false;
    }
}
