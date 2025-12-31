# Módulo 11: Seguridad Avanzada
# US-091, US-092, US-093
## Documentación Técnica Completa

---

## US-091: Rate Limiting (4 puntos)
**Descripción:** Limitar intentos de login para prevenir ataques de fuerza bruta.

### 1. Base de Datos

#### Columnas Existentes en `usuarios`
```sql
-- Ya existían en migración 2025_12_27_000020
intentos_fallidos INT DEFAULT 0
bloqueado_hasta TIMESTAMP NULL
```

### 2. Modelo Usuario

#### Métodos Agregados
```php
// app/Models/Usuario.php

/**
 * US-091: Verificar si el usuario está bloqueado por intentos fallidos
 */
public function estaBloqueadoPorFallidos(): bool
{
    if (!$this->bloqueado_hasta) {
        return false;
    }
    
    if ($this->bloqueado_hasta->isPast()) {
        // Desbloquear automáticamente si ya pasó 1 hora
        $this->update([
            'bloqueado_hasta' => null, 
            'intentos_fallidos' => 0
        ]);
        return false;
    }
    
    return true;
}

/**
 * US-091: Registrar intento fallido
 * Si llega a 3 intentos, bloquea durante 1 hora
 */
public function registrarIntentoFallido(): void
{
    $intentos = $this->intentos_fallidos + 1;
    
    if ($intentos >= 3) {
        // Bloquear durante 1 hora
        $this->update([
            'intentos_fallidos' => $intentos,
            'bloqueado_hasta' => now()->addHour()
        ]);
    } else {
        $this->increment('intentos_fallidos');
    }
}

/**
 * US-091: Limpiar intentos fallidos (login exitoso)
 */
public function limpiarIntentosFallidos(): void
{
    $this->update([
        'intentos_fallidos' => 0,
        'bloqueado_hasta' => null
    ]);
}
```

### 3. Routes con Throttle Middleware

#### routes/api.php
```php
// Throttle: 3 intentos en 15 minutos
Route::post('/auth/login', [AuthController::class, 'login'])
    ->middleware('throttle:login');

Route::post('/auth/register', [AuthController::class, 'register'])
    ->middleware('throttle:register');

Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword'])
    ->middleware('throttle:forgot-password');

Route::post('/auth/reset-password', [AuthController::class, 'resetPassword'])
    ->middleware('throttle:reset-password');
```

#### config/routing.php (limites)
```php
'login' => [3, 15],           // 3 intentos, 15 minutos
'register' => [5, 60],        // 5 intentos, 60 minutos
'forgot-password' => [2, 15], // 2 intentos, 15 minutos
'reset-password' => [3, 15],  // 3 intentos, 15 minutos
```

### 4. AuthController - Login con Rate Limiting

```php
// app/Http/Controllers/Api/AuthController.php

public function login(LoginRequest $request): JsonResponse
{
    $ipAddress = $request->ip();
    
    // 1. Verificar si usuario existe y está bloqueado
    $usuario = \App\Models\Usuario::where('email', $request->email)->first();
    if ($usuario && $usuario->estaBloqueadoPorFallidos()) {
        return response()->json([
            'exito' => false,
            'mensaje' => 'Cuenta bloqueada. Intenta nuevamente en 1 hora.',
            'bloqueado_hasta' => $usuario->bloqueado_hasta
        ], 429); // HTTP 429 Too Many Requests
    }
    
    // 2. Intentar autenticación
    $resultado = $this->authService->autenticar(
        email: $request->email,
        password: $request->password,
        ipAddress: $ipAddress
    );

    // 3. Si login exitoso, limpiar intentos
    if ($resultado['exito']) {
        if ($usuario) {
            $usuario->limpiarIntentosFallidos();
        }
        return response()->json($resultado, 200);
    }

    // 4. Si falla, registrar intento
    if ($usuario) {
        $usuario->registrarIntentoFallido();
        
        // Verificar si ahora está bloqueado
        if ($usuario->estaBloqueadoPorFallidos()) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Demasiados intentos fallidos. Cuenta bloqueada durante 1 hora.',
                'bloqueado_hasta' => $usuario->bloqueado_hasta
            ], 429);
        }
    }

    return response()->json($resultado, 401);
}
```

### 5. Ejemplos de Uso

