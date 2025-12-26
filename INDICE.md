# 📚 ÍNDICE DE DOCUMENTACIÓN - MÓDULO 1: AUTENTICACIÓN

## 🎯 Comienza aquí

1. **[RESUMEN_MODULO1.txt](RESUMEN_MODULO1.txt)** ⭐
   - Resumen visual de toda la implementación
   - Estadísticas y checklist final

2. **[README_MODULO1.md](README_MODULO1.md)** 🚀
   - Guía rápida de instalación
   - Instrucciones de uso
   - Usuarios de prueba
   - Solución de problemas

## 📖 Documentación Técnica

3. **[API_AUTHENTICATION.md](API_AUTHENTICATION.md)** 🔌
   - Referencia completa de endpoints
   - Ejemplos con CURL
   - Respuestas JSON
   - Políticas de seguridad

4. **[IMPLEMENTACION_COMPLETA.md](IMPLEMENTACION_COMPLETA.md)** 🏗️
   - Arquitectura técnica
   - Estructura de carpetas
   - Modelos y relaciones
   - Características de seguridad

## 🧪 Pruebas y Ejemplos

5. **[test-auth-api.ps1](test-auth-api.ps1)** ⚙️
   - Script de pruebas en PowerShell
   - Función helper para requests
   - Tests completos del API

6. **[authentication-api.postman_collection.json](authentication-api.postman_collection.json)** 📮
   - Colección Postman lista para importar
   - Variables de entorno configuradas
   - Ejemplos de requests

## 🔒 Código Fuente

### Modelos
- [app/Models/Usuario.php](app/Models/Usuario.php) - Modelo principal
- [app/Models/Rol.php](app/Models/Rol.php) - Roles del sistema
- [app/Models/Sesion.php](app/Models/Sesion.php) - Sesiones
- [app/Models/IntentoFallido.php](app/Models/IntentoFallido.php) - Intentos
- [app/Models/Auditoria.php](app/Models/Auditoria.php) - Auditoría

### Controladores
- [app/Http/Controllers/Api/AuthController.php](app/Http/Controllers/Api/AuthController.php)

### Servicios
- [app/Services/AuthenticationService.php](app/Services/AuthenticationService.php)

### Validaciones
- [app/Http/Requests/Auth/LoginRequest.php](app/Http/Requests/Auth/LoginRequest.php)
- [app/Http/Requests/Auth/RegisterRequest.php](app/Http/Requests/Auth/RegisterRequest.php)
- [app/Http/Requests/Auth/ChangePasswordRequest.php](app/Http/Requests/Auth/ChangePasswordRequest.php)
- [app/Http/Requests/Auth/ForgotPasswordRequest.php](app/Http/Requests/Auth/ForgotPasswordRequest.php)
- [app/Http/Requests/Auth/ResetPasswordRequest.php](app/Http/Requests/Auth/ResetPasswordRequest.php)

### Middleware
- [app/Http/Middleware/AuditoriaMiddleware.php](app/Http/Middleware/AuditoriaMiddleware.php)

### Rutas
- [routes/api.php](routes/api.php) - Endpoints API-REST

### Migraciones
- [database/migrations/2025_12_26_000000_create_personal_access_tokens_table.php](database/migrations/2025_12_26_000000_create_personal_access_tokens_table.php)

### Seeders
- [database/seeders/RolesAndUsersSeeder.php](database/seeders/RolesAndUsersSeeder.php)

## 🚀 Flujo de Uso Rápido

### Para Desarrolladores

```bash
# 1. Instalar dependencias
composer install

# 2. Configurar .env
cp .env.example .env
php artisan key:generate

# 3. Ejecutar migraciones
php artisan migrate

# 4. Cargar datos de prueba
php artisan db:seed --class=RolesAndUsersSeeder

# 5. Iniciar servidor
php artisan serve
```

### Para Probar el API

