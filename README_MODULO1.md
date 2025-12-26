# 🍕 API REST - MÓDULO 1: AUTENTICACIÓN
## LA PIZZERÍA - CRAZY SNAKES

**Estado:** ✅ COMPLETADO
**Última actualización:** 25 de Diciembre, 2025
**Endpoints implementados:** 8 + 1 de prueba

---

## 📥 INSTALACIÓN Y SETUP

### 1. Clonar el proyecto
```bash
git clone <repo-url> pizzeria-api
cd pizzeria-api
```

### 2. Instalar dependencias
```bash
composer install
```

### 3. Configurar .env
```bash
cp .env.example .env
php artisan key:generate
```

**Variables importantes en `.env`:**
```env
AUTH_MODEL=App\Models\Usuario
AUTH_GUARD=web
AUTH_PASSWORD_BROKER=usuarios
```

### 4. Ejecutar migraciones
```bash
php artisan migrate
```

### 5. Cargar datos de prueba (Opcional)
```bash
php artisan db:seed --class=RolesAndUsersSeeder
```

### 6. Iniciar servidor
```bash
php artisan serve
```

El servidor estará disponible en: **http://localhost:8000**

---

## 🔑 USUARIOS DE PRUEBA

Después de ejecutar el seeder, puedes usar estos usuarios:

| Email | Contraseña | Rol | Descripción |
|-------|-----------|-----|-------------|
| `admin@lapizzeria.ec` | `Admin@123456` | Administrador | Acceso total |
| `usuario@lapizzeria.ec` | `Usuario@123456` | Usuario | Usuario estándar |
| `cocinero@lapizzeria.ec` | `Cocinero@123456` | Op-Cocina | Operador cocina |
| `repartidor@lapizzeria.ec` | `Repartidor@123456` | Op-Delivery | Operador delivery |

---

## 🧪 PRUEBAS CON CURL

### 1. REGISTRAR NUEVO USUARIO

```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "nombre": "Nueva Persona",
    "email": "nueva@lapizzeria.ec",
    "telefono": "+593998765432",
    "password": "NuevaPass@123",
    "password_confirmation": "NuevaPass@123"
  }'
```

### 2. LOGIN

```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "admin@lapizzeria.ec",
    "password": "Admin@123456"
  }'
```

**Respuesta exitosa incluirá:**
```json
{
  "exito": true,
  "mensaje": "Login exitoso",
  "usuario": { ... },
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
}
```

Guarda el **token** para las siguientes solicitudes.

### 3. OBTENER USUARIO AUTENTICADO

```bash
curl -X GET http://localhost:8000/api/auth/me \
  -H "Authorization: Bearer {TOKEN_AQUI}" \
  -H "Content-Type: application/json"
```

### 4. CAMBIAR CONTRASEÑA

```bash
curl -X POST http://localhost:8000/api/auth/change-password \
  -H "Authorization: Bearer {TOKEN_AQUI}" \
  -H "Content-Type: application/json" \
  -d '{
    "password_actual": "Admin@123456",
    "password_nueva": "MiNuevaPass@789",
    "password_nueva_confirmation": "MiNuevaPass@789"
  }'
```

### 5. SOLICITAR RECUPERACIÓN DE CONTRASEÑA

```bash
curl -X POST http://localhost:8000/api/auth/forgot-password \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@lapizzeria.ec"
  }'
```

### 6. RESETEAR CONTRASEÑA

```bash
curl -X POST http://localhost:8000/api/auth/reset-password \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@lapizzeria.ec",
    "token": "TOKEN_AQUI",
    "password": "OtraPass@456",
    "password_confirmation": "OtraPass@456"
  }'
```

### 7. VERIFICAR TOKEN

```bash
curl -X GET http://localhost:8000/api/auth/verify-token \
  -H "Authorization: Bearer {TOKEN_AQUI}" \
  -H "Content-Type: application/json"
```

### 8. LOGOUT

```bash
curl -X POST http://localhost:8000/api/auth/logout \
  -H "Authorization: Bearer {TOKEN_AQUI}" \
  -H "Content-Type: application/json"
```

---

## 🛠️ PRUEBAS CON POSTMAN

### Importar colección

1. Abre Postman
2. Click en **Import**
3. Busca `authentication-api.postman_collection.json` en el proyecto
4. Importa la colección

### Variables de entorno

Crea una variable de entorno en Postman:
- **Variable:** `token`
- **Valor:** (Se asigna automáticamente después del login)

### Flujo recomendado de pruebas

1. **Register** - Crear nuevo usuario
2. **Login** - Obtener token
3. **Get Me** - Verificar datos autenticado
4. **Verify Token** - Confirmar token válido
5. **Change Password** - Cambiar contraseña
6. **Logout** - Cerrar sesión

---