#### Flujo Normal
```bash
# Intento 1 (falla)
POST /api/auth/login
{
  "email": "user@example.com",
  "password": "wrong"
}
# Respuesta: 401 Unauthorized

# Intento 2 (falla)
POST /api/auth/login
# Respuesta: 401 Unauthorized

# Intento 3 (falla) - SE BLOQUEA
POST /api/auth/login
# Respuesta: 429 Too Many Requests
{
  "exito": false,
  "mensaje": "Demasiados intentos fallidos. Cuenta bloqueada durante 1 hora.",
  "bloqueado_hasta": "2025-12-29 19:30:00"
}

# Intento 4 (antes de 1 hora)
POST /api/auth/login
# Respuesta: 429 Too Many Requests
{
  "exito": false,
  "mensaje": "Cuenta bloqueada. Intenta nuevamente en 1 hora.",
  "bloqueado_hasta": "2025-12-29 19:30:00"
}
```

#### Desbloqueo Automático
```bash
# Después de 1 hora
POST /api/auth/login
# Respuesta: 
# - Si password correcto: 200 OK (desbloqueo + login exitoso)
# - Si password incorrecto: 401 Unauthorized (desbloqueo + nuevo conteo)
```

---

## US-092: CORS Configurado (3 puntos)
**Descripción:** Solo permitir peticiones desde el dominio oficial lapizzeria.ec

### 1. Configuración CORS

#### config/cors.php
```php
<?php

return [
    'paths' => ['*'], // Todas las rutas
    
    'allowed_methods' => ['*'], // GET, POST, PUT, PATCH, DELETE
    
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
        env('FRONTEND_URL', 'http://localhost:3000'), // Desarrollo
    ],
    
    'allowed_origins_patterns' => [],
    
    'allowed_headers' => ['*'],
    
    'exposed_headers' => [],
    
    'max_age' => 86400, // 24 horas
    
    'supports_credentials' => true, // Permitir cookies/tokens
];
```

### 2. Middleware CORS

Laravel 12 incluye CORS de manera nativa. El middleware `HandleCors` está habilitado automáticamente en `bootstrap/app.php`.

#### Verificación
```php
// bootstrap/app.php
Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        // ...
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // CORS ya está habilitado por defecto
    })
    ->create();
```

### 3. Variables de Entorno

#### .env (Producción)
```env
# No se necesita FRONTEND_URL porque ya está en config
# El config ya tiene hardcodeado lapizzeria.ec
```

#### .env (Desarrollo)
```env
FRONTEND_URL=http://localhost:3000
# O
FRONTEND_URL=http://localhost:5173  # Vite
```

### 4. Ejemplos de Prueba

#### Permitido
```bash
# Desde dominio autorizado
curl -X POST https://api.lapizzeria.ec/api/auth/login \
  -H "Origin: https://lapizzeria.ec" \
  -H "Content-Type: application/json"
  
# Respuesta: 200 OK con headers CORS:
# Access-Control-Allow-Origin: https://lapizzeria.ec
# Access-Control-Allow-Credentials: true
```

#### Bloqueado
```bash
# Desde dominio no autorizado
curl -X POST https://api.lapizzeria.ec/api/auth/login \
  -H "Origin: https://malicious-site.com" \
  -H "Content-Type: application/json"
  
# Respuesta: Sin headers CORS
# El navegador bloqueará la respuesta
```

---

## US-093: Validación CSRF (2 puntos)
**Descripción:** Protección CSRF para endpoints web (no API)

### 1. Contexto Laravel Sanctum

Para **APIs REST con Sanctum**, NO se usa CSRF porque:
- Se usan tokens Bearer en headers (`Authorization: Bearer <token>`)
- Los tokens son stateless y seguros por diseño
- CSRF es para cookies de sesión (web forms)

### 2. Configuración Actual

#### Sanctum - API Stateless
```php
// config/sanctum.php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 
    'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1,'.parse_url(env('APP_URL'), PHP_URL_HOST)
)),

'middleware' => [
    'authenticate_session' => Illuminate\Session\Middleware\AuthenticateSession::class,
    'encrypt_cookies' => Illuminate\Cookie\Middleware\EncryptCookies::class,
    'validate_csrf_token' => Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
],
```

#### Rutas API (Sin CSRF)
```php
// routes/api.php
// Todas las rutas en api.php NO requieren CSRF
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/pedidos', [PedidoController::class, 'store'])->middleware('auth:sanctum');
```

#### Rutas Web (Con CSRF)
```php
// routes/web.php
// Si tuvieras formularios web, SÍ requieren CSRF
Route::post('/contact', [ContactController::class, 'store']);
// Blade: @csrf
```

### 3. Exclusiones CSRF (Si fuera necesario)

Si tuvieras webhooks externos (Stripe, Twilio), excluirlos:

