<?php

namespace App\Helpers;

/**
 * US-103: CDN Helper
 * Helper para generar URLs de CloudFlare CDN
 */
class CdnHelper
{
    /**
     * Obtener URL completa de CDN para un asset
     * 
     * @param string $path Path relativo del asset (ej: 'images/productos/pizza.jpg')
     * @param bool $forceCdn Forzar uso de CDN incluso en desarrollo
     * @return string URL completa del asset
     */
    public static function asset(string $path, bool $forceCdn = false): string
    {
        // En desarrollo, usar URL local a menos que se fuerce CDN
        if (!$forceCdn && config('app.env') === 'local') {
            return asset($path);
        }

        // En producción, usar CDN CloudFlare
        $cdnUrl = config('cdn.url', config('app.url'));
        
        // Limpiar path (remover barra inicial si existe)
        $path = ltrim($path, '/');
        
        return rtrim($cdnUrl, '/') . '/' . $path;
    }

    /**
     * URL de imagen de producto con CDN
     * 
     * @param string|null $imagePath
     * @return string
     */
    public static function productoImagen(?string $imagePath): string
    {
        if (empty($imagePath)) {
            // Imagen placeholder
            return self::asset('images/productos/placeholder.jpg');
        }

        return self::asset($imagePath);
    }

    /**
     * URL de avatar de usuario con CDN
     * 
     * @param string|null $avatarPath
     * @return string
     */
    public static function avatar(?string $avatarPath): string
    {
        if (empty($avatarPath)) {
            return self::asset('images/avatars/default.png');
        }

        return self::asset($avatarPath);
    }

    /**
     * Verificar si un asset debe usar CDN
     * 
     * @param string $path
     * @return bool
     */
    public static function shouldUseCdn(string $path): bool
    {
        // Usar CDN solo para imágenes, CSS, JS
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $cdnExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'css', 'js', 'svg', 'ico'];
        
        return in_array(strtolower($extension), $cdnExtensions);
    }

    /**
     * Headers de caché para diferentes tipos de archivos
     * 
     * @param string $path
     * @return array
     */
    public static function getCacheHeaders(string $path): array
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        
        return match(strtolower($extension)) {
            // Imágenes: 1 año
            'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg' => [
                'Cache-Control' => 'public, max-age=31536000, immutable',
                'Expires' => gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT',
            ],
            // CSS/JS: 1 mes
            'css', 'js' => [
                'Cache-Control' => 'public, max-age=2592000',
                'Expires' => gmdate('D, d M Y H:i:s', time() + 2592000) . ' GMT',
            ],
            // HTML: Sin caché
            'html', 'htm' => [
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ],
            // Default: 1 hora
            default => [
                'Cache-Control' => 'public, max-age=3600',
                'Expires' => gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT',
            ],
        };
    }
}
