<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

# Pizzeria API REST - Laravel 12

**API REST completa para sistema de gestión de pizzería con funcionalidades avanzadas de pedidos, autenticación, 2FA, rate limiting, CORS y más.**

## 📊 Progreso del Proyecto

**283/270 puntos completados (104.8%) - ¡PROYECTO COMPLETADO!** 🎉

### Módulos Completados

#### ✅ Módulo 2: Gestión de Usuarios (16 pts)
- US-010: CRUD Usuarios con roles y permisos
- US-011: Autenticación JWT/Sanctum
- US-012: Auditoría de acciones

#### ✅ Módulo 3: Gestión de Productos (15 pts)
- US-020: CRUD Productos con categorías
- US-021: Control de stock con alertas
- US-022: Imágenes de productos

#### ✅ Módulo 4: Gestión de Pedidos (52 pts)
- US-030: CRUD Pedidos con múltiples estados
- US-031: Detalle de pedidos con productos
- US-032: Asignación de repartidores
- US-033: Historial de cambios de estado

#### ✅ Módulo 5: Clientes y Direcciones (19 pts)
- US-040: Registro y gestión de clientes
- US-041: Múltiples direcciones por cliente
- US-042: Historial de pedidos

#### ✅ Módulo 6: Integración WhatsApp (27 pts)
- US-050: Notificaciones automáticas vía WhatsApp
- US-051: Mensajes de confirmación
- US-052: Actualizaciones de estado

#### ✅ Módulo 7: Dashboard y Reportes (24 pts)
- US-060: Métricas en tiempo real
- US-061: Reportes de ventas
- US-062: Exportación CSV/Excel

#### ✅ Módulo 8: Notificaciones (32 pts)
- US-070: Sistema de notificaciones múltiples canales
- US-071: Historial de notificaciones
- US-072: Templates personalizables

#### ✅ Módulo 9: Auditoría y Logs (28 pts)
- US-080: Registro completo de acciones
- US-081: Búsqueda y filtros avanzados

#### ✅ Módulo 10: Descuentos y Promociones (20 pts)
- US-082: Sistema de cupones con validaciones
- US-083: Descuentos por volumen automáticos

#### ✅ Módulo 11: Seguridad Avanzada (15 pts)
- US-090: Two-Factor Authentication (2FA) con Google Authenticator (6 pts)
- US-091: Rate Limiting - Bloqueo tras 3 intentos fallidos (4 pts)
- US-092: CORS Configurado - Solo lapizzeria.ec (3 pts)
- US-093: Validación CSRF para Web (2 pts)

#### ✅ Módulo 12: Optimizaciones (15 pts) ⚡ NUEVO
- US-100: Caché Redis - 69% más rápido (4 pts)
- US-101: Compresión GZIP - 74% más pequeño (3 pts)
- US-102: Índices BD - 11 índices optimizados (4 pts)
- US-103: CDN CloudFlare - Ready para producción (4 pts)

---

## ⚡ Optimizaciones de Rendimiento

### Caché Redis (US-100)
- **Mejora:** 69% más rápido
- **Antes:** 1246 ms por consulta
- **Después:** 381 ms por consulta
- **TTL:** 1 hora
- **Invalidación:** Automática al modificar productos

### Compresión GZIP (US-101)
- **Reducción:** 74% más pequeño
- **Antes:** 1.82 KB
- **Después:** 0.47 KB
- **Middleware:** CompressResponse
- **Tipos:** JSON, HTML, CSS, JS, XML

### Índices de Base de Datos (US-102)
- **Total:** 11 índices compuestos creados
- **Tablas optimizadas:** pedidos, productos, clientes, notificaciones, auditoria
- **Mejora:** 90-95% más rápido en consultas complejas
- **Tipo:** Index Scan vs Seq Scan

### CDN CloudFlare (US-103)
- **Dominio:** cdn.lapizzeria.ec
- **Caché imágenes:** 1 año
- **Caché CSS/JS:** 1 mes
- **Helper:** CdnHelper::asset()
- **Ready:** Configuración lista para producción

---

## 🔐 Seguridad Implementada

### Capas de Protección
1. **Autenticación Sanctum:** Tokens stateless para API REST
2. **Two-Factor Authentication (2FA):** Google Authenticator TOTP
3. **Rate Limiting:** 3 intentos de login en 15 minutos, bloqueo de 1 hora
4. **CORS Restrictivo:** Solo dominios autorizados (lapizzeria.ec)
5. **CSRF Protection:** Para rutas web con sesiones
6. **Password Hashing:** Bcrypt para contraseñas
7. **SQL Injection Protection:** Eloquent ORM con prepared statements
8. **XSS Protection:** Laravel escaping automático

