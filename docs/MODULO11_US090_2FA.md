# MÓDULO 11: SEGURIDAD AVANZADA
## US-090: Two-Factor Authentication (2FA)

**Fecha de Implementación:** 2025-12-29  
**Puntos de Historia:** 6 pts  
**Estado:** ✅ COMPLETADO

---

## 📋 Descripción General

Implementación de **Autenticación de Dos Factores (2FA)** usando **Google Authenticator** con soporte para:
- Generación de códigos QR
- Verificación TOTP (Time-based One-Time Password)
- Códigos de recuperación (backup codes)
- Integración con el flujo de login existente

### Método: Google Authenticator

Se eligió Google Authenticator sobre SMS porque:
- ✅ Sin costos adicionales (SMS requiere Twilio)
- ✅ Mayor seguridad (TOTP de 6 dígitos)
- ✅ Compatible con múltiples apps: Google Authenticator, Microsoft Authenticator, Authy
- ✅ Códigos generados localmente (no necesita conexión)

---

## 🗄️ Cambios en Base de Datos

### Tabla `usuarios` - Nuevos campos

```sql
ALTER TABLE usuarios ADD COLUMN dos_fa_habilitado BOOLEAN DEFAULT false;
ALTER TABLE usuarios ADD COLUMN dos_fa_secret TEXT NULL;
ALTER TABLE usuarios ADD COLUMN dos_fa_backup_codes JSON NULL;
```

**Campos:**

| Campo | Tipo | Default | Descripción |
|-------|------|---------|-------------|
| `dos_fa_habilitado` | BOOLEAN | false | Indica si 2FA está activado |
| `dos_fa_secret` | TEXT | NULL | Secret key de Google Authenticator (cifrado en producción) |
| `dos_fa_backup_codes` | JSON | NULL | Array de códigos de recuperación (8 códigos) |

---

## 📦 Paquetes Instalados

```bash
composer require pragmarx/google2fa-laravel bacon/bacon-qr-code
```

### Dependencias:
- **pragmarx/google2fa-laravel** (v2.3.0) - Librería 2FA para Laravel
- **bacon/bacon-qr-code** (v3.0.3) - Generación de códigos QR
- **pragmarx/google2fa** (v8.0.3) - Core 2FA
- **pragmarx/google2fa-qrcode** (v3.0.1) - QR integrado

---

## 📦 Modelos

### Usuario Model (Actualizado)

**Ubicación:** `app/Models/Usuario.php`

#### Nuevos Campos

**Fillable:**
```php
'dos_fa_habilitado',
'dos_fa_secret',
'dos_fa_backup_codes',
```

**Hidden:**
```php
'password_hash',
'dos_fa_secret',           // No exponer en respuestas
'dos_fa_backup_codes',     // No exponer en respuestas
```

**Casts:**
```php
'dos_fa_habilitado' => 'boolean',
'dos_fa_backup_codes' => 'json',
```

#### Nuevo Método

```php
public function tieneDosFa(): bool
{
    return $this->dos_fa_habilitado === true;
}
```

---

## 🎯 Controlador

### TwoFactorAuthController

**Ubicación:** `app/Http/Controllers/Api/TwoFactorAuthController.php`

#### Métodos

**`setup(SetupTwoFactorRequest $request)`**
- Genera un nuevo secret key
- Crea código QR en SVG
- Retorna secret (para guardar en caso de error)
- No guarda nada en BD (esperando verificación)

**`verify(VerifyTwoFactorRequest $request)`**
- Recibe: secret + código TOTP
- Valida que el código sea correcto (tolerancia de 2 ventanas de 30s)
- Genera 8 códigos de recuperación
- Guarda en BD: secret + backup codes + flag 2fa_habilitado=true

**`disable(VerifyTwoFactorRequest $request)`**
- Recibe: código TOTP actual
- Valida código
- Limpia: dos_fa_secret, dos_fa_backup_codes, dos_fa_habilitado=false

**`verifyLogin(Request $request)`**
- Endpoint para verificar 2FA durante login
- Recibe: email + código
- Soporta: código TOTP o backup code
- Si usa backup code, lo elimina de la lista
- Retorna token Sanctum si válido

---

## 🔌 Endpoints

### 1. POST /api/auth/2fa/setup

Generar código QR y secret para 2FA.

**Autenticación:** ✅ Requerida (auth:sanctum)

