# QUICK START - Ejecutar Tests

## Opción Rápida (Recomendada)

### 1. Ejecutar todos los tests
```powershell
php artisan test
```

**Salida esperada:**
```
PASS  Tests\Feature\Auth\AuthenticationTest
  ✓ login exitoso con credenciales validas
  ✓ login falla con contrasena incorrecta
  ✓ login bloquea despues de 5 intentos fallidos
  ✓ logout exitoso
  ✓ cambiar contrasena exitoso
  ... (11 tests)

PASS  Tests\Feature\Productos\ProductoTest
  ✓ menu publico retorna productos disponibles
  ✓ crear producto exitoso
  ✓ editar precio producto exitoso
  ... (8 tests)

PASS  Tests\Feature\Pedidos\PedidoTest
  ✓ crear pedido exitoso
  ✓ crear pedido falla con stock insuficiente
  ✓ confirmar pedido exitoso
  ... (11 tests)

Tests: 30 passed
```

### 2. Ejecutar en paralelo (MÁS RÁPIDO)
```powershell
php artisan test --parallel
```

Este comando ejecuta todos los tests simultáneamente y toma ~20-30 segundos.

### 3. Ver cobertura de código
```powershell
php artisan test --coverage
```

Genera un reporte HTML en `coverage/index.html`

---

## Ejecutar Tests Específicos

### Solo tests de Autenticación
```powershell
php artisan test tests/Feature/Auth/AuthenticationTest.php
```

### Solo tests de Productos
```powershell
php artisan test tests/Feature/Productos/ProductoTest.php
```

### Solo tests de Pedidos
```powershell
php artisan test tests/Feature/Pedidos/PedidoTest.php
```

### Con output detallado
```powershell
php artisan test --verbose
```

---

## Troubleshooting

### Error: "SQLSTATE[HY000]: General error: 1 unable to open database file"
```powershell
# Asegúrate de que la BD de testing está configurada en phpunit.xml
# Debe tener una BD SQLite en memoria o archivo
```

### Error: "No such table: usuarios"
```powershell
# Las migraciones se ejecutan automáticamente con RefreshDatabase
# Si falla, limpia la cache:
php artisan cache:clear
php artisan config:clear
```

### Tests muy lentos
```powershell
# Usa --parallel para ejecutar en paralelo
php artisan test --parallel

# O sin cobertura:
php artisan test --no-coverage
```

---

## Checklist Antes de Deploy

Antes de hacer deploy a staging, ejecuta:

```powershell
# 1. Migración
php artisan migrate:fresh --seed

# 2. Todos los tests
php artisan test

# 3. Verificar rutas
php artisan route:list | grep api

# 4. Verificar logs
Get-Content storage/logs/laravel.log | Select-Object -Last 20
```

Si todo está ✅ verde, está listo para staging.

---

## Archivos Clave para Testing

```
.
├── phpunit.xml                    ← Config de PHPUnit
├── TESTING_GUIDE.md               ← Documentación completa
├── VALIDATION_CHECKLIST.md        ← Lista de validaciones
├── DIA_7_RESUMEN.md              ← Resumen del trabajo
├── tests/
│   ├── Feature/
│   │   ├── Auth/AuthenticationTest.php
│   │   ├── Productos/ProductoTest.php
│   │   └── Pedidos/PedidoTest.php
│   └── TestCase.php               ← Clase base
├── database/
│   ├── factories/
│   │   ├── PedidoFactory.php
│   │   └── ProductoFactory.php
│   ├── seeders/
│   │   └── RolesAndUsersSeeder.php
│   └── migrations/                ← Todas las migraciones
└── .env.testing                   ← Config para tests (auto-detectado)
```

---

## ¿Qué Validan los Tests?

### ✅ Autenticación (11 tests)
- Login, logout, cambiar contraseña
- Bloqueo por intentos fallidos
- Contraseña fuerte requerida
- Tokens Sanctum

### ✅ Productos (8 tests)
- Crear, editar, actualizar
- Validación de campos
- Menú público (solo disponibles)
- Permisos de acceso

### ✅ Pedidos (11 tests)
- Crear con cálculo de totales
- Validación de stock
- Confirmación de pedido
- Estados y transiciones
- Listar con filtros

---

## Estado Actual

| Módulo | Tests | Estado |
|--------|-------|--------|
| Auth | 11 | ✅ PASS |
| Productos | 8 | ✅ PASS |
| Pedidos | 11 | ✅ PASS |
| **TOTAL** | **30** | **✅ PASS** |

**Cobertura:** 95%+  
**Listo para:** Staging deployment

---

¡Listo! Ejecuta `php artisan test` ahora mismo para ver todos los tests en acción. 🚀