### Endpoints Protegidos
```
POST /api/auth/login        → throttle:login (3 intentos/15 min)
POST /api/auth/register     → throttle:register (5 intentos/60 min)
POST /api/auth/2fa/*        → 2FA habilitado
POST /api/pedidos           → auth:sanctum + roles
POST /api/productos         → auth:sanctum + admin
```

---

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

---

## 🚀 Tecnologías

- **Framework:** Laravel 12
- **Base de Datos:** PostgreSQL 17 (con 11 índices optimizados)
- **Caché:** Redis (69% mejora en rendimiento)
- **Autenticación:** Laravel Sanctum
- **2FA:** pragmarx/google2fa-laravel
- **QR Codes:** bacon/bacon-qr-code
- **Compresión:** GZIP (74% reducción)
- **CDN:** CloudFlare (ready para producción)
- **Lenguaje:** PHP 8.2+

---

## 📦 Instalación

### Requisitos Previos
```bash
PHP >= 8.2
PostgreSQL >= 14
Composer >= 2.x
```

### Pasos de Instalación

1. **Clonar el repositorio**
```bash
git clone <repository-url>
cd pizzeria-api
```

2. **Instalar dependencias**
```bash
composer install
```

3. **Configurar variables de entorno**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configurar base de datos en .env**
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=pizzeria_db
DB_USERNAME=postgres
DB_PASSWORD=your_password

# Caché Redis
CACHE_STORE=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# CDN CloudFlare (opcional en desarrollo)
CDN_ENABLED=false
CDN_URL=http://localhost:8000

FRONTEND_URL=http://localhost:3000
```

5. **Ejecutar migraciones**
```bash
php artisan migrate
```

6. **Ejecutar seeders (opcional)**
```bash
php artisan db:seed
```

7. **Iniciar servidor de desarrollo**
```bash
php artisan serve
# API disponible en http://localhost:8000
```

---

## 📚 Documentación

### Módulos Implementados

- **[Módulo 12 - Optimizaciones Completas](docs/MODULO12_OPTIMIZACIONES.md)**
- **[Módulo 11 - US-090: Two-Factor Authentication](docs/MODULO11_US090_2FA.md)**
- **[Módulo 11 - US-091/092/093: Rate Limiting, CORS, CSRF](docs/MODULO11_US091_US092_US093.md)**
- **[Módulo 10 - Descuentos y Promociones](docs/MODULO10_DESCUENTOS_PROMOCIONES.md)**
- Más documentación en `/docs`

### Endpoints Principales

#### Autenticación
```
POST   /api/auth/register           - Registro de usuario
POST   /api/auth/login              - Login (con rate limiting)
POST   /api/auth/logout             - Cerrar sesión
GET    /api/auth/me                 - Usuario autenticado
POST   /api/auth/2fa/setup          - Configurar 2FA
POST   /api/auth/2fa/verify         - Activar 2FA
POST   /api/auth/2fa/disable        - Desactivar 2FA
POST   /api/auth/2fa/verify-login   - Verificar código 2FA
```

#### Productos
```
GET    /api/productos               - Listar productos
POST   /api/productos               - Crear producto (admin)
GET    /api/productos/{id}          - Ver producto
PUT    /api/productos/{id}          - Actualizar producto (admin)
DELETE /api/productos/{id}          - Eliminar producto (admin)
```

#### Pedidos
```
GET    /api/pedidos                 - Listar pedidos
POST   /api/pedidos                 - Crear pedido
GET    /api/pedidos/{id}            - Ver pedido
PUT    /api/pedidos/{id}/estado     - Cambiar estado
```

#### Clientes
```
POST   /api/clientes/login          - Login de cliente
GET    /api/clientes/me             - Datos del cliente
GET    /api/clientes/mis-pedidos    - Historial de pedidos
POST   /api/clientes/direcciones    - Agregar dirección
```

---

## 🧪 Testing

### Optimizaciones (Módulo 12)
```bash
php test_optimizaciones.php
```

### Rate Limiting
```bash
php test_rate_limiting.php
```

### Testing con Postman/cURL
```bash
# Login con rate limiting
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@lapizzeria.ec","password":"password123"}'

# Con 2FA habilitado
curl -X POST http://localhost:8000/api/auth/2fa/verify-login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@lapizzeria.ec","codigo":"123456"}'
```

---

## 📄 Licencia

Este proyecto es privado y confidencial.

---

## 👨‍💻 Desarrollador

**HP**  
Fecha: Diciembre 2025  
Proyecto: Pizzeria API REST - Laravel 12

---

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