**Request:**
```bash
curl -X POST "http://localhost:8000/api/auth/2fa/setup" \
  -H "Authorization: Bearer {TOKEN}"
```

**Response (200):**
```json
{
  "exito": true,
  "datos": {
    "secret": "JBSWY3DPEBLW64TMMQ7GGEN2WIZTMQ4P",
    "qr_code": "<svg>...</svg>",
    "mensaje": "Escanea el código QR con Google Authenticator"
  }
}
```

**Validaciones:**
- Usuario debe estar autenticado
- Usuario NO debe tener 2FA ya habilitado

---

### 2. POST /api/auth/2fa/verify

Verificar código 2FA y habilitar en cuenta.

**Autenticación:** ✅ Requerida

**Request:**
```bash
curl -X POST "http://localhost:8000/api/auth/2fa/verify" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "secret": "JBSWY3DPEBLW64TMMQ7GGEN2WIZTMQ4P",
    "codigo": "123456"
  }'
```

**Response (200):**
```json
{
  "exito": true,
  "datos": {
    "mensaje": "2FA habilitado exitosamente",
    "backup_codes": [
      "AB12CD",
      "EF34GH",
      "IJ56KL",
      "MN78OP",
      "QR90ST",
      "UV12WX",
      "YZ34AB",
      "CD56EF"
    ],
    "instrucciones": "Guarda estos códigos de recuperación en un lugar seguro"
  }
}
```

**Validaciones:**
- Secret debe ser válido (mínimo 16 caracteres)
- Código debe ser 6 dígitos
- Código debe coincidir con secret (tolerancia 2 ventanas)

---

### 3. POST /api/auth/2fa/disable

Deshabilitar 2FA en cuenta.

**Autenticación:** ✅ Requerida

**Request:**
```bash
curl -X POST "http://localhost:8000/api/auth/2fa/disable" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "codigo": "123456"
  }'
```

**Response (200):**
```json
{
  "exito": true,
  "mensaje": "2FA deshabilitado exitosamente"
}
```

**Validaciones:**
- Usuario debe tener 2FA habilitado
- Código debe ser válido con secret actual

---

### 4. POST /api/auth/2fa/verify-login

Verificar 2FA durante login.

**Autenticación:** ❌ NO requerida (usado durante login)

**Request:**
```bash
curl -X POST "http://localhost:8000/api/auth/2fa/verify-login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "usuario@example.com",
    "codigo": "123456"
  }'
```

**Response (200):**
```json
{
  "exito": true,
  "datos": {
    "token": "...",
    "usuario": {
      "id": 1,
      "nombre": "Juan",
      "email": "usuario@example.com",
      "dos_fa_habilitado": true
    }
  }
}
```

**Validaciones:**
- Email debe existir
- Usuario debe tener 2FA habilitado
- Código debe ser válido (TOTP o backup code)

---

## 📝 Form Requests

### SetupTwoFactorRequest

**Ubicación:** `app/Http/Requests/SetupTwoFactorRequest.php`

No requiere campos - solo validación de autenticación.

### VerifyTwoFactorRequest

**Ubicación:** `app/Http/Requests/VerifyTwoFactorRequest.php`

**Validaciones:**
- `codigo` (required, 6 dígitos): `regex:/^\d{6}$/`
- `secret` (nullable, mínimo 16): para verify setup
- `email` (nullable, email): para verify-login

---

## 🔐 Flujo de Autenticación 2FA

### Paso 1: Usuario activa 2FA

```
1. Usuario autenticado llama POST /api/auth/2fa/setup
2. Servidor genera secret + QR Code
3. Usuario escanea QR con Google Authenticator
4. Usuario ve código de 6 dígitos en la app
```

### Paso 2: Usuario verifica código

```
1. Usuario obtiene código del Authenticator
2. Envía POST /api/auth/2fa/verify con secret + código
3. Servidor valida código (TOTP)
4. Si válido:
   - Guarda secret en BD
   - Genera 8 códigos de recuperación
   - Retorna backup codes
5. Usuario guarda backup codes en lugar seguro
```

### Paso 3: Login con 2FA

```
1. Usuario hace login normal (POST /api/auth/login)
   - Email + contraseña
2. Servidor valida credenciales
3. Si 2FA está habilitado:
   - Retorna instrucción "2FA requerido"
   - Cliente obtiene código del Authenticator
4. Usuario envía POST /api/auth/2fa/verify-login
   - Email + código
5. Servidor valida código TOTP
6. Si válido: retorna token Sanctum
```

