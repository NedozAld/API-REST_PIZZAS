# 🔐 MÓDULO 1: AUTENTICACIÓN - GUÍA API REST

## Estado de Implementación

| Funcionalidad | Estado | Endpoint |
|---|---|---|
| **US-001: Login** | ✅ Completado | `POST /api/auth/login` |
| **US-004: Logout** | ✅ Completado | `POST /api/auth/logout` |
| **US-002: Cambiar Contraseña** | ✅ Completado | `POST /api/auth/change-password` |
| **US-003: Recuperar Contraseña** | ✅ Completado | `POST /api/auth/forgot-password` / `reset-password` |
| **Registro** | ✅ Completado | `POST /api/auth/register` |

---

## 📚 ENDPOINTS DISPONIBLES

### 1. Registro de Usuario

**Endpoint:** `POST /api/auth/register`

**Descripción:** Registrar un nuevo usuario en el sistema

**Headers:**
```
Content-Type: application/json
```

**Body:**
```json
{
  "nombre": "Juan García López",
  "email": "juan@lapizzeria.ec",
  "telefono": "+593998765432",
  "password": "MiPassword@123",
  "password_confirmation": "MiPassword@123"
}
```

**Validaciones:**
- ✓ Nombre: requerido, máx 120 caracteres
- ✓ Email: requerido, único, formato válido
- ✓ Teléfono: opcional, máx 20 caracteres
- ✓ Contraseña: mín 8 caracteres, mayúsculas, minúsculas, números, caracteres especiales
- ✓ Confirmación de contraseña: debe coincidir

**Respuesta exitosa (201):**
```json
{
  "exito": true,
  "mensaje": "Usuario registrado exitosamente",
  "usuario": {
    "id": 5,
    "nombre": "Juan García López",
    "email": "juan@lapizzeria.ec",
    "telefono": "+593998765432",
    "rol_id": 4,
    "estado": "activo",
    "created_at": "2025-12-25T10:30:00.000000Z",
    "updated_at": "2025-12-25T10:30:00.000000Z"
  }
}
```

**Respuesta con error (400):**
```json
{
  "exito": false,
  "mensaje": "Error al registrar usuario: El correo ya está registrado"
}
```

---

### 2. Login (Iniciar Sesión)

**Endpoint:** `POST /api/auth/login`

**Descripción:** Autenticar usuario y obtener token de acceso

**Headers:**
```
Content-Type: application/json
```

**Body:**
```json
{
  "email": "juan@lapizzeria.ec",
  "password": "MiPassword@123"
}
```

**Respuesta exitosa (200):**
```json
{
  "exito": true,
  "mensaje": "Login exitoso",
  "usuario": {
    "id": 5,
    "nombre": "Juan García López",
    "email": "juan@lapizzeria.ec",
    "telefono": "+593998765432",
    "rol_id": 4,
    "estado": "activo",
    "created_at": "2025-12-25T10:30:00.000000Z",
    "updated_at": "2025-12-25T10:30:00.000000Z"
  },
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
}
```

**Respuesta con error (401):**
```json
{
  "exito": false,
  "mensaje": "Credenciales inválidas"
}
```

**Respuesta si cuenta bloqueada (401):**
```json
{
  "exito": false,
  "mensaje": "Cuenta bloqueada por seguridad. Contacta al administrador"
}
```

---

### 3. Obtener Datos del Usuario Autenticado

**Endpoint:** `GET /api/auth/me`

**Descripción:** Obtener información del usuario autenticado

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Respuesta exitosa (200):**
```json
{
  "exito": true,
  "usuario": {
    "id": 5,
    "nombre": "Juan García López",
    "email": "juan@lapizzeria.ec",
    "telefono": "+593998765432",
    "rol_id": 4,
    "estado": "activo",
    "created_at": "2025-12-25T10:30:00.000000Z",
    "updated_at": "2025-12-25T10:30:00.000000Z"
  },
  "rol": {
    "id": 4,
    "nombre": "USUARIO",
    "descripcion": "Usuario estándar del sistema"
  }
}
```

---

### 4. Cambiar Contraseña

**Endpoint:** `POST /api/auth/change-password`

**Descripción:** Cambiar contraseña del usuario autenticado

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Body:**
```json
{
  "password_actual": "MiPassword@123",
  "password_nueva": "MiNuevaPassword@456",
  "password_nueva_confirmation": "MiNuevaPassword@456"
}
```

**Validaciones:**
- ✓ Contraseña actual: obligatoria, mín 6 caracteres
- ✓ Contraseña nueva: mín 8 caracteres, mayúsculas, minúsculas, números, caracteres especiales
- ✓ Confirmación: debe coincidir con la nueva contraseña
- ✓ La nueva contraseña no puede ser igual a la actual

**Respuesta exitosa (200):**
```json
{
  "exito": true,
  "mensaje": "Contraseña actualizada exitosamente"
}
```

**Respuesta si contraseña actual es incorrecta (400):**
```json
{
  "exito": false,
  "mensaje": "La contraseña actual es incorrecta"
}
```

---

### 5. Solicitar Recuperación de Contraseña

**Endpoint:** `POST /api/auth/forgot-password`

**Descripción:** Enviar enlace de recuperación de contraseña al email

**Headers:**
```
Content-Type: application/json
```

**Body:**
```json
{
  "email": "juan@lapizzeria.ec"
}
```

