# TESTING GUIDE - Pizzería API

## Información General

Esta guía cubre cómo ejecutar tests unitarios, feature tests, y validaciones en la Pizzería API.

---

## Setup para Testing

### 1. Instalar PHPUnit (si no está instalado)
```bash
composer require --dev phpunit/phpunit
```

### 2. Configurar Base de Datos de Testing
El archivo `phpunit.xml` ya está configurado para usar una BD de testing separada.

Asegúrate de que tu `.env.testing` tiene:
```env
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
```

O si prefieres una BD SQLite en archivo:
```env
DB_CONNECTION=sqlite
DB_DATABASE=database/testing.sqlite
```

---

## Ejecutar Tests

### Todos los Tests
```bash
php artisan test
```

### Tests de un directorio específico
```bash
php artisan test tests/Feature/Auth
php artisan test tests/Feature/Productos
php artisan test tests/Feature/Pedidos
```

### Un test específico
```bash
php artisan test tests/Feature/Auth/AuthenticationTest.php
```

### Con output verboso
```bash
php artisan test --verbose
```

### Con cobertura de código
```bash
php artisan test --coverage
```

### Tests en paralelo (más rápido)
```bash
php artisan test --parallel
```

---

## Tests Implementados

### 1. Authentication Tests (`tests/Feature/Auth/AuthenticationTest.php`)

#### Casos cubiertos:
- ✅ Login exitoso con credenciales válidas
- ✅ Login falla con contraseña incorrecta
- ✅ Login falla con email inexistente
- ✅ Login se bloquea después de 5 intentos fallidos
- ✅ Logout exitoso
- ✅ GET /api/auth/me retorna usuario autenticado
- ✅ Cambiar contraseña exitoso
- ✅ Cambiar contraseña falla con contraseña actual incorrecta
- ✅ Cambiar contraseña sin autenticación
- ✅ Registrar usuario falla con contraseña débil
- ✅ Registrar usuario exitoso

**Ejecutar:**
```bash
php artisan test tests/Feature/Auth/AuthenticationTest.php
```

---

### 2. Productos Tests (`tests/Feature/Productos/ProductoTest.php`)

#### Casos cubiertos:
- ✅ Ver menú público retorna solo productos disponibles
- ✅ Crear producto exitoso
- ✅ Crear producto falla sin autenticación
- ✅ Crear producto falla con nombre duplicado
- ✅ Editar precio de producto
- ✅ Editar precio falla con valor negativo
- ✅ Actualizar producto completo
- ✅ Actualizar producto falla con categoría inexistente

**Ejecutar:**
```bash
php artisan test tests/Feature/Productos/ProductoTest.php
```

---

### 3. Pedidos Tests (`tests/Feature/Pedidos/PedidoTest.php`)

#### Casos cubiertos:
- ✅ Crear pedido exitoso (US-020)
- ✅ Crear pedido falla sin items
- ✅ Crear pedido falla con stock insuficiente
- ✅ Crear pedido falla con producto no disponible
- ✅ Confirmar pedido exitoso (US-021)
- ✅ Confirmar pedido falla si ya está confirmado
- ✅ Ver estado del pedido (US-022)
- ✅ Ver pedido falla si no existe
- ✅ Listar pedidos exitosamente
- ✅ Listar pedidos con filtro por estado
- ✅ Listar pedidos falla sin autenticación

**Ejecutar:**
```bash
php artisan test tests/Feature/Pedidos/PedidoTest.php
```

---

## Validaciones de FormRequest

Todos los FormRequest incluyen validaciones automáticas:

### RegisterRequest
```php
- nombre: required|string|max:100
- email: required|email|unique:usuarios
- password: required|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/
- telefono: required|string|max:20
```

### CrearProductoRequest
```php
- nombre: required|string|unique:productos
- descripcion: nullable|string
- precio_base: required|numeric|min:0
- categoria_id: required|exists:categorias,id
- stock_disponible: nullable|integer|min:0
- stock_minimo: nullable|integer|min:0
```

### ActualizarProductoRequest
```php
- nombre: sometimes|string|unique:productos,nombre,{id}
- precio_base: sometimes|numeric|min:0
- categoria_id: sometimes|exists:categorias,id
- stock_disponible: sometimes|integer|min:0
- disponible: sometimes|boolean
- activo: sometimes|boolean
```

### CrearPedidoRequest
```php
- items: required|array|min:1
- items.*.producto_id: required|exists:productos,id
- items.*.cantidad: required|integer|min:1
- items.*.notas: nullable|string|max:500
- notas: nullable|string|max:1000
- costo_entrega: nullable|numeric|min:0
- monto_descuento: nullable|numeric|min:0
```

**Validaciones adicionales en withValidator():**
- Producto existe y está disponible
- Producto está activo
- Stock suficiente para la cantidad solicitada