### Paso 4: Usuario desactiva 2FA

```
1. Usuario autenticado llama POST /api/auth/2fa/disable
2. Envía código actual del Authenticator
3. Servidor valida código
4. Si válido:
   - Limpia secret de BD
   - Limpia backup codes
   - Desactiva dos_fa_habilitado
```

---

## 🧪 Ejemplos de Uso

### Ejemplo 1: Activar 2FA

```bash
# Paso 1: Generar QR Code
curl -X POST "http://localhost:8000/api/auth/2fa/setup" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc..." \
  -H "Content-Type: application/json"

# Respuesta:
# {
#   "exito": true,
#   "datos": {
#     "secret": "JBSWY3DPEBLW64TMMQ...",
#     "qr_code": "<svg>...</svg>"
#   }
# }

# Paso 2: Usuario escanea QR con Google Authenticator
# Ver código en app (ej: 123456)

# Paso 3: Verificar código
curl -X POST "http://localhost:8000/api/auth/2fa/verify" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc..." \
  -H "Content-Type: application/json" \
  -d '{
    "secret": "JBSWY3DPEBLW64TMMQ...",
    "codigo": "123456"
  }'

# Respuesta:
# {
#   "exito": true,
#   "datos": {
#     "mensaje": "2FA habilitado exitosamente",
#     "backup_codes": ["AB12CD", "EF34GH", ...]
#   }
# }

# USUARIO GUARDA BACKUP CODES EN LUGAR SEGURO
```

### Ejemplo 2: Login con 2FA

```bash
# Paso 1: Login normal
curl -X POST "http://localhost:8000/api/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "usuario@example.com",
    "password": "contraseña"
  }'

# Respuesta si 2FA activo:
# {
#   "exito": false,
#   "mensaje": "2FA requerido",
#   "requiere_2fa": true
# }

# Paso 2: Verificar 2FA
# Usuario abre Google Authenticator → ve código 234567

curl -X POST "http://localhost:8000/api/auth/2fa/verify-login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "usuario@example.com",
    "codigo": "234567"
  }'

# Respuesta:
# {
#   "exito": true,
#   "datos": {
#     "token": "...",
#     "usuario": {...}
#   }
# }

# Paso 3: Usar token para acceder a endpoints protegidos
curl -X GET "http://localhost:8000/api/auth/me" \
  -H "Authorization: Bearer ..."
```

### Ejemplo 3: Usar Backup Code

```bash
# Si usuario perdió acceso a Authenticator, puede usar backup code
curl -X POST "http://localhost:8000/api/auth/2fa/verify-login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "usuario@example.com",
    "codigo": "AB12CD"  # Backup code en lugar de TOTP
  }'

# Respuesta: mismo token Sanctum
# El backup code es eliminado de la lista (no puede usarse 2 veces)
```

### Ejemplo 4: Desactivar 2FA

```bash
# Usuario obtiene código actual del Authenticator
curl -X POST "http://localhost:8000/api/auth/2fa/disable" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc..." \
  -H "Content-Type: application/json" \
  -d '{
    "codigo": "567890"
  }'

# Respuesta:
# {
#   "exito": true,
#   "mensaje": "2FA deshabilitado exitosamente"
# }

# 2FA está completamente desactivado
# Login futuro solo requiere email + contraseña
```

---

## 📱 Aplicaciones Compatibles

Cualquier app TOTP compatible:

| Aplicación | iOS | Android | Desktop |
|-----------|-----|---------|---------|
| **Google Authenticator** | ✅ | ✅ | - |
| **Microsoft Authenticator** | ✅ | ✅ | ✅ |
| **Authy** | ✅ | ✅ | ✅ |
| **LastPass Authenticator** | ✅ | ✅ | - |
| **FreeOTP** | ✅ | ✅ | - |

Todas soportan TOTP estándar generado por PragmaRX Google2FA.

---

## 🔒 Consideraciones de Seguridad

### Secret Key
- ✅ Se guarda en BD (en producción debería estar encriptado)
- ✅ No se expone en respuestas del servidor (hidden)
- ✅ Se utiliza solo para validar códigos TOTP

### Códigos TOTP
- ✅ Validez: 30 segundos
- ✅ Tolerancia: ±2 ventanas (60 segundos total)
- ✅ Formato: 6 dígitos
- ✅ No reutilizables en el mismo intervalo de tiempo