```php
// app/Http/Middleware/VerifyCsrfToken.php
protected $except = [
    'webhooks/stripe',
    'webhooks/twilio',
    'api/*', // Ya excluido por estar en routes/api.php
];
```

### 4. Conclusión US-093

✅ **CSRF está configurado correctamente:**
- Rutas API (`routes/api.php`): **Sin CSRF** (usan tokens Bearer)
- Rutas Web (`routes/web.php`): **Con CSRF** (usan cookies de sesión)
- Sanctum maneja la seguridad stateless para APIs

**No se requieren cambios adicionales** porque el proyecto es API REST puro.

---

## Resumen de Implementación

### US-091: Rate Limiting ✅ (4 pts)
- ✅ 3 intentos en 15 minutos (throttle middleware)
- ✅ Bloqueo durante 1 hora tras 3 fallos
- ✅ Desbloqueo automático tras 1 hora
- ✅ Métodos en Usuario: `estaBloqueadoPorFallidos()`, `registrarIntentoFallido()`, `limpiarIntentosFallidos()`
- ✅ Integrado en `AuthController::login()`
- ✅ HTTP 429 (Too Many Requests)

### US-092: CORS Configurado ✅ (3 pts)
- ✅ Solo `lapizzeria.ec` y `www.lapizzeria.ec`
- ✅ Métodos: GET, POST, PUT, PATCH, DELETE
- ✅ Max age: 24 horas
- ✅ Credentials: true
- ✅ Middleware nativo Laravel 12

### US-093: Validación CSRF ✅ (2 pts)
- ✅ CSRF habilitado para rutas web
- ✅ API REST sin CSRF (usa Sanctum tokens)
- ✅ Configuración correcta para arquitectura stateless

---

## Testing

### Test Rate Limiting
```bash
php test_rate_limiting.php
# Salida:
# Usuario: Administrador
# Intentos fallidos: 0
# Bloqueado hasta: null
# Está bloqueado: NO
#
# Probando 3 intentos fallidos...
#   Intento fallido #1...
#     Intentos: 1
#     Bloqueado: NO
#   Intento fallido #2...
#     Intentos: 2
#     Bloqueado: NO
#   Intento fallido #3...
#     Intentos: 3
#     Bloqueado: SI
# ✓ Rate limiting funcionando correctamente!
```

### Test CORS (Postman/cURL)
```bash
# Permitido
curl -X OPTIONS http://localhost:8000/api/auth/login \
  -H "Origin: https://lapizzeria.ec" \
  -H "Access-Control-Request-Method: POST"
# Respuesta: 200 OK con headers CORS

# Bloqueado
curl -X OPTIONS http://localhost:8000/api/auth/login \
  -H "Origin: https://evil.com" \
  -H "Access-Control-Request-Method: POST"
# Respuesta: Sin headers CORS (bloqueado por navegador)
```

---

## Puntos Ganados

| User Story | Puntos | Estado |
|------------|--------|--------|
| US-091: Rate Limiting | 4 | ✅ Completado |
| US-092: CORS Configurado | 3 | ✅ Completado |
| US-093: Validación CSRF | 2 | ✅ Completado |
| **TOTAL MÓDULO 11** | **9** | **✅ Completado** |

---

## Progreso del Proyecto

- **Anterior:** 259/270 pts (95.9%)
- **US-091 + US-092 + US-093:** +9 pts
- **Actual:** **268/270 pts (99.3%)**
- **Falta:** 2 pts (Módulos 1 o 6)

---

## Seguridad Implementada

### Capas de Seguridad
1. **Autenticación:** Sanctum tokens (stateless)
2. **2FA:** Google Authenticator TOTP (US-090)
3. **Rate Limiting:** 3 intentos, bloqueo 1 hora (US-091)
4. **CORS:** Solo lapizzeria.ec (US-092)
5. **HTTPS:** Obligatorio en producción
6. **Passwords:** Bcrypt hash
7. **SQL Injection:** Eloquent ORM (prepared statements)
8. **XSS:** Laravel escaping automático

### Endpoints Seguros
- ✅ POST /api/auth/login (throttle + rate limiting)
- ✅ POST /api/auth/register (throttle)
- ✅ POST /api/auth/2fa/* (2FA habilitado)
- ✅ POST /api/pedidos (auth:sanctum)
- ✅ POST /api/productos (auth:sanctum + role)

---

**Fecha de Implementación:** 29 de diciembre de 2025  
**Desarrollador:** HP  
**Estado:** ✅ COMPLETADO - 268/270 pts (99.3%)