---

## Estructura de Test

Cada test sigue este patrón:

```php
public function test_descripcion_clara()
{
    // Arrange: Preparar datos
    $usuario = Usuario::where('email', 'admin@pizzeria.com')->first();
    $token = $usuario->createToken('test-token')->plainTextToken;

    // Act: Ejecutar acción
    $response = $this->withHeaders([
        'Authorization' => "Bearer $token",
    ])->postJson('/api/ruta', $data);

    // Assert: Verificar resultado
    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ]);
}
```

---

## Factories Disponibles

### PedidoFactory
```php
// Crear pedido aleatorio
$pedido = Pedido::factory()->create();

// Crear pedido confirmado
$pedido = Pedido::factory()->confirmado()->create();

// Crear 5 pedidos
$pedidos = Pedido::factory()->count(5)->create();
```

### ProductoFactory
```php
// Crear producto disponible
$producto = Producto::factory()->create();

// Crear producto no disponible
$producto = Producto::factory()->noDisponible()->create();

// Crear 10 productos aleatorios
$productos = Producto::factory()->count(10)->create();
```

---

## Base de Datos de Testing

### Usar RefreshDatabase
Todos los tests usan `RefreshDatabase` que:
- Corre migraciones antes de cada test
- Limpia la BD después de cada test
- Proporciona BD limpia y aislada

```php
class MyTest extends TestCase {
    use RefreshDatabase;
    
    public function test_algo() { ... }
}
```

### Seed de Datos
Los tests siembran automáticamente datos iniciales:

```php
protected function setUp(): void
{
    parent::setUp();
    $this->seed(\Database\Seeders\RolesAndUsersSeeder::class);
}
```

---

## Ejecutar Tests en CI/CD

### GitHub Actions (ejemplo)
```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      postgres:
        image: postgres:15
        options: >-
          --health-cmd pg_isready
          --health-interval 10s
          --health-timeout 5s
          --health-retries 5
    
    steps:
      - uses: actions/checkout@v2
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '10.3'
      
      - run: composer install
      - run: php artisan test --coverage
```

---

## Troubleshooting Tests

### Error: "SQLSTATE[HY000]: General error: 1 unable to open database file"
**Solución:** Asegurate que `database/testing.sqlite` existe o usa `:memory:`

### Error: "Illuminate\Database\QueryException: SQLSTATE[HY000]"
**Solución:** Ejecuta `php artisan migrate:fresh --seed` en ambiente de testing

### Tests lentos
**Solución:** Usa `--parallel` para ejecutar tests en paralelo
```bash
php artisan test --parallel
```

### Test falla con "Undefined offset"
**Solución:** Verifica que los datos están siendo creados correctamente en `setUp()`

---

## Cobertura de Código

Generar reporte de cobertura:
```bash
php artisan test --coverage
```

Ver cobertura en HTML:
```bash
php artisan test --coverage --coverage-html coverage
```

Luego abre `coverage/index.html` en el navegador.

**Objetivo de cobertura:**
- Controllers: > 90%
- Models: > 85%
- Services: > 90%

---

## Checklist de Testing Completo

- [ ] Todos los tests pasan: `php artisan test`
- [ ] Cobertura > 85%: `php artisan test --coverage`
- [ ] No hay warnings o errores de PHP
- [ ] Tests corren en < 30 segundos (con --parallel)
- [ ] BD testing está aislada de BD producción
- [ ] Logs no contienen errores (check storage/logs/)
- [ ] FormRequest validaciones están probadas
- [ ] Permissiones de usuario están validadas
- [ ] Errores HTTP retornan status correcto
- [ ] Respuestas JSON tienen estructura correcta

---

## Ejemplos de Assertions Útiles

```php
// Status HTTP
$response->assertStatus(200);
$response->assertOk();
$response->assertCreated(); // 201
$response->assertUnauthorized(); // 401
$response->assertForbidden(); // 403
$response->assertNotFound(); // 404

// JSON
$response->assertJson(['key' => 'value']);
$response->assertJsonStructure(['data' => ['id', 'name']]);
$response->assertJsonValidationErrors(['email']);

// Base de datos
$this->assertDatabaseHas('usuarios', ['email' => 'test@example.com']);
$this->assertDatabaseMissing('usuarios', ['email' => 'nonexistent@example.com']);

// Headers
$response->assertHeader('Content-Type', 'application/json');

// Redirecciones
$response->assertRedirect('/login');
```

---

## Recursos Adicionales

- [Laravel Testing Documentation](https://laravel.com/docs/testing)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Laravel API Testing](https://laravel.com/docs/http-tests)
- [Test Driven Development](https://en.wikipedia.org/wiki/Test-driven_development)