### Backup Codes
- ✅ 8 códigos generados aleatoriamente
- ✅ Se eliminan al usarse (single-use)
- ✅ Para recuperación en caso de pérdida de Authenticator
- ✅ NO se exponen en respuestas (hidden)

### Rate Limiting
- ⏳ Recomendado: Limitar intentos fallidos de 2FA
- ⏳ Por implementar en US-091

---

## 🚀 Integración con Login Existente

### Cambios necesarios en Frontend

El login debería tener lógica como:

```javascript
// 1. Login normal
const loginResponse = await fetch('/api/auth/login', {
  method: 'POST',
  body: JSON.stringify({
    email: email,
    password: password
  })
});

if (loginResponse.requiere_2fa) {
  // 2. Mostrar pantalla de 2FA
  const code = prompt('Ingresa código de 6 dígitos');
  
  // 3. Verificar 2FA
  const twoFaResponse = await fetch('/api/auth/2fa/verify-login', {
    method: 'POST',
    body: JSON.stringify({
      email: email,
      codigo: code
    })
  });
  
  // 4. Usar token
  localStorage.setItem('token', twoFaResponse.datos.token);
}
```

---

## ✅ Checklist de Implementación

- [x] Instalar pragmarx/google2fa-laravel
- [x] Crear migración con campos 2FA
- [x] Actualizar modelo Usuario
- [x] Crear TwoFactorAuthController
- [x] Crear Form Requests
- [x] Registrar rutas en auth.php
- [x] Ejecutar migración
- [x] Actualizar endpoint /api/auth/me
- [x] Documentación completa

---

## 🧪 Testing Manual

### Test 1: Setup 2FA

```bash
# 1. Login
TOKEN=$(curl -s -X POST "http://localhost:8000/api/auth/login" \
  -d "email=admin@example.com&password=admin" | jq -r '.datos.token')

# 2. Setup
curl -X POST "http://localhost:8000/api/auth/2fa/setup" \
  -H "Authorization: Bearer $TOKEN" | jq

# VERIFICAR: Obtiene secret y QR code
```

### Test 2: Verify 2FA

```bash
# Usar secret del test anterior
curl -X POST "http://localhost:8000/api/auth/2fa/verify" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "secret": "...",
    "codigo": "123456"  # Código del Authenticator
  }' | jq

# VERIFICAR: Retorna backup codes, dos_fa_habilitado=true
```

### Test 3: Check /api/auth/me

```bash
curl -X GET "http://localhost:8000/api/auth/me" \
  -H "Authorization: Bearer $TOKEN" | jq '.usuario'

# VERIFICAR: "dos_fa_habilitado": true
```

### Test 4: Disable 2FA

```bash
curl -X POST "http://localhost:8000/api/auth/2fa/disable" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "codigo": "234567"  # Código actual
  }' | jq

# VERIFICAR: "dos_fa_habilitado": false en siguiente /api/auth/me
```

---

## 📊 Resultados de Implementación

| Aspecto | Estado |
|--------|--------|
| **Migraciones** | ✅ 1 creada y ejecutada |
| **Modelos** | ✅ 1 actualizado (Usuario) |
| **Controladores** | ✅ 1 nuevo (TwoFactorAuthController) |
| **Form Requests** | ✅ 2 nuevos |
| **Rutas** | ✅ 4 nuevas |
| **Documentación** | ✅ Completa |
| **Paquetes** | ✅ 2 instalados (google2fa-laravel, bacon-qr-code) |

---

## 🎯 Próximas Fases

### US-091: Rate Limiting (4 pts)
- Limitar intentos fallidos de 2FA
- Bloquear account temporalmente
- Registrar intentos en auditoría

### US-092: CORS Configurado (3 pts)
- Configurar CORS solo para dominio permitido
- Headers de seguridad

### US-093: Validación CSRF (2 pts)
- Tokens CSRF en formularios
- Middleware CSRF

---

## 📚 Referencias

- [PragmaRX Google2FA Laravel](https://github.com/pragmarx/google2fa-laravel)
- [TOTP Spec RFC 6238](https://tools.ietf.org/html/rfc6238)
- [Google Authenticator](https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2)

---

**Generado:** 2025-12-29  
**Versión:** 1.0  
**Estado:** ✅ COMPLETADO Y DOCUMENTADO