**Respuesta (200):**
```json
{
  "exito": true,
  "mensaje": "Si el correo existe, se enviará un enlace de recuperación"
}
```

**Nota:** El endpoint siempre devuelve éxito por seguridad (no revelar si el email existe o no).

---

### 6. Resetear Contraseña

**Endpoint:** `POST /api/auth/reset-password`

**Descripción:** Actualizar contraseña usando token de recuperación

**Headers:**
```
Content-Type: application/json
```

**Body:**
```json
{
  "email": "juan@lapizzeria.ec",
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "password": "MiNuevaPassword@456",
  "password_confirmation": "MiNuevaPassword@456"
}
```

**Respuesta exitosa (200):**
```json
{
  "exito": true,
  "mensaje": "Contraseña reseteada exitosamente"
}
```

---

### 7. Logout (Cerrar Sesión)

**Endpoint:** `POST /api/auth/logout`

**Descripción:** Cerrar sesión y revocar token de acceso

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Respuesta exitosa (200):**
```json
{
  "exito": true,
  "mensaje": "Logout exitoso"
}
```

---

### 8. Verificar Token

**Endpoint:** `GET /api/auth/verify-token`

**Descripción:** Verificar que el token actual es válido

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Respuesta exitosa (200):**
```json
{
  "exito": true,
  "mensaje": "Token válido",
  "usuario_id": 5
}
```

**Respuesta si token inválido (401):**
```json
{
  "exito": false,
  "mensaje": "Token inválido o expirado"
}
```

---

## 🔒 SEGURIDAD Y POLÍTICAS

### Contraseña

- **Mínimo:** 8 caracteres
- **Complejidad:** Obligatorio mayúsculas, minúsculas, números y caracteres especiales
- **Hash:** bcrypt con 10 rondas (Laravel default)
- **Nunca se devuelve:** La contraseña nunca se incluye en respuestas

### Intentos Fallidos

- **Máximo permitido:** 5 intentos en 15 minutos
- **Acción:** Bloquea la cuenta automáticamente
- **Desbloqueo:** Solo administrador puede desbloquear

### Token JWT

- **Tipo:** Sanctum Personal Access Token
- **Duración:** Sin expiración específica (mantiene duración de sesión)
- **Almacenamiento:** Base de datos (no firmado)
- **Revocación:** Posible mediante logout inmediato

### Auditoría

Cada acción se registra automáticamente en la tabla `auditoria`:
- Usuario que realizó la acción
- Tabla afectada
- Tipo de acción (CREATE, UPDATE, DELETE, LOGIN, etc)
- Dirección IP
- User-Agent
- Timestamp exacto
- Datos anteriores y nuevos (para UPDATE)

---

## 📋 EJEMPLO DE USO COMPLETO

### 1. Registrar usuario
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Juan García",
    "email": "juan@lapizzeria.ec",
    "telefono": "+593998765432",
    "password": "MiPassword@123",
    "password_confirmation": "MiPassword@123"
  }'
```

### 2. Login
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "juan@lapizzeria.ec",
    "password": "MiPassword@123"
  }'
```

**Respuesta:** Obtiene el token

### 3. Obtener datos del usuario
```bash
curl -X GET http://localhost:8000/api/auth/me \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

### 4. Cambiar contraseña
```bash
curl -X POST http://localhost:8000/api/auth/change-password \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "password_actual": "MiPassword@123",
    "password_nueva": "MiNuevaPassword@456",
    "password_nueva_confirmation": "MiNuevaPassword@456"
  }'
```

### 5. Logout
```bash
curl -X POST http://localhost:8000/api/auth/logout \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

## ⚙️ CONFIGURACIÓN

### Archivo `.env`

```env
AUTH_MODEL=App\Models\Usuario
AUTH_GUARD=web
AUTH_PASSWORD_BROKER=usuarios
AUTH_PASSWORD_RESET_TOKEN_TABLE=password_reset_tokens
AUTH_PASSWORD_TIMEOUT=10800
```

### Modelos Utilizados

- `App\Models\Usuario` - Usuario del sistema
- `App\Models\Rol` - Rol asignado
- `App\Models\Sesion` - Sesiones activas
- `App\Models\IntentoFallido` - Intentos fallidos de login
- `App\Models\Auditoria` - Registro de auditoría

### Servicios

- `App\Services\AuthenticationService` - Lógica de autenticación centralizada

### Controladores

- `App\Http\Controllers\Api\AuthController` - Endpoints API REST

---

## 🧪 PRUEBAS CON POSTMAN

Ver archivo: `authentication-api.postman_collection.json`

Pasos:
1. Importar la colección en Postman
2. Reemplazar `{{base_url}}` con `http://localhost:8000`
3. Ejecutar requests en orden

---

## 📝 CHECKLIST DE IMPLEMENTACIÓN

- ✅ Modelo `Usuario` con relaciones
- ✅ Modelo `Rol`, `Sesion`, `IntentoFallido`, `Auditoria`
- ✅ FormRequests para validación
- ✅ AuthenticationService para lógica centralizada
- ✅ AuthController con todos los endpoints
- ✅ Rutas API-REST protegidas
- ✅ Auditoría automática en tabla
- ✅ Bloqueo por intentos fallidos
- ✅ Hashing de contraseñas seguro
- ✅ Tokens Sanctum

---

**Módulo 1 completado: 18/18 puntos** ✅
