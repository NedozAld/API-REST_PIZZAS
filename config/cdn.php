<?php

return [

    /*
    |--------------------------------------------------------------------------
    | US-103: CDN Configuration
    |--------------------------------------------------------------------------
    |
    | CloudFlare CDN para servir assets estáticos (imágenes, CSS, JS)
    | 
    | Producción: cdn.lapizzeria.ec
    | Desarrollo: localhost:8000 (sin CDN)
    |
    */

    'enabled' => env('CDN_ENABLED', env('APP_ENV') === 'production'),

    'url' => env('CDN_URL', env('APP_URL')),

    /*
    |--------------------------------------------------------------------------
    | CloudFlare Settings
    |--------------------------------------------------------------------------
    */

    'cloudflare' => [
        'zone_id' => env('CLOUDFLARE_ZONE_ID', ''),
        'api_token' => env('CLOUDFLARE_API_TOKEN', ''),
        
        // Purge cache after deployment
        'auto_purge' => env('CDN_AUTO_PURGE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache TTL por tipo de archivo
    |--------------------------------------------------------------------------
    |
    | Tiempo de caché en segundos para diferentes tipos de archivos
    |
    */

    'ttl' => [
        'images' => 31536000,  // 1 año (imágenes inmutables)
        'css'    => 2592000,   // 1 mes
        'js'     => 2592000,   // 1 mes
        'fonts'  => 31536000,  // 1 año
        'html'   => 0,         // Sin caché
        'default' => 3600,     // 1 hora
    ],

    /*
    |--------------------------------------------------------------------------
    | Paths que usan CDN
    |--------------------------------------------------------------------------
    */

    'paths' => [
        'images' => 'storage/images',
        'css' => 'css',
        'js' => 'js',
        'fonts' => 'fonts',
    ],

];