**Opción 1: CURL**
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@lapizzeria.ec","password":"Admin@123456"}'
```

**Opción 2: Postman**
1. Importar `authentication-api.postman_collection.json`
2. Ejecutar requests

**Opción 3: PowerShell**
```powershell
.\test-auth-api.ps1
```

## 📊 Estructura del Proyecto

```
pizzeria-api/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/
│   │   │   └── AuthController.php
│   │   ├── Middleware/
│   │   │   └── AuditoriaMiddleware.php
│   │   └── Requests/Auth/
│   │       ├── LoginRequest.php
│   │       ├── RegisterRequest.php
│   │       ├── ChangePasswordRequest.php
│   │       ├── ForgotPasswordRequest.php
│   │       └── ResetPasswordRequest.php
│   ├── Models/
│   │   ├── Usuario.php
│   │   ├── Rol.php
│   │   ├── Sesion.php
│   │   ├── IntentoFallido.php
│   │   └── Auditoria.php
│   └── Services/
│       └── AuthenticationService.php
├── database/
│   ├── migrations/
│   │   └── 2025_12_26_000000_create_personal_access_tokens_table.php
│   └── seeders/
│       └── RolesAndUsersSeeder.php
├── routes/
│   └── api.php
├── config/
│   └── auth.php (actualizado)
├── API_AUTHENTICATION.md
├── README_MODULO1.md
├── IMPLEMENTACION_COMPLETA.md
├── RESUMEN_MODULO1.txt
├── INDICE.md (este archivo)
├── test-auth-api.ps1
└── authentication-api.postman_collection.json
```

## ✅ Checklist de Historias de Usuario

- [x] **US-001: Login** (5 pts)
  - POST /api/auth/login
  - Autenticación con email/contraseña
  - Devuelve token JWT

- [x] **US-002: Cambiar Contraseña** (5 pts)
  - POST /api/auth/change-password
  - Requiere contraseña actual
  - Validación de complejidad

- [x] **US-003: Recuperar Contraseña** (5 pts)
  - POST /api/auth/forgot-password
  - POST /api/auth/reset-password
  - Con token de seguridad

- [x] **US-004: Logout** (3 pts)
  - POST /api/auth/logout
  - Revoca token inmediatamente

- [x] **BONUS: Registro** (extra)
  - POST /api/auth/register
  - Validación completa
  - Datos de prueba

## 📞 Preguntas Frecuentes

### ¿Cómo obtengo un token?
1. Haz login en `POST /api/auth/login`
2. El token viene en la respuesta
3. Úsalo en el header: `Authorization: Bearer {TOKEN}`

### ¿Qué pasa si olvido la contraseña?
1. Solicita recuperación en `POST /api/auth/forgot-password`
2. Recibirás un email con token
3. Resetea en `POST /api/auth/reset-password`

### ¿Cuántos intentos fallidos puedo tener?
- Máximo 5 intentos en 15 minutos
- Se bloquea automáticamente
- Solo admin puede desbloquear

### ¿Los tokens expiran?
- Sí, se revocan en logout
- Sin expiración automática
- Pueden verificarse en `/api/auth/verify-token`

## 🔗 Enlaces Útiles

- [Laravel Documentation](https://laravel.com/docs)
- [Sanctum Documentation](https://laravel.com/docs/sanctum)
- [Postman Documentation](https://learning.postman.com/)
- [PostgreSQL Documentation](https://www.postgresql.org/docs/)

## 📝 Versiones

- **v1.0** (25 de Diciembre, 2025) - Lanzamiento inicial
  - Módulo 1 completado al 100%
  - 18/18 puntos

## 👨‍💻 Autor

**LA PIZZERÍA - CRAZY SNAKES**  
Sistema de Gestión de Pedidos en Línea  
Guayaquil, Ecuador

---

**Última actualización:** 25 de Diciembre, 2025  
**Estado:** Production Ready ✅