## 📚 ESTRUCTURA DE ARCHIVOS CREADOS

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       └── AuthController.php          (Controlador API)
│   ├── Middleware/
│   │   └── AuditoriaMiddleware.php        (Auditoría automática)
│   └── Requests/
│       └── Auth/
│           ├── LoginRequest.php           (Validación login)
│           ├── RegisterRequest.php        (Validación registro)
│           ├── ChangePasswordRequest.php  (Validación cambio)
│           ├── ForgotPasswordRequest.php  (Validación recuperación)
│           └── ResetPasswordRequest.php   (Validación reset)
├── Models/
│   ├── Usuario.php                        (Modelo Usuario)
│   ├── Rol.php                           (Modelo Rol)
│   ├── Sesion.php                        (Modelo Sesión)
│   ├── IntentoFallido.php                (Modelo Intentos)
│   └── Auditoria.php                     (Modelo Auditoría)
└── Services/
    └── AuthenticationService.php          (Lógica de autenticación)

database/
├── migrations/
│   └── 2025_12_26_000000_create_personal_access_tokens_table.php
└── seeders/
    └── RolesAndUsersSeeder.php           (Datos de prueba)

routes/
└── api.php                               (Rutas API)
```

---

## 🔒 CARACTERÍSTICAS DE SEGURIDAD IMPLEMENTADAS

✅ **Contraseñas seguras**
- Hash bcrypt con 10 rondas
- Complejidad obligatoria (mayúsculas, minúsculas, números, especiales)
- Mínimo 8 caracteres

✅ **Protección contra ataques**
- Rate limiting implícito con Sanctum
- Bloqueo automático después de 5 intentos fallidos
- Desbloqueo solo por administrador

✅ **Auditoría**
- Cada acción se registra (INSERT/UPDATE/DELETE)
- IP del cliente
- User-Agent del navegador
- Timestamp exacto

✅ **Tokens Sanctum**
- Almacenados en base de datos
- Revocación inmediata
- Sin expiración (mantiene sesión)

✅ **Validaciones**
- FormRequest con reglas Laravel
- Mensajes personalizados en español
- Validación de email único

---

## 📊 DIAGRAMA DE FLUJO

```
┌─────────────────────────────────────────────────────────────┐
│                      USUARIO                                │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
        ┌────────────────────────┐
        │  REGISTRAR / LOGIN     │
        └────────────┬───────────┘
                     │
                     ▼
        ┌────────────────────────┐
        │  Validar datos         │  ← FormRequest
        └────────────┬───────────┘
                     │
                     ▼
        ┌────────────────────────┐
        │  AuthenticationService │  ← Lógica centralizada
        └────────────┬───────────┘
                     │
         ┌───────────┴───────────┬────────────┐
         │                       │            │
         ▼                       ▼            ▼
    ┌─────────┐          ┌──────────┐  ┌─────────────┐
    │  Crear  │          │ Verificar│  │ Generar     │
    │ Usuario │          │Contraseña│  │ Token       │
    └────┬────┘          └─────┬────┘  └──────┬──────┘
         │                     │               │
         └─────────────────────┴───────────────┘
                     │
                     ▼
        ┌────────────────────────┐
        │ Registrar en Auditoría │  (opcional)
        └────────────┬───────────┘
                     │
                     ▼
        ┌────────────────────────┐
        │ Devolver respuesta     │
        │ con Token (si login)   │
        └────────────────────────┘
```

---

## 🐛 SOLUCIÓN DE PROBLEMAS

### Error: "Usuario no encontrado"
- Verifica que el email sea correcto
- Asegúrate de haber ejecutado el seeder: `php artisan db:seed --class=RolesAndUsersSeeder`

### Error: "Contraseña inválida"
- Las contraseñas son case-sensitive
- Verifica la ortografía exacta

### Error: "Token inválido"
- El token puede haber expirado o sido revocado
- Realiza nuevo login para obtener token nuevo

### Error: "Cuenta bloqueada"
- Demasiados intentos fallidos (5 en 15 minutos)
- Contacta al administrador para desbloqueo
- El administrador puede usar: `php artisan tinker` para desbloquear

---

## 📋 CHECKLIST FINAL

- ✅ Modelos Eloquent creados
- ✅ Migraciones ejecutadas
- ✅ Controlador API REST implementado
- ✅ Rutas API-REST registradas
- ✅ FormRequests con validación
- ✅ AuthenticationService centralizado
- ✅ Usuarios de prueba creados
- ✅ Auditoría implementada
- ✅ Bloqueo por intentos fallidos
- ✅ Documentación completa
- ✅ Ejemplos CURL incluidos
- ✅ Seeder funcional

---

## 📞 SOPORTE

Para reportar bugs o sugerencias:
1. Verifica el archivo `storage/logs/laravel.log`
2. Ejecuta `php artisan tinker` para inspeccionar BD
3. Revisa la tabla `auditoria` para ver acciones

---

**Módulo 1: Autenticación - COMPLETADO ✅**

18 puntos / 18 puntos de la historia de usuarios
