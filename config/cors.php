<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['*'],

    'allowed_methods' => ['*'],

    /*
    |--------------------------------------------------------------------------
    | US-092: Allowed Origins (CORS Configurado)
    |--------------------------------------------------------------------------
    |
    | Only allow requests from lapizzeria.ec domain in production.
    | Durante desarrollo, usar FRONTEND_URL en .env
    |
    */

    'allowed_origins' => [
        'https://lapizzeria.ec',
        'https://www.lapizzeria.ec',
        env('FRONTEND_URL', 'http://localhost:3000'), // Para desarrollo
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 86400, // 24 horas

    'supports_credentials' => true,

];
